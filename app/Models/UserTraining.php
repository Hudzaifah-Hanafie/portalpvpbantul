<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTraining extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'sso_id',
        'training_center_name',
        'vocational_name',
        'sub_vocational_name',
        'training_program_name',
        'start_month',
        'start_year',
        'finish_month',
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
