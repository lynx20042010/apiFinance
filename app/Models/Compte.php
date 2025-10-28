<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'numeroCompte',
        'type',
        'devise',
        'statut',
        'solde',
        'metadata'
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'metadata' => 'array',
        'id' => 'string',
        'client_id' => 'string'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Mutator pour générer automatiquement le numéro de compte
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($compte) {
            if (empty($compte->numeroCompte)) {
                $compte->numeroCompte = self::generateNumeroCompte();
            }
        });
    }

    /**
     * Générer un numéro de compte unique
     */
    public static function generateNumeroCompte(): string
    {
        do {
            $numero = 'CPT' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('numeroCompte', $numero)->exists());

        return $numero;
    }

    /**
     * Relation avec le client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec les transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relation avec les transactions sortantes (vers d'autres comptes)
     */
    public function transactionsSortantes(): HasMany
    {
        return $this->hasMany(Transaction::class, 'compte_destination_id');
    }

    /**
     * Calculer le solde actuel basé sur les transactions
     */
    public function calculerSolde(): float
    {
        $entrees = $this->transactions()->where('statut', 'traitee')->sum('montant');
        $sorties = $this->transactionsSortantes()->where('statut', 'traitee')->sum('montant');

        return $entrees - $sorties;
    }

    /**
     * Vérifier si le compte a suffisamment de fonds
     */
    public function aSuffisammentDeFonds(float $montant): bool
    {
        return $this->calculerSolde() >= $montant;
    }

    /**
     * Scope pour les comptes locaux (actifs uniquement)
     */
    public function scopeLocalScope($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour les comptes globaux (actifs et inactifs)
     */
    public function scopeGlobalScope($query)
    {
        return $query->whereIn('statut', ['actif', 'inactif']);
    }

    /**
     * Appliquer un scope selon le paramètre
     */
    public function scopeApplyScope($query, string $scope = 'global')
    {
        if ($scope === 'local') {
            return $query->localScope();
        } else {
            return $query->globalScope();
        }
    }
}
