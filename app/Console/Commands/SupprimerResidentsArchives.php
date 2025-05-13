<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Adress;
use App\Models\Batiment;
use App\Models\Chambre;
use App\Models\Dates;
use App\Models\Evenement;
use App\Models\Fichier;
use App\Models\MomentEvenement;
use App\Models\Occupation;
use App\Models\Parents;
use App\Models\Resident;
use App\Models\ResidentArchive;
use App\Models\Salle;

class SupprimerResidentsArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:supprimer-residents-archives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les résidents archivés depuis plus de 3 ans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limite = now()->subYears(3);

        $residents = ResidentArchive::where('DATEARCHIVE', '<=', $limite)->get();

        foreach ($residents as $resident) {
            // Supprimer les relations (ex: parents)
            foreach ($resident->parents as $parent) {
                $parent->pivot->delete(); 
                $parent->delete();
            }
            $resident->adresse->delete();

            // Supprimer les fichiers associés si besoin
            foreach ($resident->fichiers as $fichier) {
                Storage::delete($fichier->CHEMINFICHIER); // si stocké via le Storage facade
                $fichier->delete();
            }

            // Puis supprimer le résident archivé
            $resident->delete();
        }

        $this->info('Résidents archivés depuis plus de 3 ans supprimés.');
    }
}
