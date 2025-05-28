<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batiment;
use App\Models\Chambre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParametreBatimentController extends Controller
{
    public function index()
    {
        $batiments = Batiment::where('IDBATIMENT', '!=', 'Accueil')->get();
        return view('parametres.batiments', ['batiments' => $batiments]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'capacite' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Création du bâtiment
            $batiment = new Batiment();
            $batiment->IDBATIMENT = $request->input('nom'); // Utilisation du nom comme ID
            $batiment->CAPACITE = $request->input('capacite');
            $batiment->save();
            
            // Création automatique des chambres selon la capacité
            $capacite = $request->input('capacite');
            for ($i = 1; $i <= $capacite; $i++) {
                // Générer un ID unique pour la chambre
                $derniereChambrel = Chambre::orderBy('IDCHAMBRE', 'desc')->first();
                $nouveauId = 1;
                if ($derniereChambrel) {
                    // Si des chambres existent, incrémenter l'ID de la dernière
                    $dernierID = intval($derniereChambrel->IDCHAMBRE);
                    $nouveauId = $dernierID + 1;
                }
                
                // Créer la chambre
                $chambre = new Chambre();
                $chambre->IDCHAMBRE = $nouveauId;
                $chambre->IDBATIMENT = $batiment->IDBATIMENT;
                $chambre->NUMEROCHAMBRE = $i; // Numéro de chambre séquentiel
                $chambre->save();
            }
            
            DB::commit();
            return redirect()->route('parametres.batiments')->with('success', "Bâtiment ajouté avec succès avec $capacite chambres.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('parametres.batiments')->with('error', "Erreur lors de l'ajout du bâtiment : " . $e->getMessage());
        }
    }
    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'capacite' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $batiment = Batiment::findOrFail($id);
        // On ne modifie pas l'IDBATIMENT qui est la clé primaire
        $batiment->CAPACITE = $request->input('capacite');
        $batiment->save();
        
        return redirect()->route('parametres.batiments')->with('success', 'Bâtiment modifié avec succès.');
    }
    
    public function destroy($id)
    {
        try {
            // Démarrer une transaction pour garantir l'intégrité des données
            DB::beginTransaction();
            
            $batiment = Batiment::findOrFail($id);
            
            // Vérifier si le bâtiment a des chambres avec des résidents
            $chambresOccupees = Chambre::where('IDBATIMENT', $id)
                ->whereNotNull('IDRESIDENT')
                ->count();
            
            if ($chambresOccupees > 0) {
                return redirect()->route('parametres.batiments')
                    ->with('error', 'Impossible de supprimer ce bâtiment car il contient des chambres occupées.');
            }
            
            // Supprimer les chambres associées au bâtiment
            $chambres = Chambre::where('IDBATIMENT', $id)->get();
            $nombreChambres = $chambres->count();
            Chambre::where('IDBATIMENT', $id)->delete();
            
            // Supprimer le bâtiment
            $batiment->delete();
            
            DB::commit();
            
            $message = 'Bâtiment supprimé avec succès.';
            if ($nombreChambres > 0) {
                $message = "Bâtiment et {$nombreChambres} chambre(s) associée(s) supprimés avec succès.";
            }
            
            return redirect()->route('parametres.batiments')->with('success', $message);
        } catch (\Exception $e) {
            // En cas d'erreur, annuler toutes les modifications
            DB::rollBack();
            
            return redirect()->route('parametres.batiments')
                ->with('error', 'Erreur lors de la suppression du bâtiment : ' . $e->getMessage());
        }
    }
}
