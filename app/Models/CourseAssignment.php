<?php

namespace App\Models;

use App\Models\CourseModule;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseAssignment extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_class_id',
        'course_module_id',
        'title',
        'description',
        'type',
        'quiz_scope',
        'assessment_type',
        'due_at',
        'weight',
        'max_score',
        'rubric_id',
        'rubric',
        'late_policy',
        'penalty_percent',
        'is_active',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'published_at',
        'quiz_schema',
        'quiz_settings',
        'require_token',
        'exam_token',
        'exam_start_at',
        'exam_end_at',
        'auto_submit',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'rubric' => 'array',
        'quiz_schema' => 'array',
        'quiz_settings' => 'array',
        'require_token' => 'boolean',
        'exam_start_at' => 'datetime',
        'exam_end_at' => 'datetime',
        'auto_submit' => 'boolean',
    ];

    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Menunggu Review',
            'published' => 'Terpublikasi',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CourseSubmission::class, 'course_assignment_id');
    }
}
