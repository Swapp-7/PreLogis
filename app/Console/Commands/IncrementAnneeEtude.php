<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resident;
use Carbon\Carbon;

class IncrementAnneeEtude extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'residents:increment-annee-etude {--force : Force l\'exécution même si ce n\'est pas le 1er septembre}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incrémente les années d\'étude des résidents le 1er septembre de chaque année';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier si nous sommes le 1er septembre (sauf si --force est utilisé)
        $today = Carbon::now();
        if (!$this->option('force') && !($today->month == 9 && $today->day == 1)) {
            $this->info('Cette commande ne s\'exécute que le 1er septembre. Utilisez --force pour forcer l\'exécution.');
            return 0;
        }

        $this->info('Début de l\'incrémentation des années d\'étude...');

        // Définir les règles d'incrémentation
        $incrementRules = [
            '1re' => '2e',
            '2e' => '3e',
            '3e' => '4e',
            '4e' => '5e',
            '5e' => 'jeune travailleur',
            '6e' => '7e',
            '7e' => 'jeune travailleur',
            'L1' => 'L2',
            'L2' => 'L3',
            'L3' => 'L4',
            'L4' => 'L5',
            'L5' => 'jeune travailleur',
            'M1' => 'M2',
            'M2' => 'jeune travailleur',
            '1ème' => '2ème',
            '2ème' => '3ème',
            '3ème' => '4ème',
            '4ème' => '5ème',
            '5ème' => 'jeune travailleur',
        ];

        $totalUpdated = 0;
        $statistics = [];

        foreach ($incrementRules as $currentYear => $nextYear) {
            // Récupérer tous les résidents avec l'année d'étude actuelle
            $residents = Resident::where('ANNEEETUDE', $currentYear)->get();
            
            if ($residents->count() > 0) {
                $this->line("Mise à jour des résidents en {$currentYear} vers {$nextYear}...");
                
                // Mettre à jour chaque résident
                foreach ($residents as $resident) {
                    $resident->ANNEEETUDE = $nextYear;
                    $resident->save();
                    
                    $this->line("  - {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT}: {$currentYear} → {$nextYear}");
                }
                
                $statistics[$currentYear] = [
                    'count' => $residents->count(),
                    'to' => $nextYear
                ];
                
                $totalUpdated += $residents->count();
            }
        }

        // Afficher les statistiques
        $this->info("\n=== Résumé de la mise à jour ===");
        if ($totalUpdated > 0) {
            foreach ($statistics as $from => $data) {
                $this->line("• {$data['count']} résident(s) : {$from} → {$data['to']}");
            }
            $this->info("Total : {$totalUpdated} résident(s) mis à jour.");
        } else {
            $this->info("Aucun résident à mettre à jour.");
        }

        // Vérifier les résidents sans année d'étude définie
        $residentsWithoutYear = Resident::whereNull('ANNEEETUDE')
            ->orWhere('ANNEEETUDE', '')
            ->orWhere('ANNEEETUDE', 'Non renseigné')
            ->count();

        if ($residentsWithoutYear > 0) {
            $this->warn("Attention : {$residentsWithoutYear} résident(s) n'ont pas d'année d'étude définie et n'ont pas été mis à jour.");
        }

        // Afficher les "jeunes travailleurs" qui ne changent pas
        $jeunesTravailleurs = Resident::where('ANNEEETUDE', 'jeune travailleur')->count();
        if ($jeunesTravailleurs > 0) {
            $this->line("{$jeunesTravailleurs} jeune(s) travailleur(s) conservent leur statut.");
        }

        $this->info("Incrémentation des années d'étude terminée avec succès !");
        
        return 0;
    }
}
