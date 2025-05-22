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
use Carbon\Carbon;

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
        $today = now();
        $chambresLibre = Chambre::all();
        return view('chambresLibre', [
            'chambresLibre' => $chambresLibre,
            'month' => $month,
            'year' => $year, // Assurez-vous que cette ligne est présente
        ]);
    }
        
    public function filterDepartingResidents(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Récupérer toutes les chambres avec leurs résidents et futurs résidents
        $chambresLibre = Chambre::with(['resident', 'futureResidents' => function($query) {
            $query->orderBy('DATEINSCRIPTION', 'asc');
        }])->get();
        
        return view('chambresLibre', [
            'chambresLibre' => $chambresLibre,
            'month' => $month,
            'year' => $year
        ]);
    }
    

    public function chambreVide(Request $request)
    {
        return $this->filterDepartingResidents($request);
    }
}