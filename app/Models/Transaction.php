<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $connection = 'render';

    protected $fillable = [
        'compte_id',
        'type',
        'montant',
        'devise',
        'description',
        'compte_destination_id',
        'statut',
        'date_execution',
        'metadata'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_execution' => 'datetime',
        'metadata' => 'array',
        'id' => 'string',
        'compte_id' => 'string',
        'compte_destination_id' => 'string'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Relation avec le compte source
     */
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }

    /**
     * Relation avec le compte destination (si applicable)
     */
    public function compteDestination(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'compte_destination_id');
    }

    /**
     * Vérifier si la transaction est un débit (sortie d'argent)
     */
    public function isDebit(): bool
    {
        return in_array($this->type, ['retrait', 'virement', 'transfert', 'commission']);
    }

    /**
     * Vérifier si la transaction est un crédit (entrée d'argent)
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['depot', 'interet']);
    }

    /**
     * Vérifier si la transaction est interne (entre comptes de la même banque)
     */
    public function isInterne(): bool
    {
        return !is_null($this->compte_destination_id);
    }

    /**
     * Calculer l'impact sur le solde du compte source
     */
    public function impactSurSolde(): float
    {
        if ($this->isCredit()) {
            return $this->montant;
        } elseif ($this->isDebit()) {
            return -$this->montant;
        }
        return 0;
    }

    /**
     * Marquer la transaction comme traitée
     */
    public function marquerCommeTraitee(): bool
    {
        return $this->update([
            'statut' => 'traitee',
            'date_execution' => now()
        ]);
    }

    /**
     * Annuler la transaction
     */
    public function annuler(): bool
    {
        return $this->update(['statut' => 'annulee']);
    }
}
