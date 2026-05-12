<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseMaterialDiscussion extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_material_id',
        'user_id',
        'parent_id',
        'message',
        'likes_count',
    ];

    public function courseMaterial(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CourseMaterialDiscussion::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CourseMaterialDiscussion::class, 'parent_id');
    }
}
