<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resident;
use App\Models\Chambre;
use Carbon\Carbon;

class AssignerChambres extends Command
{
    protected $signature = 'chambres:assigner';
    protected $description = 'Assigne automatiquement les chambres aux résidents selon leur date d\'inscription';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Début de l\'assignation automatique des chambres...');
        
        // Trouver tous les résidents qui doivent entrer aujourd'hui mais qui n'ont pas de chambre assignée
        $residents = Resident::whereDate('DATEINSCRIPTION', '<=', Carbon::today())
                          ->whereNotNull('CHAMBREASSIGNE')
                          ->whereDoesntHave('chambre')
                          ->get();
        
        $count = 0;
        foreach ($residents as $resident) {
            // Extraire le code bâtiment et numéro de chambre
            // Récupérer directement la chambre avec l'identifiant stocké
            $chambreId = $resident->CHAMBREASSIGNE;
            
            // Trouver la chambre correspondante directement par son ID
            $chambre = Chambre::find($chambreId);
            
            if ($chambre && $chambre->IDRESIDENT == null) {
                $chambre->IDRESIDENT = $resident->IDRESIDENT;
                $chambre->save();
                $count++;
                $this->info("Chambre {$chambre->IDBATIMENT}{$chambre->NUMEROCHAMBRE} assignée à {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT}");
            } else {
                $this->warn("Impossible d'assigner la chambre {$resident->CHAMBREASSIGNE} à {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT} - Chambre non disponible ou déjà occupée");
            }
        }
        
        $this->info("Attribution terminée: $count chambres assignées");
        
        return Command::SUCCESS;
    }
}