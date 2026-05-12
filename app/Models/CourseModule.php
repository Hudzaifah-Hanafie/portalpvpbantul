<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_class_id',
        'title',
        'description',
        'sort_order',
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

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'course_module_id')->orderBy('due_at');
    }
}
