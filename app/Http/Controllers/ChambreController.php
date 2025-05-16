<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

class ChambreController extends Controller
{
    public function index($IdBatiment)
    {
        $chambres = Chambre::where('IDBATIMENT', $IdBatiment)->with('resident')->get();

        return view('chambre', ['chambres' => $chambres]);
    }
    
    public function showDepartingResidents()
    {
        $month = now()->month;
        $year = now()->year;

        // Récupérer les résidents qui partent ce mois/année
        $departingResidents = Resident::whereMonth('DATEDEPART', $month)
            ->whereYear('DATEDEPART', $year)
            ->pluck('IDRESIDENT')
            ->toArray();

        // Récupérer toutes les chambres avec leur résident actuel
        $chambresDepart = Chambre::whereIn('IDRESIDENT', $departingResidents)
            ->with('resident')
            ->get();
        
        // Récupérer les données des futurs résidents pour chaque chambre
        $futureResidentsInfo = [];
        foreach ($chambresDepart as $chambre) {
            // Récupérer TOUS les futurs résidents pour cette chambre
            $futureResidents = $chambre->futureResidents()->get();
            
            if ($futureResidents->count() > 0) {
                // Utilisez une clé composite qui identifie de manière unique chaque chambre
                $key = $chambre->IDBATIMENT . '-' . $chambre->NUMEROCHAMBRE;
                
                // Stocker tous les futurs résidents dans un tableau
                $futureResidentsInfo[$key] = [];
                
                foreach ($futureResidents as $futureResident) {
                    $futureResidentsInfo[$key][] = [
                        'nom' => $futureResident->NOMRESIDENT . ' ' . $futureResident->PRENOMRESIDENT,
                        'DATEINSCRIPTION' => $futureResident->DATEINSCRIPTION
                    ];
                }
            }
        }
        
        // Récupérer les chambres libres
        $chambresLibres = Chambre::whereNull('IDRESIDENT')->get();
        
        $allChambres = $chambresDepart->merge($chambresLibres);
        
        // Reste du code inchangé...
        $allChambres = $allChambres->sortBy([
            ['IDBATIMENT', 'asc'],
            ['NUMEROCHAMBRE', 'asc']
        ]);

        return view('chambresLibre', [
            'chambres' => $allChambres,
            'departingResidents' => $departingResidents,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'futureResidentsInfo' => $futureResidentsInfo
        ]);
    }

    public function filterDepartingResidents(Request $request)
    {
        // Même logique que showDepartingResidents mais avec les paramètres de la requête
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $departingResidents = Resident::whereMonth('DATEDEPART', $month)
            ->whereYear('DATEDEPART', $year)
            ->pluck('IDRESIDENT')
            ->toArray();

        $chambresDepart = Chambre::whereIn('IDRESIDENT', $departingResidents)
            ->with('resident')
            ->get();
        
        // Même modification que dans showDepartingResidents
        $futureResidentsInfo = [];
        foreach ($chambresDepart as $chambre) {
            // Récupérer TOUS les futurs résidents pour cette chambre
            $futureResidents = $chambre->futureResidents()->get();
            
            if ($futureResidents->count() > 0) {
                // Utilisez une clé composite qui identifie de manière unique chaque chambre
                $key = $chambre->IDBATIMENT . '-' . $chambre->NUMEROCHAMBRE;
                
                // Stocker tous les futurs résidents dans un tableau
                $futureResidentsInfo[$key] = [];
                
                foreach ($futureResidents as $futureResident) {
                    $futureResidentsInfo[$key][] = [
                        'nom' => $futureResident->NOMRESIDENT . ' ' . $futureResident->PRENOMRESIDENT,
                        'DATEINSCRIPTION' => $futureResident->DATEINSCRIPTION
                    ];
                }
            }
        }
        
        $chambresLibres = Chambre::whereNull('IDRESIDENT')->get();
        
        $allChambres = $chambresDepart->merge($chambresLibres);
        
        $allChambres = $allChambres->sortBy([
            ['IDBATIMENT', 'asc'],
            ['NUMEROCHAMBRE', 'asc']
        ]);

        return view('chambresLibre', [
            'chambres' => $allChambres,
            'departingResidents' => $departingResidents,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'futureResidentsInfo' => $futureResidentsInfo
        ]);
    }

    public function chambreVide(Request $request)
    {
        return $this->filterDepartingResidents($request);
    }
  
}
