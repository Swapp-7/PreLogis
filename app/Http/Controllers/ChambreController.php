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

class ChambreController extends Controller
{
    public function index($IdBatiment)
    {
        $chambres = Chambre::where('IDBATIMENT', $IdBatiment)->with('resident')->get();

        return view('chambre', ['chambres' => $chambres]);
    }
    public function chambreVide()
    {
        $chambres = Chambre::where('IDRESIDENT', null)->get();
        return view('chambresLibre', ['chambres' => $chambres]);

    }
  
}
