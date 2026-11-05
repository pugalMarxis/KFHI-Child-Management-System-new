<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Enumerator extends Model
{
    use HasFactory;

    protected $fillable = [
        'enumerator_code',
        'name',
        'phone',
        'pin_code',
        'status',
        'created_by',
    ];

    protected $hidden = [
        'pin_code',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questionnaires(): BelongsToMany
    {
        return $this->belongsToMany(
            Questionnaire::class,
            'enumerator_questionnaire'
        )->withPivot('assigned_at');
    }
    
    public function allowedChildren(): BelongsToMany
        {
            return $this->belongsToMany(
                Child::class,
                'enumerator_child_assignments'
            )->withPivot('allowed_by', 'include_family_details')->withTimestamps();
        }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function verifyPin(string $pin): bool
    {
        // Support both hashed PIN and direct comparison for initial default pins if plain
        if (str_starts_with($this->pin_code, '$2y$')) {
            return Hash::check($pin, $this->pin_code);
        }

        return hash_equals((string) $this->pin_code, (string) $pin);
    }

    public function setPinCodeAttribute(string $value): void
    {
        $this->attributes['pin_code'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }


    /**
     * Update the shared common password.
     */
   
public static function setCommonPassword(string $password): void
{
    $clean = trim($password);

    static::updateOrCreate(
        ['enumerator_code' => 'GLOBAL_PASSWORD'],
        [
            'name' => 'Global Common Access Password',
            'pin_code' => $clean, // setPinCodeAttribute hashes this value
            'status' => 'active',
            'created_by' => auth()->id() ?? 1,
        ]
    );
}

   
public static function verifyCommonPassword(string $input): bool
{
    $setting = static::where('enumerator_code', 'GLOBAL_PASSWORD')->first();

    if (!$setting || empty($setting->pin_code)) {
        return false;
    }

    return Hash::check(trim($input), $setting->pin_code);
}
}
