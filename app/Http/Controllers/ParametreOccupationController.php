<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Occupation;
use App\Models\Dates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParametreOccupationController extends Controller
{
    public function index()
    {
        return view('parametres.optimisation-occupation');
    }

    public function optimiserOccupations(Request $request)
    {
        $debutSemaineActuelle = Carbon::now()->startOfWeek();
        
        $request->validate([
            'date_limite' => 'required|date|before:' . $debutSemaineActuelle->format('Y-m-d'),
        ], [
            'date_limite.before' => 'La date limite doit être antérieure au début de la semaine actuelle (' . $debutSemaineActuelle->format('d/m/Y') . ').'
        ]);

        $dateLimite = Carbon::parse($request->date_limite);

        // Récupérer les dates à supprimer
        $datesPlanning = Dates::where('DATEPLANNING', '<', $dateLimite)->pluck('DATEPLANNING');

        // Supprimer les occupations liées à ces dates
        $nombreOccupationsSupprimees = Occupation::whereIn('DATEPLANNING', $datesPlanning)->delete();
        
        // Supprimer les dates elles-mêmes
        $nombreDatesSupprimees = Dates::whereIn('DATEPLANNING', $datesPlanning)->delete();

        return redirect()->route('parametres.optimisation-occupation')->with([
            'success' => true,
            'message' => "Optimisation réussie ! $nombreOccupationsSupprimees occupations et $nombreDatesSupprimees dates antérieures au " . $dateLimite->format('d/m/Y') . " ont été supprimées."
        ]);
    }
}
