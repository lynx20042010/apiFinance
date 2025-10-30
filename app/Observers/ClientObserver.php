<?php

namespace App\Observers;

use App\Models\Client;

class ClientObserver
{
    /**
     * Handle the Client "creating" event.
     */
    public function creating(Client $client): void
    {
        // Générer un numéro de compte unique si non fourni
        if (empty($client->numeroCompte)) {
            $client->numeroCompte = Client::generateNumeroCompte();
        }

        // Définir le type par défaut si non fourni
        if (empty($client->type)) {
            $client->type = 'particulier';
        }

        // Définir la devise par défaut si non fourni
        if (empty($client->devise)) {
            $client->devise = 'XAF';
        }

        // Définir le statut par défaut si non fourni
        if (empty($client->statut)) {
            $client->statut = 'actif';
        }

        // Initialiser les métadonnées si non fournies
        if (empty($client->metadata)) {
            $client->metadata = [
                'date_creation' => now()->toISOString(),
                'version' => 1,
                'statut_initial' => $client->statut
            ];
        }

        // Log de création
        \Illuminate\Support\Facades\Log::info('Création d\'un nouveau client', [
            'client_id' => $client->id ?? 'non défini',
            'numero_compte' => $client->numeroCompte,
            'titulaire' => $client->titulaire,
            'type' => $client->type
        ]);
    }

    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        // Mettre à jour les métadonnées après création
        $currentMetadata = is_array($client->metadata) ? $client->metadata : json_decode($client->metadata ?? '{}', true);
        $client->update([
            'metadata' => array_merge($currentMetadata, [
                'date_creation_confirmee' => now()->toISOString(),
                'statut_creation' => 'succès'
            ])
        ]);

        // Log de succès
        \Illuminate\Support\Facades\Log::info('Client créé avec succès', [
            'client_id' => $client->id,
            'numero_compte' => $client->numeroCompte,
            'titulaire' => $client->titulaire
        ]);

        // TODO: Envoyer notification de bienvenue
        // TODO: Créer historique d'activité
    }

    /**
     * Handle the Client "updating" event.
     */
    public function updating(Client $client): void
    {
        // Validation des changements
        $original = $client->getOriginal();

        // Empêcher le changement de numéro de compte
        if (isset($original['numeroCompte']) && $client->numeroCompte !== $original['numeroCompte']) {
            throw new \Exception('Le numéro de compte ne peut pas être modifié.');
        }

        // Validation du changement de statut
        $statutsAutorises = ['actif', 'inactif', 'suspendu'];
        if (!in_array($client->statut, $statutsAutorises)) {
            throw new \Exception('Statut client invalide.');
        }

        // Mettre à jour les métadonnées
        $metadata = $client->metadata ?? [];
        $metadata['derniere_modification'] = now()->toISOString();
        $metadata['version'] = ($metadata['version'] ?? 1) + 1;
        $client->metadata = $metadata;

        // Log de mise à jour
        \Illuminate\Support\Facades\Log::info('Mise à jour du client', [
            'client_id' => $client->id,
            'changements' => $client->getDirty()
        ]);
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        // Actions après mise à jour réussie
        $dirty = $client->getDirty();

        // Si le statut a changé, effectuer des actions spécifiques
        if (isset($dirty['statut'])) {
            if ($client->statut === 'suspendu') {
                // TODO: Suspendre tous les comptes associés
                // TODO: Envoyer notification de suspension
            } elseif ($client->statut === 'actif') {
                // TODO: Réactiver les comptes
                // TODO: Envoyer notification de réactivation
            }
        }

        // Log de succès
        \Illuminate\Support\Facades\Log::info('Client mis à jour avec succès', [
            'client_id' => $client->id,
            'champs_modifies' => array_keys($dirty)
        ]);
    }

    /**
     * Handle the Client "deleting" event.
     */
    public function deleting(Client $client): void
    {
        // Vérifier s'il y a des comptes actifs
        $comptesActifs = $client->comptes()->where('statut', 'actif')->count();

        if ($comptesActifs > 0) {
            throw new \Exception('Impossible de supprimer un client avec des comptes actifs.');
        }

        // Log de suppression
        \Illuminate\Support\Facades\Log::warning('Suppression du client', [
            'client_id' => $client->id,
            'numero_compte' => $client->numeroCompte,
            'titulaire' => $client->titulaire,
            'comptes_total' => $client->comptes()->count()
        ]);
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        // Actions après suppression
        // TODO: Archiver les données sensibles
        // TODO: Notifier les administrateurs
        // TODO: Supprimer les tokens associés

        // Log de succès
        \Illuminate\Support\Facades\Log::info('Client supprimé définitivement', [
            'client_id' => $client->id,
            'numero_compte' => $client->numeroCompte
        ]);
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        //
    }
}
