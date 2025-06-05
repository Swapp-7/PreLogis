<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salle;
use App\Models\Dates;
use App\Models\Occupation;
use App\Models\MomentEvenement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ParametreSalleController extends Controller
{
    public function index()
    {
        $salles = Salle::all();
        return view('parametres.salles', ['salles' => $salles]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Générer un ID unique pour la salle
            $derniereSalle = Salle::orderBy('IDSALLE', 'desc')->first();
            $nouveauId = 1;
            if ($derniereSalle) {
                $dernierID = intval($derniereSalle->IDSALLE);
                $nouveauId = $dernierID + 1;
            }
            
            $salle = new Salle();
            $salle->IDSALLE = $nouveauId;
            $salle->LIBELLESALLE = $request->input('libelle');
            $salle->save();
            
            // Générer les occupations vides pour toutes les dates existantes
            $this->generateOccupationsForNewSalle($nouveauId);
            
            DB::commit();
            
            return redirect()->route('parametres.salles')
                ->with('success', 'Salle ajoutée avec succès.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout de la salle: ' . $e->getMessage());
        }
    }
    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $salle = Salle::findOrFail($id);
        $salle->LIBELLESALLE = $request->input('libelle');
        $salle->save();
        
        return redirect()->route('parametres.salles')->with('success', 'Salle modifiée avec succès.');
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $salle = Salle::findOrFail($id);
            
            // Vérifier si la salle a des occupations
            $occupationsCount = DB::table('OCCUPATION')
                ->where('IDSALLE', $id)
                ->where('ESTOCCUPEE', true)
                ->count();
                
            if ($occupationsCount > 0) {
                return redirect()->route('parametres.salles')
                    ->with('error', 'Impossible de supprimer cette salle car elle a des occupations actives.');
            }
            
            // Supprimer toutes les occupations liées à cette salle
            DB::table('OCCUPATION')->where('IDSALLE', $id)->delete();
            
            // Supprimer la salle
            $salle->delete();
            
            DB::commit();
            
            return redirect()->route('parametres.salles')
                ->with('success', 'Salle supprimée avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('parametres.salles')
                ->with('error', 'Erreur lors de la suppression de la salle : ' . $e->getMessage());
        }
    }
    
    /**
     * Génère les occupations vides pour une nouvelle salle sur toutes les dates existantes
     */
    private function generateOccupationsForNewSalle($salleId)
    {
        $dates = Dates::all();
        $moments = MomentEvenement::all();
        
        foreach ($dates as $date) {
            foreach ($moments as $moment) {
                // Vérifier si l'occupation n'existe pas déjà
                $existingOccupation = Occupation::where('DATEPLANNING', $date->DATEPLANNING)
                    ->where('IDSALLE', $salleId)
                    ->where('IDMOMENT', $moment->IDMOMENT)
                    ->exists();
                    
                if (!$existingOccupation) {
                    $occupation = new Occupation();
                    $occupation->DATEPLANNING = $date->DATEPLANNING;
                    $occupation->IDSALLE = $salleId;
                    $occupation->IDMOMENT = $moment->IDMOMENT;
                    $occupation->ESTOCCUPEE = false;
                    $occupation->save();
                }
            }
        }
    }
}
