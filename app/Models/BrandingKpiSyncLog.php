<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class BrandingKpiSyncLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'platform',
        'status',
        'message',
        'indicator_key',
        'metric',
        'metric_value',
        'payload',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
        'metric_value' => 'float',
    ];
}
