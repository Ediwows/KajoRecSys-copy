<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assigned_by',
    'student_id',
    'related_assignment_id',
    'slip',
    'status',
    'slip_submission_id',
    'cns_request_id',
    'completed_at',
    'approved_by',
    'approved_at',
])]
class SlipAssignment extends Model
{
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function relatedAssignment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function slipSubmission(): BelongsTo
    {
        return $this->belongsTo(SlipSubmission::class);
    }

    public function cnsRequest(): BelongsTo
    {
        return $this->belongsTo(CnsRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
