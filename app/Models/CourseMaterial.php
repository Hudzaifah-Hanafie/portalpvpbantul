<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMaterial extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_module_id',
        'title',
        'content',
        'link_url',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function discussions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseMaterialDiscussion::class);
    }

    public function progress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseMaterialProgress::class);
    }
}
