<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'sso_user_id',
        'sso_profile_id',
        'identity_number',
        'phone',
        'address',
        'domicile_address',
        'domicile_region_id',
        'domicile_region_type',
        'domicile_province_id',
        'domicile_city_id',
        'domicile_sub_district_id',
        'domicile_village_id',
        'birth_place',
        'birth_date',
        'about',
        'picture_uri',
        'blood_type',
        'gender',
        'status',
        'domicile_region_payload',
        'payload',
        'synced_at',
    ];

    protected $casts = [
        'domicile_region_payload' => 'array',
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
