<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evenement;
use Illuminate\Support\Facades\Log;
use App\Models\Adresse;
use App\Models\Batiment;
use App\Models\Chambre;
use App\Models\Dates;
use App\Models\Fichier;
use App\Models\MomentEvenement;
use App\Models\Occupation;
use App\Models\Parents;
use App\Models\Resident;
use App\Models\ResidentArchive;
use App\Models\Salle;

class EvenementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nomEvenement' => 'required|string|max:100',
            'mailGroupe' => 'required|email|max:255',
            'telGroupe' => [
                'required',
                'regex:/^(\+?\d{1,4}[\s.-]?)?(\(?\d{1,4}\)?[\s.-]?)?[\d\s.-]{6,14}$/'
            ],
            'referentGroupe' => 'required|string|max:255',
        ], [
            'telGroupe.regex' => 'Le numéro de téléphone est invalide. Exemple : +33 6 12 34 56 78 ou 06 12 34 56 78.',
        ]);
        
    
        $evenement = new Evenement();
        $evenement->NOMEVENEMENT = $request->input('nomEvenement');
        $evenement->MAILGROUPE = $request->input('mailGroupe');
        $evenement->TELGROUPE = $request->input('telGroupe');
        $evenement->REFERENTGROUPE = $request->input('referentGroupe');
        $evenement->COULEUR = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $evenement->save();
    
        return redirect()->back()->with('success', 'Événement créé avec succès.');
    }
}