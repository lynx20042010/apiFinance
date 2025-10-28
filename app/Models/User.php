<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'id' => 'string',
    ];

    /**
     * Indicate that the model's ID is not incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * Relation avec le client
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Relation avec l'admin
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Vérifier si l'utilisateur est un client
     */
    public function isClient(): bool
    {
        return $this->client()->exists();
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    /**
     * Obtenir le rôle de l'utilisateur
     */
    public function getRole(): ?string
    {
        if ($this->isAdmin()) {
            return $this->admin->role;
        }
        return 'client';
    }

    /**
     * Générer un UUID pour le nouvel utilisateur
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->id)) {
                $user->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
