<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            $setting = \App\Models\SystemSetting::where('key', 'enableAppNotifications')->first();
            // If setting doesn't exist OR is false, block notification creation
            // This means notifications are DISABLED by default until explicitly enabled
            if (!$setting || $setting->value === 'false' || $setting->value === '0' || $setting->value === 0 || $setting->value === false) {
                return false;
            }
        });
    }
}
