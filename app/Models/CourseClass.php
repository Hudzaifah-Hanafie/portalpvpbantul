<?php

namespace App\Models;

use App\Models\CourseAnnouncement;
use App\Models\CourseModule;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseClass extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'format',
        'prerequisites',
        'competencies',
        'badge',
        'tags',
        'is_active',
        'status',
        'instructor_id',
        'created_by',
        'approved_by',
        'approved_at',
        'published_at',
        'min_attendance',
        'min_score',
        'require_final_project',
        'require_final_exam',
        'weight_theory',
        'weight_practice',
        'weight_attitude',
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'competencies' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'require_final_project' => 'boolean',
        'require_final_exam' => 'boolean',
        'weight_theory' => 'integer',
        'weight_practice' => 'integer',
        'weight_attitude' => 'integer',
    ];

    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Menunggu Review',
            'published' => 'Terpublikasi',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class)->orderBy('start_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class)->orderBy('due_at');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function curricula(): HasMany
    {
        return $this->hasMany(CourseCurriculum::class, 'course_class_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(CourseAnnouncement::class, 'course_class_id')->latest();
    }
}
