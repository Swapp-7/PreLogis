<?php

namespace App\Http\Controllers;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;
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

class SalleController extends Controller
{
    public function index()
    {
        $salles = Salle::all();
        return view('salle', ['salles' => $salles]);
    }
    public function addDate(Carbon $startDate, Carbon $endDate)
    {
        $currentDate = clone $startDate; 
        $moments = MomentEvenement::all();
        $salles = Salle::all();
        while ($currentDate <= $endDate) {
            if ($currentDate->format('N') >= 1 && $currentDate->format('N') <= 7) {
                if (!Dates::where('DATEPLANNING', $currentDate->format('Y-m-d'))->exists()) {
                    $dateToAdd = new Dates();
                    $dateToAdd->DATEPLANNING = $currentDate->format('Y-m-d');
                    $dateToAdd->save();
                }
                foreach($salles as $salle){
                    foreach($moments as $moment){
                        if (!Occupation::where('DATEPLANNING', $currentDate->format('Y-m-d'))
                        ->where('IDSALLE', $salle->IDSALLE)
                        ->where('IDMOMENT', $moment->IDMOMENT)
                        ->exists()) {
                            $ocupation = new Occupation();
                            $ocupation->DATEPLANNING = $currentDate->format('Y-m-d');
                            $ocupation->IDSALLE = $salle->IDSALLE;
                            $ocupation->IDMOMENT = $moment->IDMOMENT;
                            $ocupation->ESTOCCUPEE = false;
                            $ocupation->save();
                        }
                    }
                }
            }
            $currentDate->addDay(); 
        
        }
    }
    public function gererOccupation(Request $request)
    {
        $action = $request->input('action');
        $multi = json_decode($request->input('multi_occupations'), true);

        if (!$multi || !is_array($multi)) {
            return redirect()->back()->with('error', 'Aucune occupation sélectionnée.');
        }

        foreach ($multi as $occ) {
            if ($action === 'add') {
                Occupation::where('DATEPLANNING', $occ['date'])
                    ->where('IDMOMENT', $occ['moment'])
                    ->where('IDSALLE', $occ['salle'])
                    ->update([
                        'IDEVENEMENT' => (int) $request->input('event'),
                        'ESTOCCUPEE' => true,
                    ]);
            } elseif ($action === 'delete') {
                Occupation::where('DATEPLANNING', $occ['date'])
                    ->where('IDMOMENT', $occ['moment'])
                    ->where('IDSALLE', $occ['salle'])
                    ->update([
                        'IDEVENEMENT' => null,
                        'ESTOCCUPEE' => false,
                    ]);
            }
        }

        return redirect()->back()->with('success', 'Action effectuée avec succès.');
    }
    public function getSalle(Request $request, $IdSalle, $weekOffset = 0)
    {
        $salle = Salle::findOrFail($IdSalle);

        // Cas 1 : l'utilisateur vient avec ?date=YYYY-MM-DD
        if ($request->filled('date')) {
            $selectedDate = Carbon::parse($request->input('date'));
            $startDate    = $selectedDate->copy()->startOfWeek();
            $endDate      = $selectedDate->copy()->endOfWeek();

            // Recalculer weekOffset pour garder la cohérence des liens fléchés
            $weekOffset = now()
                ->startOfWeek()
                ->diffInWeeks($startDate, false);
        }
        // Cas 2 : l'utilisateur vient avec ?weekOffset=N (ou valeur par défaut 0)
        else {
            $startDate = now()->startOfWeek()->addWeeks($weekOffset);
            $endDate   = now()->endOfWeek()->addWeeks($weekOffset);
        }

        // Si les dates n'existent pas en base, on les crée
        if (! Dates::whereBetween('DATEPLANNING', [
                $startDate->format('Y-m-d'),
                $endDate  ->format('Y-m-d'),
            ])->exists()
        ) {
            $this->addDate($startDate, $endDate);
        }

        // Chargement des occupations et des données associées
        $occupations = Occupation::with('evenement')
            ->where('IDSALLE', $IdSalle)
            ->whereBetween('DATEPLANNING', [
                $startDate->format('Y-m-d'),
                $endDate  ->format('Y-m-d'),
            ])
            ->get();

        $lesdates   = Dates::whereBetween('DATEPLANNING', [
                $startDate->format('Y-m-d'),
                $endDate  ->format('Y-m-d'),
            ])->get();
        $moments    = MomentEvenement::all();
        $evenements = Evenement::all();

        return view('detailSalle', [
            'salle'       => $salle,
            'dates'       => $lesdates,
            'occupations' => $occupations,
            'moments'     => $moments,
            'evenements'  => $evenements,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'weekOffset'  => $weekOffset,   // indispensable pour tes liens <a>
        ]);
    }
    public function nouvelleOccupation(Request $request)
    {
        $multi = $request->input('multi_occupations');
        if ($multi) {
            $occupations = json_decode($multi, true);

            foreach ($occupations as $occ) {
                Occupation::where('DATEPLANNING', $occ['date'])
                    ->where('IDMOMENT', $occ['moment'])
                    ->where('IDSALLE', $occ['salle'])
                    ->update([
                        'IDEVENEMENT' => (int) $request->input('event'),
                        'ESTOCCUPEE' => true,
                    ]);
            }
        } else {
            // Ancien comportement pour ajout simple
            Occupation::where('DATEPLANNING', $request->input('date'))
                ->where('IDMOMENT', $request->input('moment'))
                ->where('IDSALLE', $request->input('salle'))
                ->update([
                    'IDEVENEMENT' => (int) $request->input('event'),
                    'ESTOCCUPEE' => true,
                ]);
        }

        return redirect()->back()->with('success', 'Occupation(s) ajoutée(s) avec succès.');
    }

    public function lesSalles(Request $request)
    {
        $weekOffset = (int) $request->get('weekOffset', 0);

        $startDate = Carbon::now()->startOfWeek()->addWeeks($weekOffset);
        $endDate = Carbon::now()->endOfWeek()->addWeeks($weekOffset);

        $this->addDate($startDate, $endDate);

        $salles = Salle::all();
        $moments = MomentEvenement::all();

        $occupations = Occupation::with('evenement')
            ->whereBetween('DATEPLANNING', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy(function($item) {
                return $item->IDSALLE . '_' . Carbon::parse($item->DATEPLANNING)->format('Y-m-d');
            });

        $dates = Dates::whereBetween('DATEPLANNING', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('DATEPLANNING')
            ->get();

        return view('lesSalles', [
            'salles' => $salles,
            'moments' => $moments,
            'occupations' => $occupations,
            'dates' => $dates,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'weekOffset' => $weekOffset,
        ]);
    }

   
    
}