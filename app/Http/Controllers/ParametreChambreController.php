<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batiment;
use App\Models\Chambre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParametreChambreController extends Controller
{
    public function index($batimentId)
    {
        $batiment = Batiment::findOrFail($batimentId);
        $chambres = Chambre::where('IDBATIMENT', $batimentId)->get();
        
        return view('parametres.chambres', [
            'batiment' => $batiment,
            'chambres' => $chambres
        ]);
    }
    
    public function store(Request $request, $batimentId)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $batiment = Batiment::findOrFail($batimentId);
        
        // Vérifier si la chambre existe déjà
        $existe = Chambre::where('IDBATIMENT', $batimentId)
                        ->where('NUMEROCHAMBRE', $request->input('numero'))
                        ->exists();
                        
        if ($existe) {
            return redirect()->back()
                ->with('error', 'Une chambre avec ce numéro existe déjà dans ce bâtiment.');
        }
        
        // Créer la nouvelle chambre
        try {
            DB::beginTransaction();
            
            // Générer un ID unique pour la chambre
            $derniereChambrel = Chambre::orderBy('IDCHAMBRE', 'desc')->first();
            $nouveauId = 1;
            if ($derniereChambrel) {
                // Si des chambres existent, incrémenter l'ID de la dernière
                $dernierID = intval($derniereChambrel->IDCHAMBRE);
                $nouveauId = $dernierID + 1;
            }
            
            // Augmenter la capacité du bâtiment
            $batiment->CAPACITE = $batiment->CAPACITE + 1;
            $batiment->save();
            
            $chambre = new Chambre();
            $chambre->IDCHAMBRE = $nouveauId;
            $chambre->IDBATIMENT = $batimentId;
            $chambre->NUMEROCHAMBRE = $request->input('numero');
            $chambre->save();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout de la chambre: ' . $e->getMessage());
        }
        
        return redirect()->route('parametres.chambres', $batimentId)
            ->with('success', 'Chambre ajoutée avec succès. La capacité du bâtiment a été augmentée.');
    }
    
    public function destroy($chambreId)
    {
        try {
            DB::beginTransaction();
            
            $chambre = Chambre::findOrFail($chambreId);
            $batimentId = $chambre->IDBATIMENT;
            
            // Vérifier si la chambre est occupée
            if ($chambre->IDRESIDENT) {
                return redirect()->route('parametres.chambres', $batimentId)
                    ->with('error', 'Impossible de supprimer une chambre occupée.');
            }
            
            // Récupérer le bâtiment et diminuer sa capacité
            $batiment = Batiment::findOrFail($batimentId);
            $batiment->CAPACITE = max(0, $batiment->CAPACITE - 1); // Évite une capacité négative
            $batiment->save();
            
            // Supprimer la chambre
            $chambre->delete();
            
            DB::commit();
            
            return redirect()->route('parametres.chambres', $batimentId)
                ->with('success', 'Chambre supprimée avec succès. La capacité du bâtiment a été mise à jour.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la chambre: ' . $e->getMessage());
        }
    }
}
