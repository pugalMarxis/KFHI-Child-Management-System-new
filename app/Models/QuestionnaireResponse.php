<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'child_id',
        'submitted_by',
        'enumerator_id',
        'enumerator_name',
        'primary_identifier_value',
        'primary_identifier_hash',
        'started_at',
        'submitted_at',
        'response_uuid',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'merge_status',
        'merged_by',
        'merged_at',
        'child_snapshot',
        'approved_fields',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'started_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'merged_at' => 'datetime',
            'child_snapshot' => 'array',
            'approved_fields' => 'array',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function enumerator(): BelongsTo
    {
        return $this->belongsTo(Enumerator::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function merger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'response_id');
    }

    public function isPendingReview(): bool
    {
        return in_array($this->review_status, ['submitted', 'under_review'], true);
    }

    public function isApproved(): bool
    {
        return $this->review_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->review_status === 'rejected';
    }

    public function isReturned(): bool
    {
        return $this->review_status === 'returned';
    }

    public function isMerged(): bool
    {
        return $this->merge_status === 'merged';
    }

    public function getAnswerForQuestion(int $questionId)
    {
        $answer = $this->answers->firstWhere('question_id', $questionId);
        if (!$answer) {
            return null;
        }

        return $answer->answer_json ?? $answer->answer_text;
    }
}