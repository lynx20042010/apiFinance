<?php

namespace App\Jobs;

use App\Models\Compte;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAccountArchiving implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->processArchiving();
        $this->processUnarchiving();
        $this->processUnblocking();
    }

    /**
     * Traiter l'archivage automatique des comptes épargne bloqués
     * Archivage : Les comptes épargne bloqués deviennent archivés automatiquement
     */
    private function processArchiving(): void
    {
        // Récupérer tous les comptes épargne bloqués
        $comptesToArchive = Compte::epargneBloque()->get();

        foreach ($comptesToArchive as $compte) {
            try {
                // Vérifier si le compte n'est pas déjà archivé
                if ($compte->statut === 'bloque') {
                    $metadata = $compte->metadata ?? [];
                    $metadata['motifArchivage'] = 'Archivage automatique suite au blocage';
                    $metadata['dateArchivage'] = now()->toISOString();
                    $metadata['dureeArchivage'] = 1825; // 5 ans par défaut
                    $metadata['dateFinArchivage'] = now()->addDays(1825)->toISOString();
                    $metadata['statutAvantArchivage'] = $compte->statut;

                    $compte->update([
                        'statut' => 'ferme', // Changement : bloque -> ferme (archivé)
                        'metadata' => $metadata
                    ]);

                    Log::info("Compte archivé automatiquement (statut ferme)", [
                        'compteId' => $compte->id,
                        'numeroCompte' => $compte->numeroCompte,
                        'dateBlocage' => $metadata['dateBlocage'] ?? null
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'archivage du compte", [
                    'compteId' => $compte->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Traiter le désarchivage automatique des comptes épargne fermés (archivés)
     * Désarchivage : Seulement les comptes épargne fermés dont la date de fin d'archivage est échue peuvent être désarchivés
     */
    private function processUnarchiving(): void
    {
        // Récupérer les comptes épargne fermés (archivés) éligibles au désarchivage
        // La date de fin d'archivage doit être échue
        $comptesToUnarchive = Compte::where('type', 'epargne')
            ->where('statut', 'ferme')
            ->whereNotNull('metadata->dateFinArchivage')
            ->whereRaw("JSON_EXTRACT(metadata, '$.dateFinArchivage') <= ?", [now()->toISOString()])
            ->get();

        foreach ($comptesToUnarchive as $compte) {
            $statutAvantArchivage = $compte->metadata['statutAvantArchivage'] ?? 'actif';

            $metadata = $compte->metadata ?? [];
            $metadata['motifDesarchivage'] = 'Désarchivage automatique suite à expiration de la période d\'archivage';
            $metadata['dateDesarchivage'] = now()->toISOString();
            unset($metadata['motifArchivage'], $metadata['dateArchivage'], $metadata['dureeArchivage'], $metadata['dateFinArchivage'], $metadata['statutAvantArchivage']);

            $compte->update([
                'statut' => $statutAvantArchivage,
                'metadata' => $metadata
            ]);

            Log::info("Compte désarchivé automatiquement", [
                'compteId' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'dateFinArchivage' => $metadata['dateFinArchivage'] ?? null
            ]);
        }
    }

    /**
     * Traiter le déblocage automatique des comptes épargne bloqués
     * Déblocage : Seulement les comptes épargne bloqués dont la date de fin de blocage est échue peuvent être débloqués
     */
    private function processUnblocking(): void
    {
        // Récupérer les comptes épargne bloqués dont la période de blocage est terminée
        // La date de fin de blocage doit être échue
        $comptesToUnblock = Compte::epargneBloque()
            ->whereNotNull('metadata->dateFinBlocage')
            ->whereRaw("JSON_EXTRACT(metadata, '$.dateFinBlocage') <= ?", [now()->toISOString()])
            ->get();

        foreach ($comptesToUnblock as $compte) {
            if ($compte->peutEtreDebloque()) {
                $statutAvantBlocage = $compte->metadata['statutAvantBlocage'] ?? 'actif';

                $metadata = $compte->metadata ?? [];
                $metadata['motifDeblocage'] = 'Déblocage automatique suite à expiration de la période de blocage';
                $metadata['dateDeblocage'] = now()->toISOString();
                unset($metadata['motifBlocage'], $metadata['dateBlocage'], $metadata['dureeBlocage'], $metadata['dateFinBlocage'], $metadata['statutAvantBlocage']);

                $compte->update([
                    'statut' => $statutAvantBlocage,
                    'metadata' => $metadata
                ]);

                Log::info("Compte débloqué automatiquement", [
                    'compteId' => $compte->id,
                    'numeroCompte' => $compte->numeroCompte,
                    'dateFinBlocage' => $metadata['dateFinBlocage'] ?? null
                ]);
            }
        }
    }

    // Méthodes de transfert supprimées car on n'utilise plus render3 pour l'archivage
    // L'archivage se fait maintenant via le statut 'ferme'
}
