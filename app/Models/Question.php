<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'question_text',
        'question_type',
        'is_required',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class);
    }

    public function isOptionType(): bool
    {
        return in_array($this->question_type, ['radio', 'select', 'checkbox', 'poll'], true);
    }

    public function setting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function isPrimaryIdentifier(): bool
    {
        return (bool) $this->setting('is_primary_identifier', false);
    }

    public function mappedChildField(): ?string
    {
        return $this->setting('child_field_mapping');
    }

    public function autofillFromQr(): bool
    {
        return (bool) $this->setting('autofill_from_qr', false);
    }

    public function readonlyForEnumerator(): bool
    {
        return (bool) $this->setting('readonly_for_enumerator', false);
    }
}