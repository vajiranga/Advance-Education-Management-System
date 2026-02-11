<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\Notice;
use App\Models\SystemSetting;
use Carbon\Carbon;

class CleanOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications and notices based on retention settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting notification cleanup...');

        // 1. Get Retention Days from Settings (Default to 30 days if not set)
        $days = SystemSetting::where('key', 'notificationRetentionDays')->value('value');

        if (!$days) {
            $days = 30; // Default
            $this->info("No retention setting found. Using default: {$days} days.");
        } else {
            $days = (int)$days;
            $this->info("Retention setting found: {$days} days.");
        }

        $cutoffDate = Carbon::now()->subDays($days);
        $this->info("Deleting notifications older than: " . $cutoffDate->toDateTimeString());

        // 2. Delete Personal Notifications
        $deletedNotifications = Notification::where('created_at', '<', $cutoffDate)->delete();
        $this->info("Deleted {$deletedNotifications} old personal notifications.");

        // 3. Delete Old Notices (Announcements)
        // We use scheduled_at or created_at. Scheduled_at is better for notices.
        $deletedNotices = Notice::where('scheduled_at', '<', $cutoffDate)->delete();
        $this->info("Deleted {$deletedNotices} old broadcast notices.");

        $this->info('Notification cleanup completed successfully.');
        return 0;
    }
}
