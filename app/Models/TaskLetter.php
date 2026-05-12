<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLetter extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_class_id',
        'letter_number',
        'legal_basis',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location_name',
        'location_link',
        'instructors',
        'committees',
        'participant_statuses',
        'status',
        'signed_city',
        'signed_at',
        'signatory_name',
        'signatory_position',
        'signatory_nip',
        'created_by',
    ];

    protected $casts = [
        'instructors' => 'array',
        'committees' => 'array',
        'participant_statuses' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'date',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }
}
