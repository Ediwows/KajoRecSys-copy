<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'referral_id', 'scheduled_at', 'duration_minutes', 'purpose', 'concerns', 'status', 'decline_reason', 'guidance_message', 'reschedule_reason', 'reviewed_by', 'reviewed_at', 'action_taken', 'completed_at', 'attachments'])]
class Appointment extends Model
{
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(SlipAssignment::class, 'referral_id');
    }
}
