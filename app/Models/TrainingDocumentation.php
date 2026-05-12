<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingDocumentation extends Model
{
    use HasUuid;

    protected $fillable = [
        'training_schedule_id',
        'title',
        'description',
        'link_url',
        'documented_at',
        'owner_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'documented_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
