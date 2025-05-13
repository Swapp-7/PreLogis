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

class BatimentController extends Controller
{
    public function index()
    {
        $batiments = Batiment::all();
        return view('batiment', compact('batiments'));
    }
}
