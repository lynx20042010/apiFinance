<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    use HasFactory;

    protected $connection = 'render';

    protected $fillable = [
        'user_id',
        'role',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'id' => 'string',
        'user_id' => 'string'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si c'est un super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Vérifier si c'est un admin normal
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si c'est un modérateur
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Générer un UUID pour le nouvel admin
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->id)) {
                $admin->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
