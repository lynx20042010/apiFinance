<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numeroCompte',
        'titulaire',
        'type',
        'devise',
        'statut',
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
     * Relation avec les comptes
     */
    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }

    /**
     * Générer un numéro de compte unique
     */
    public static function generateNumeroCompte(): string
    {
        do {
            $numero = 'CLT' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('numeroCompte', $numero)->exists());

        return $numero;
    }
}
