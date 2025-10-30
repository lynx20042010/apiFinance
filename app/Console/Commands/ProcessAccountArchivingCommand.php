<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAccountArchiving;
use Illuminate\Console\Command;

class ProcessAccountArchivingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comptes:process-archiving {--sync : Exécuter de manière synchrone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter automatiquement l\'archivage, le désarchivage et le déblocage des comptes épargne';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début du traitement automatique des comptes épargne...');

        if ($this->option('sync')) {
            // Exécution synchrone pour les tests
            $job = new ProcessAccountArchiving();
            $job->handle();
            $this->info('Traitement terminé (mode synchrone).');
        } else {
            // Dispatch du job en file d'attente
            ProcessAccountArchiving::dispatch();
            $this->info('Job de traitement des comptes épargne ajouté à la file d\'attente.');
        }

        return Command::SUCCESS;
    }
}
