<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resident;
use App\Http\Controllers\ResidentController;
use Carbon\Carbon;

class RemoveDepartedResidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'residents:remove-departed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime automatiquement les résidents dont la date de départ est passée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Trouver tous les résidents dont la date de départ est passée
        $departedResidents = Resident::whereNotNull('DATEDEPART')
            ->where('DATEDEPART', '<', $today)
            ->get();
            
        $count = 0;
        
        foreach ($departedResidents as $resident) {
            $this->info("Suppression du résident: {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT}");
            
            // Utiliser la méthode existante pour supprimer le résident
            app(ResidentController::class)->supprimerResident($resident->IDRESIDENT,true);
            
            $count++;
        }
        
        $this->info("Opération terminée. {$count} résident(s) supprimé(s).");
        
        return Command::SUCCESS;
    }
}