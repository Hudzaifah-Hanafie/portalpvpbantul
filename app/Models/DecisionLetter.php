<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionLetter extends Model
{
    use HasUuid;

    protected $fillable = [
        'letter_number',
        'batch_id',
        'period_year',
        'schedule_ids',
        'subject',
        'considerations',
        'legal_basis',
        'decisions',
        'location_name',
        'status',
        'signed_city',
        'signed_at',
        'signatory_name',
        'signatory_position',
        'signatory_nip',
        'approval_left_name',
        'approval_left_position',
        'approval_left_nip',
        'approval_right_name',
        'approval_right_position',
        'approval_right_nip',
        'instructor_team',
        'recruitment_team',
        'management_team',
        'created_by',
    ];

    protected $casts = [
        'schedule_ids' => 'array',
        'considerations' => 'array',
        'legal_basis' => 'array',
        'decisions' => 'array',
        'instructor_team' => 'array',
        'recruitment_team' => 'array',
        'management_team' => 'array',
        'signed_at' => 'date',
        'period_year' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
