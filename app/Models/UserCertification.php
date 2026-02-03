<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCertification extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'sso_id',
        'institution_name',
        'program_name',
        'issued_month',
        'issued_year',
        'expire_month',
        'expire_year',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
