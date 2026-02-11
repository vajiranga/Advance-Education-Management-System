<?php

namespace App\Traits;

use App\Models\User;

trait GeneratesCustomIds
{
    /**
     * Helper to generate sequential IDs (e.g. STU00000001)
     * Supports overflow to Alphanumeric (99999999 -> A0000000)
     * And smart reset logic for handling low sequences amidst high legacy IDs.
     */
    private function generateCustomId($role, $prefix, $startSequence)
    {
        // Padded Target string (e.g. 00020000)
        $targetStartStr = str_pad($startSequence, 8, '0', STR_PAD_LEFT);
        $firstChar = $targetStartStr[0];

        // SMART RESET LOGIC:
        // If the requested Start Sequence starts with '0' or '1' (e.g. 00020000 or 10000000),
        // and existing IDs are Year-Based (e.g. 2026xxxx -> '2...'),
        // we should prefer the lower sequence range instead of appending to the huge Year ID.
        if (ctype_digit($firstChar) && (int)$firstChar < 2) {
             // Find max ID specifically in this lower range (e.g. starts with STU0...)
             $localLastUser = User::where('role', $role)
                    ->where('username', 'LIKE', "{$prefix}{$firstChar}%")
                    ->orderByRaw('LENGTH(username) DESC')
                    ->orderBy('username', 'DESC')
                    ->first();

             if ($localLastUser) {
                 $suffix = substr($localLastUser->username, strlen($prefix));
                 if (ctype_digit($suffix)) {
                      $num = (int)$suffix;
                      $next = max($num + 1, $startSequence);
                      return $prefix . str_pad($next, 8, '0', STR_PAD_LEFT);
                 }
             } else {
                 // No ID found in this range, so we can safely start fresh here!
                 // Check basic existence to be safe (though query above implies it's free)
                 // Or maybe check if EXACT sequence start is taken by something else?
                 if (!User::where('username', $prefix . $targetStartStr)->exists()) {
                     return $prefix . $targetStartStr;
                 }
             }
        }

        // FALLBACK: Standard Global Max Logic (for high numbers or normal continuity)
        $lastUser = User::where('role', $role)
            ->where('username', 'LIKE', "{$prefix}%")
            ->orderByRaw('LENGTH(username) DESC') // Ensure we get longest IDs first (e.g. 10 vs 9 chars)
            ->orderBy('username', 'DESC')
            ->first();

        if (!$lastUser) {
            return $prefix . $targetStartStr;
        }

        $lastId = $lastUser->username;
        if (strpos($lastId, $prefix) !== 0) {
            return $prefix . $targetStartStr;
        }

        $suffix = substr($lastId, strlen($prefix));

        if (ctype_digit($suffix)) {
            $num = (int)$suffix;

            // If the current Setting is HIGHER than the last DB ID, we jump to the setting.
            if ($startSequence > $num + 1) {
                // Wait, if startSequence is higher, we should jump.
                // But we must also check if we are in the "High Legacy Mode" fallback.
                // If Legacy is 2026... sequence is 20000.
                // 20000 > 2026...? False.
                // So this block only runs if Setting is unusually huge (e.g. 30000000).

                $num = $startSequence - 1;
            }

            if ($num < 99999999) {
                $num++;
                return $prefix . str_pad($num, 8, '0', STR_PAD_LEFT);
            } else {
                // Numeric Overflow -> Switch to Alpha (A0000000)
                return $prefix . 'A0000000';
            }
        } else {
            // Already Alphanumeric (e.g. A0000001)
            $newSuffix = $suffix;
            $newSuffix++;
            return $prefix . $newSuffix;
        }
    }
}
