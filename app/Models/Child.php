<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributes that can be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'child_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'school_name',
        'grade',
        'address',
        'district',
        'gn_division',
        'profile_photo',
        'status',
        'notes',
        'created_by',
        'custom_fields',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'custom_fields' => 'array',
        ];
    }

    /**
     * User who created this child record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Guardians of this child.
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function allowedEnumerators(): BelongsToMany
    {
        return $this->belongsToMany(
            Enumerator::class,
            'enumerator_child_assignments'
        )->withPivot('allowed_by', 'include_family_details')->withTimestamps();
    }


    /**
     * QR code assigned to this child.
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QRCode::class)->latestOfMany();
    }

    /**
     * Digital ID assigned to this child.
     */
    public function digitalId(): HasOne
    {
        return $this->hasOne(DigitalId::class);
    }

    /**
     * Attendance records for this child.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Follow-up records for this child.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Additional information records for this child.
     */
    public function additionalInformation(): HasMany
    {
        return $this->hasMany(ChildInformation::class);
    }

    /**
     * Questionnaire responses for this child.
     */
    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }
}
