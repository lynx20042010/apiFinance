<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    use HasFactory;

    protected $connection = 'neon';

    /**
     * Get the appropriate connection based on account status
     */
    public function getConnectionName()
    {
        // Use Neon only for blocked accounts
        if ($this->statut === 'bloque') {
            return 'neon';
        }

        // Use default connection for all other accounts
        return config('database.default');
    }

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
            // Générer un UUID pour l'ID si non fourni
            if (empty($compte->id)) {
                $compte->id = (string) \Illuminate\Support\Str::uuid();
            }

            // Générer un numéro de compte unique si non fourni
            if (empty($compte->numeroCompte)) {
                $compte->numeroCompte = self::generateNumeroCompte();
            }

            // Définir le statut par défaut
            if (empty($compte->statut)) {
                $compte->statut = 'actif';
            }

            // Initialiser les métadonnées
            if (empty($compte->metadata)) {
                $compte->metadata = [
                    'date_creation' => now()->toISOString(),
                    'solde_initial' => $compte->solde ?? 0,
                    'version' => 1
                ];
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

    /**
     * Scope pour les comptes épargne bloqués
     */
    public function scopeEpargneBloque($query)
    {
        return $query->where('type', 'epargne')
                    ->where('statut', 'bloque');
    }

    /**
     * Vérifier si le compte peut être archivé
     * Un compte épargne bloqué peut être archivé si la date de début de blocage est échue (plus de 30 jours)
     */
    public function peutEtreArchive(): bool
    {
        if ($this->type !== 'epargne' || $this->statut !== 'bloque') {
            return false;
        }

        $dateBlocage = $this->metadata['dateBlocage'] ?? null;
        if (!$dateBlocage) {
            return false;
        }

        return \Carbon\Carbon::parse($dateBlocage)->addDays(30)->isPast();
    }

    /**
     * Vérifier si le compte peut être désarchivé
     * Un compte épargne bloqué peut être désarchivé si la date de fin de blocage est échue
     */
    public function peutEtreDesarchive(): bool
    {
        if ($this->type !== 'epargne' || $this->statut !== 'bloque') {
            return false;
        }

        $dateFinBlocage = $this->metadata['dateFinBlocage'] ?? null;
        if (!$dateFinBlocage) {
            return false;
        }

        return \Carbon\Carbon::parse($dateFinBlocage)->isPast();
    }

    /**
     * Vérifier si le compte peut être débloqué
     * Alias pour peutEtreDesarchive selon les règles métier
     */
    public function peutEtreDebloque(): bool
    {
        return $this->peutEtreDesarchive();
    }
}
