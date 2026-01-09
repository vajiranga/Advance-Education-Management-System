<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
// use Stancl\Tenancy\Contracts\TenantWithDatabase;
// use Stancl\Tenancy\Database\Concerns\HasDatabase;
// use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends Model // BaseTenant implements TenantWithDatabase
{
    // use HasDatabase, HasDomains;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'plan_id',
            'name',
            'status',
        ];
    }
}
