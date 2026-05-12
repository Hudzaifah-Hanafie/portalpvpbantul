<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMaterialProgress extends Model
{
    use HasUuid;

    protected $table = 'course_material_progress';

    protected $fillable = [
        'user_id',
        'course_material_id',
        'is_completed',
        'completed_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courseMaterial(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class);
    }
}
