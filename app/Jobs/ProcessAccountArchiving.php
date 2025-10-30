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
     * Archivage : Seulement les comptes épargne bloqués dont la date de début de blocage est échue peuvent être archivés
     */
    private function processArchiving(): void
    {
        // Récupérer les comptes épargne bloqués éligibles à l'archivage
        // La date de début de blocage doit être échue (plus de 30 jours)
        $comptesToArchive = Compte::epargneBloque()
            ->whereNotNull('metadata->dateBlocage')
            ->whereRaw("JSON_EXTRACT(metadata, '$.dateBlocage') <= ?", [Carbon::now()->subDays(30)->toISOString()])
            ->get();

        foreach ($comptesToArchive as $compte) {
            if ($compte->peutEtreArchive()) {
                try {
                    // Transférer le compte vers Neon avant archivage
                    $this->transferToNeon($compte);

                    $metadata = $compte->metadata ?? [];
                    $metadata['motifArchivage'] = 'Archivage automatique suite à blocage prolongé';
                    $metadata['dateArchivage'] = now()->toISOString();
                    $metadata['dureeArchivage'] = 1825; // 5 ans par défaut
                    $metadata['dateFinArchivage'] = now()->addDays(1825)->toISOString();
                    $metadata['statutAvantArchivage'] = $compte->statut;
                    $metadata['transfereVersNeon'] = true;

                    $compte->update([
                        'statut' => 'archive',
                        'metadata' => $metadata
                    ]);

                    Log::info("Compte archivé automatiquement et transféré vers Neon", [
                        'compteId' => $compte->id,
                        'numeroCompte' => $compte->numeroCompte,
                        'dateBlocage' => $metadata['dateBlocage'] ?? null
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'archivage du compte", [
                        'compteId' => $compte->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Traiter le désarchivage automatique des comptes épargne bloqués
     * Désarchivage : Seulement les comptes épargne bloqués dont la date de fin de blocage est échue peuvent être désarchivés
     */
    private function processUnarchiving(): void
    {
        // Récupérer les comptes épargne bloqués éligibles au désarchivage
        // La date de fin de blocage doit être échue
        $comptesToUnarchive = Compte::epargneBloque()
            ->whereNotNull('metadata->dateFinBlocage')
            ->whereRaw("JSON_EXTRACT(metadata, '$.dateFinBlocage') <= ?", [now()->toISOString()])
            ->get();

        foreach ($comptesToUnarchive as $compte) {
            if ($compte->peutEtreDesarchive()) {
                $statutAvantArchivage = $compte->metadata['statutAvantArchivage'] ?? 'actif';

                $metadata = $compte->metadata ?? [];
                $metadata['motifDesarchivage'] = 'Désarchivage automatique suite à expiration de la période de blocage';
                $metadata['dateDesarchivage'] = now()->toISOString();
                unset($metadata['motifBlocage'], $metadata['dateBlocage'], $metadata['dureeBlocage'], $metadata['dateFinBlocage'], $metadata['statutAvantBlocage']);

                $compte->update([
                    'statut' => $statutAvantArchivage,
                    'metadata' => $metadata
                ]);

                Log::info("Compte désarchivé automatiquement", [
                    'compteId' => $compte->id,
                    'numeroCompte' => $compte->numeroCompte,
                    'dateFinBlocage' => $metadata['dateFinBlocage'] ?? null
                ]);
            }
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
                try {
                    // Transférer le compte depuis Neon avant déblocage (si il était archivé)
                    if (isset($compte->metadata['transfereVersNeon']) && $compte->metadata['transfereVersNeon']) {
                        $this->transferFromNeon($compte);
                    }

                    $statutAvantBlocage = $compte->metadata['statutAvantBlocage'] ?? 'actif';

                    $metadata = $compte->metadata ?? [];
                    $metadata['motifDeblocage'] = 'Déblocage automatique suite à expiration de la période de blocage';
                    $metadata['dateDeblocage'] = now()->toISOString();
                    unset($metadata['motifBlocage'], $metadata['dateBlocage'], $metadata['dureeBlocage'], $metadata['dateFinBlocage'], $metadata['statutAvantBlocage'], $metadata['transfereVersNeon']);

                    $compte->update([
                        'statut' => $statutAvantBlocage,
                        'metadata' => $metadata
                    ]);

                    Log::info("Compte débloqué automatiquement", [
                        'compteId' => $compte->id,
                        'numeroCompte' => $compte->numeroCompte,
                        'dateFinBlocage' => $metadata['dateFinBlocage'] ?? null
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erreur lors du déblocage du compte", [
                        'compteId' => $compte->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Transférer un compte vers la base Neon
     */
    private function transferToNeon(Compte $compte): void
    {
        DB::connection('neon')->table('comptes')->insert([
            'id' => $compte->id,
            'client_id' => $compte->client_id,
            'numeroCompte' => $compte->numeroCompte,
            'type' => $compte->type,
            'devise' => $compte->devise,
            'statut' => 'archive',
            'solde' => $compte->solde,
            'metadata' => json_encode($compte->metadata),
            'created_at' => $compte->created_at,
            'updated_at' => now(),
        ]);
    }

    /**
     * Transférer un compte depuis la base Neon
     */
    private function transferFromNeon(Compte $compte): void
    {
        // Supprimer le compte de Neon
        DB::connection('neon')->table('comptes')->where('id', $compte->id)->delete();
    }
}
