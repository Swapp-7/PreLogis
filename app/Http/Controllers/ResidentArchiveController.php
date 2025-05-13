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
                ->orWhere('MAILRESIDENTARCHIVE', 'like', "%$query%");
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
   
  
}
