<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResidentArchiveExport;

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

class ResidentArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $residentA = ResidentArchive::query();

        if ($query) {
            $residentA->where(function ($q) use ($query) {
                $q->where('NOMRESIDENTARCHIVE', 'like', "%$query%")
                ->orWhere('PRENOMRESIDENTARCHIVE', 'like', "%$query%")
                ->orWhere('MAILRESIDENTARCHIVE', 'like', "%$query%")
                ->orWhereHas('chambre', function ($chambreQuery) use ($query) {
                    $chambreQuery->where('NUMEROCHAMBRE', 'like', "%$query%")
                    ->orWhereRaw("CONCAT(IDBATIMENT, NUMEROCHAMBRE) LIKE ?", ['%' . $query . '%']);
                });
            });
        }

        $residentA = $residentA->get();

        return view('archive', ['residentA' => $residentA, 'query' => $query]);
    }
    public function getResidentArchive($idResidentA)
    {
        $residentA = ResidentArchive::find($idResidentA);
        return view('resident-archive', ['residentA' => $residentA]);
    }
    public function deleteResidentArchive($idResidentArchive)
    {
        $residentArchive = ResidentArchive::find($idResidentArchive);
        
        if (!$residentArchive) {
            return redirect()->route('archive')->with('error', 'Résident archivé non trouvé');
        }

        try {
            // Supprimer tous les fichiers associés
            if ($residentArchive->fichiers) {
                foreach ($residentArchive->fichiers as $fichier) {
                    // Supprimer le fichier physique du stockage
                    if ($fichier->CHEMINFICHIER && Storage::exists($fichier->CHEMINFICHIER)) {
                        Storage::delete($fichier->CHEMINFICHIER);
                    }
                    // Supprimer l'enregistrement du fichier
                    $fichier->delete();
                }
            }

            // Supprimer la photo de profil si elle existe
            if ($residentArchive->PHOTOARCHIVE && $residentArchive->PHOTOARCHIVE !== 'photo') {
                $photoPath = 'public/' . $residentArchive->PHOTOARCHIVE;
                if (Storage::exists($photoPath)) {
                    Storage::delete($photoPath);
                }
            }

            // Supprimer les relations avec les parents
            if ($residentArchive->parents) {
                foreach ($residentArchive->parents as $parent) {
                    // Supprimer la relation pivot
                    $residentArchive->parents()->detach($parent->IDPARENT);
                    
                    // Vérifier si ce parent n'est utilisé par aucun autre résident (actuel ou archivé)
                    $parentUsageCount = DB::table('AVAITPOURPARENT')
                        ->where('IDPARENT', $parent->IDPARENT)
                        ->count();
                    
                    $parentUsageArchiveCount = DB::table('AVAITPOURPARENT')
                        ->where('IDPARENT', $parent->IDPARENT)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                  ->from('RESIDENTARCHIVE')
                                  ->whereColumn('RESIDENTARCHIVE.IDRESIDENTARCHIVE', 'AVAITPOURPARENT.IDRESIDENTARCHIVE');
                        })
                        ->count();
                    
                    // Si le parent n'est utilisé nulle part ailleurs, on peut le supprimer
                    if ($parentUsageCount === 0 && $parentUsageArchiveCount === 0) {
                        $parent->delete();
                    }
                }
            }

            // Supprimer l'adresse si elle n'est utilisée par personne d'autre
            if ($residentArchive->adresse) {
                $adresse = $residentArchive->adresse;
                
                // Vérifier si cette adresse est utilisée par d'autres résidents (actuels ou archivés)
                $adresseUsageCount = Resident::where('IDADRESSE', $adresse->IDADRESSE)->count();
                $adresseUsageArchiveCount = ResidentArchive::where('IDADRESSE', $adresse->IDADRESSE)
                    ->where('IDRESIDENTARCHIVE', '!=', $idResidentArchive)
                    ->count();
                
                if ($adresseUsageCount === 0 && $adresseUsageArchiveCount === 0) {
                    $adresse->delete();
                }
            }

            // Finalement, supprimer le résident archivé
            $residentArchive->delete();

            return redirect()->route('archive')->with('success', 'Résident archivé supprimé définitivement avec succès');
            
        } catch (\Exception $e) {
            return redirect()->route('archive')->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $query = null;
        
        // Si une recherche est en cours, exporter uniquement les résultats de la recherche
        if ($request->has('query')) {
            $searchQuery = $request->input('query');
            $query = ResidentArchive::with(['chambre', 'parents', 'adresse'])
                ->where(function ($q) use ($searchQuery) {
                    $q->where('NOMRESIDENTARCHIVE', 'like', "%{$searchQuery}%")
                    ->orWhere('PRENOMRESIDENTARCHIVE', 'like', "%{$searchQuery}%")
                    ->orWhere('MAILRESIDENTARCHIVE', 'like', "%{$searchQuery}%")
                    ->orWhereHas('chambre', function ($chambreQuery) use ($searchQuery) {
                        $chambreQuery->where('NUMEROCHAMBRE', 'like', "%{$searchQuery}%")
                        ->orWhereRaw("CONCAT(IDBATIMENT, NUMEROCHAMBRE) LIKE ?", ['%' . $searchQuery . '%']);
                    });
                })
                ->get();
        } else {
            // Si pas de recherche, récupérer tous les résidents archivés avec leurs relations
            $query = ResidentArchive::with(['chambre', 'parents', 'adresse'])->get();
        }
        
        return Excel::download(new ResidentArchiveExport($query), 'residents_archives.xlsx');
    }
}
