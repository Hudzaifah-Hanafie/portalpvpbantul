<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseCurriculum extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_class_id',
        'title',
        'skkni_reference',
        'matrix_reference',
        'notes',
        'method',
        'total_jp_theory',
        'total_jp_practice',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(CourseCurriculumUnit::class)->orderBy('sort_order');
    }
}
