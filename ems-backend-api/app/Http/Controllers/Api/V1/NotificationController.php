<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Notice;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user's notifications (Personal + System Broadcasts)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $request->query('limit', 10);

        // 1. Fetch Personal Notifications
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // 2. Fetch Relevant Broadcasts/Notices
        $noticesQuery = Notice::where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'desc');

        if ($user->role === 'student') {
            $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');
            $noticesQuery->where(function($q) use ($enrolledCourseIds) {
                $q->whereIn('target_audience', ['all', 'student'])
                  ->orWhereIn('course_id', $enrolledCourseIds);
            });
        } elseif ($user->role === 'teacher') {
            $noticesQuery->whereIn('target_audience', ['all', 'teacher']);
            // Optionally add notices for courses they teach? 
            // For now, assume teachers only get global/teacher notices.
        } elseif ($user->role === 'parent') {
            $noticesQuery->whereIn('target_audience', ['all', 'parent']);
        } else {
             // Admins see everything? Or just 'all'? Let's show everything for now or just 'all'
             $noticesQuery->whereIn('target_audience', ['all', 'student', 'teacher', 'parent']);
        }

        $notices = $noticesQuery->limit($limit)->get();

        // 3. Transform Notices to match Notification structure
        $formattedNotices = $notices->map(function($n) {
            return [
                'id' => 'notice-' . $n->id, // Distinct ID format
                'user_id' => null,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type ?? 'info',
                'read_at' => null, // Broadcasts technically don't track read state yet
                'created_at' => $n->scheduled_at,
                'updated_at' => $n->updated_at,
                'data' => [] // Extra data field if needed
            ];
        });

        // 4. Merge and Sort
        $all = $notifications->concat($formattedNotices)->sortByDesc('created_at')->values();
        
        // 5. Paginate Manually
        // Since we combined two sources, true DB pagination is hard. 
        // We'll return the top X combined results.
        $pagedData = $all->take($limit);

        // Recalculate unread (Approximation)
        $unreadCount = Notification::where('user_id', $user->id)->whereNull('read_at')->count();
        // Add notice count? For now, let's just stick to personal unread count to avoid bloat
        // $unreadCount += $formattedNotices->count(); 

        return response()->json([
            'data' => $pagedData,
            'meta' => [
                'current_page' => 1,
                'last_page' => 1, // Simplified
                'unread_count' => $unreadCount
            ]
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        // Handle Broadcast Notices (Fake Read for client compatibility)
        if (str_starts_with($id, 'notice-')) {
            return response()->json(['success' => true]);
        }

        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all as read
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
