<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_key',
        'api_key_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'api_key' => 'encrypted',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            // Check if the api_key is dirty (has been changed)
            if ($user->isDirty('api_key')) {
                $user->api_key_hash = $user->api_key;
            }
        });
    }

    /**
     * Get the user's scrape records.
     *
     * @return HasMany
     */
    public function scrapeRecords(): HasMany
    {
        return $this->hasMany(ScrapeRecord::class);
    }

    /**
     * Interact with the user's api key hash.
     *
     * @return Attribute
     */
    protected function apiKeyHash(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => hash_hmac(
                'sha256',
                $value,
                config('app.key')
            )
        );
    }
}
