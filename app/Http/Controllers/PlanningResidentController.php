<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PlanningResidentExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Models\Adresse;
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

class PlanningResidentController extends Controller
{
    public function index(Request $request)
    {
       // Récupérer le mois et l'année demandés ou utiliser le mois actuel
       $month = $request->input('month', now()->month);
       $year = $request->input('year', now()->year);
       
       $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
       $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
       
       $chambres = Chambre::all();
       
       // Récupérer les résidents qui partent ce mois-ci avec leurs chambres
       $departs = Resident::with('chambre')
           ->whereNotNull('DATEDEPART')
           ->whereDate('DATEDEPART', '>=', $startOfMonth)
           ->whereDate('DATEDEPART', '<=', $endOfMonth)
           ->get();
       
       // Récupérer les résidents qui arrivent ce mois-ci avec leurs chambres
       $arrivées = Resident::with('chambre')
           ->whereDate('DATEINSCRIPTION', '>=', $startOfMonth)
           ->whereDate('DATEINSCRIPTION', '<=', $endOfMonth)
           ->get();
       
       // Combiner arrivées et départs pour l'affichage du calendrier
       $residents = $departs->merge($arrivées)->unique('IDRESIDENT');
       
       return view('planning-resident', compact('chambres', 'residents', 'departs', 'arrivées', 'startOfMonth', 'endOfMonth', 'month', 'year'));
   }

   public function exportExcel(Request $request)
   {
       // Augmenter la limite de temps d'exécution pour cette requête
       ini_set('max_execution_time', 300); // 5 minutes
       
       // Récupérer l'année demandée ou utiliser l'année actuelle
       $year = $request->input('year', date('Y'));
       $fileName = 'planning_residents_' . $year . '.xlsx';
       
       // Téléchargement direct (sans utiliser de job)
       return Excel::download(new PlanningResidentExport($year), $fileName);
   }
    
}