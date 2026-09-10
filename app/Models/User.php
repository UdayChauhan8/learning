<?php

namespace App\Models;

use App\Concerns\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\Contracts\OAuthenticatable;   // ← NEW: Passport interface
use Laravel\Passport\HasApiTokens;                  // ← NEW: Passport trait

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Role> $roles
 */
// REMOVED: two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at
// Those columns belong to Fortify. We no longer use them.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

// CHANGED: implements OAuthenticatable instead of PasskeyUser
// OAuthenticatable is the Passport contract. It tells Passport how to find
// the user from a token. Without it, Passport throws a runtime error.
class User extends Authenticatable implements OAuthenticatable
{
    /**
     * REMOVED: PasskeyAuthenticatable, TwoFactorAuthenticatable (Fortify traits)
     * ADDED:   HasApiTokens (Passport trait — gives createToken(), tokens() relation)
     *
     * @use HasFactory<UserFactory>
     */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            // REMOVED: two_factor_confirmed_at cast (no longer needed)
        ];
    }
}