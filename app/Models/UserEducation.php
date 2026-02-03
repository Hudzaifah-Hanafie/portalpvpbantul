<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEducation extends Model
{
    use HasUuid;

    protected $table = 'user_educations';

    protected $fillable = [
        'user_id',
        'sso_id',
        'school_name',
        'school_address',
        'graduate_name',
        'study_field_name',
        'start_year',
        'finish_year',
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
