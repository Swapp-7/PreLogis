<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function deleteResidentArchive($idResidentA)
    {
        $residentA = ResidentArchive::find($idResidentA);
            $residentA->delete();
        foreach ($residentA->parents as $parent) {
            $parent->pivot->delete();
            $parent->delete(); 
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
