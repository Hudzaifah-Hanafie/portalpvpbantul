<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCurriculumUnit extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_curriculum_id',
        'unit_code',
        'unit_title',
        'elements',
        'kuk',
        'materials',
        'jp_theory',
        'jp_practice',
        'method',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(CourseCurriculum::class, 'course_curriculum_id');
    }
}
