<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'status',
        'public_token',
        'primary_identifier_settings',
        'child_field_mapping',
        'requires_enumerator',
        'allow_anonymous',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'primary_identifier_settings' => 'array',
            'child_field_mapping' => 'array',
            'requires_enumerator' => 'boolean',
            'allow_anonymous' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function enumerators(): BelongsToMany
    {
        return $this->belongsToMany(
            Enumerator::class,
            'enumerator_questionnaire'
        )->withPivot('assigned_at');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->public_token) {
            return null;
        }

        return url('/forms/' . $this->public_token);
    }

    public function getWhatsAppShareUrlAttribute(): ?string
    {
        $url = $this->public_url;
        if (!$url) {
            return null;
        }

        $text = "Please complete this survey for '{$this->title}': " . $url;
        return 'https://api.whatsapp.com/send?text=' . urlencode($text);
    }

    public function ensurePublicToken(): string
    {
        if (empty($this->public_token)) {
            $this->public_token = Str::random(40);
            $this->save();
        }

        return $this->public_token;
    }
}