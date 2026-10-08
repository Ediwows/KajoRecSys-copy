<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'full_name',
    'last_name',
    'first_name',
    'middle_initial',
    'student_id',
    'email',
    'phone',
    'degree',
    'year_level',
    'school_year',
    'purpose',
    'purpose_other',
    'pdf_path',
    'review_status',
    'approved_by',
    'approved_at',
])]
class CnsRequest extends Model
{
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
