<?php

namespace App\Http\Controllers;

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

class ResidentController extends Controller
{
    public function index($IdBatiment, $NumChambre)
    {
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
 
        return view('resident', ['chambre' => $chambre]);
    }
    public function getAllResident()
    {
        $residents = Resident::with('adresse')->get();
        return view('listeResidents', ['residents' => $residents]);
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $residents = Resident::where('NOMRESIDENT', 'like', '%' . $query . '%')
            ->orWhere('PRENOMRESIDENT', 'like', '%' . $query . '%')
            ->orWhere('MAILRESIDENT', 'like', '%' . $query . '%')
            ->orWhereHas('chambre', function ($q) use ($query) {
                $q->where('NUMEROCHAMBRE', 'like', '%' . $query . '%');
            })
            ->get();

        return view('listeResidents', ['residents' => $residents, 'query' => $query]);
    }
    public function getModif($idResident)
    {
        $resident = Resident::find($idResident);
        return view('modifierResident', ['resident' => $resident]);
    }    
    
    public function supprimerResident($idResident, $fromCommand = false)
    {
        $resident = Resident::find($idResident);
        
        if (!$resident) {
            if ($fromCommand) {
                return false;
            }
            return redirect()->back()->with('error', 'Résident non trouvé');
        }

        $chambre = Chambre::where('IDRESIDENT', $idResident)->first();
        if (!$chambre) {
            if ($fromCommand) {
                return false;
            }
            return redirect()->back()->with('error', 'Chambre non trouvée');
        }
        
        $this->archiverResident($idResident);

        $IdBatiment = $chambre->IDBATIMENT;
        $chambre->IDRESIDENT = null;
        $chambre->save();
    
        foreach ($resident->parents as $parent) {
            $parent->pivot->delete(); 
        }
        $resident->delete();

        if ($fromCommand) {
            return true;
        }
        
        return redirect()->route('chambre', ['IdBatiment' => $IdBatiment]);
    }
    public function nouveauResident($IdBatiment, $NumChambre)
    {
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
        
        $residentPartant = Resident::whereHas('chambre', function($query) use ($IdBatiment, $NumChambre) {
            $query->where('IDBATIMENT', $IdBatiment)
                ->where('NUMEROCHAMBRE', $NumChambre);
        })->whereNotNull('DATEDEPART')->first();
        
        if ($chambre->IDRESIDENT != null && (!$residentPartant || $residentPartant->DATEDEPART == null)) {
            return redirect()->route('resident', ['IdBatiment' => $IdBatiment, 'NumChambre' => $NumChambre]);
        }

        $dateEntreeSuggestion = $residentPartant ? 
            \Carbon\Carbon::parse($residentPartant->DATEDEPART)->addDay()->format('Y-m-d') : 
            now()->format('Y-m-d');
        
        return view('nouveauResident', [
            'chambre' => $chambre,
            'dateEntreeSuggestion' => $dateEntreeSuggestion,
            'residentPartant' => $residentPartant
        ]);
    }
    public function modifierResident(Request $request, $idResident)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'tel' => 'required|string|max:10',
            'adresse.code_postal' => 'required|string|max:10',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'tel.required' => 'Le numéro de téléphone est obligatoire.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'tel.max' => 'Le numéro de téléphone ne doit pas dépasser 10 caractères.',
            'adresse.code_postal.required' => 'Le code postal est obligatoire.',
            'adresse.code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'adresse.code_postal.max' => 'Le code postal ne doit pas dépasser 10 caractères.',
        ]);

        $resident = Resident::find($idResident);
        $resident->NOMRESIDENT = $request->input('nom');
        $resident->PRENOMRESIDENT = $request->input('prenom');
        $resident->MAILRESIDENT = $request->input('email');
        $resident->TELRESIDENT = ltrim($request->input('tel'));
        $resident->DATENAISSANCE = $request->input('anniversaire');
        $resident->NATIONALITE = $request->input('nationalite');
        $resident->ETABLISSEMENT = $request->input('etablissement');
        $resident->ANNEEETUDE = $request->input('annee_etude');
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {

            $photo = $request->file('photo');
            $photoPath = $photo->store('photos', 'public');
            $resident->PHOTO = $photoPath;
        }
        $resident->save();

        if ($request->has('parents')) {
            foreach ($request->input('parents') as $index => $parentData) {
                $parent = $resident->parents[$index] ?? new Parents();
                $parent->NOMPARENT = $parentData['nom'];
                $parent->TELPARENT = $parentData['tel'];
                $parent->PROFESSION = $parentData['profession'];
                $parent->save();
            }
        }

        if ($request->has('adresse')) {
            $adresse = Adresse::find((int) $request->input('adresse.idadresse'));
            $adresse->PAYS = $request->input('adresse.pays');
            $adresse->VILLE = $request->input('adresse.ville');
            $adresse->ADRESSE = $request->input('adresse.adresse');
            $adresse->CODEPOSTAL = $request->input('adresse.code_postal');
            $adresse->save();
        }

        $chambre = Chambre::where('IDRESIDENT',$idResident)->first();

        return redirect()->route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]);
    }
    
    public function store(Request $request, $IdBatiment, $NumChambre)
    {
        
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
        $request->validate([
            'email' => 'required|email|max:255',
            'tel' => 'required|string|max:10',
            'adresse.code_postal' => 'required|string|max:10',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'tel.required' => 'Le numéro de téléphone est obligatoire.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'tel.max' => 'Le numéro de téléphone ne doit pas dépasser 10 caractères.',
            'adresse.code_postal.required' => 'Le code postal est obligatoire.',
            'adresse.code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'adresse.code_postal.max' => 'Le code postal ne doit pas dépasser 10 caractères.',
        ]);
        
        
        $resident = new Resident();
        
        
        //-----------------------------------------
        //INSERT Adresse
        //-----------------------------------------

        if ($request->has('adresse')) {
            $adresse = new Adresse();
            $adresse->PAYS = $request->input('adresse.pays');
            $adresse->VILLE = $request->input('adresse.ville');
            $adresse->ADRESSE = $request->input('adresse.adresse');
            $adresse->CODEPOSTAL = $request->input('adresse.code_postal');
            $adresse->save();
            $resident->IDADRESSE = $adresse->IDADRESSE;
        }
        //-----------------------------------------
        //INSERT Resident
        //-----------------------------------------

        $resident->NOMRESIDENT = $request->input('nom');
        $resident->PRENOMRESIDENT = $request->input('prenom');
        $resident->MAILRESIDENT = $request->input('email');
        $resident->TELRESIDENT = ltrim($request->input('tel'));
        $resident->DATENAISSANCE = $request->input('anniversaire');
        $resident->NATIONALITE = $request->input('nationalite');
        $resident->ETABLISSEMENT = $request->input('etablissement');
        $resident->ANNEEETUDE = $request->input('annee_etude');
        $resident->DATEINSCRIPTION = $request->input('date_entree', now());
        $resident->CHAMBREASSIGNE = $chambre->IDCHAMBRE;

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoPath = $photo->store('photos', 'public');
            $resident->PHOTO = $photoPath;
        }

        $resident->save();

        if ($request->has('parents')) {
            foreach ($request->input('parents') as $parentData) {
                if (!empty($parentData['nom']) && !empty($parentData['tel']) && !empty($parentData['profession'])) {
                    
                    $parent = new Parents();
                    $parent->NOMPARENT = $parentData['nom'];
                    $parent->TELPARENT = $parentData['tel'];
                    $parent->PROFESSION = $parentData['profession'];
                    $parent->save();
                    $resident->parents()->attach($parent->IDPARENT);
                }
            }
        }

        $dateEntree = \Carbon\Carbon::parse($resident->DATEINSCRIPTION);
        $aujourdhui = now()->startOfDay();

        if ($dateEntree->lte($aujourdhui)) {
            
            if ($chambre) {
                $chambre->IDRESIDENT = $resident->IDRESIDENT;
                $chambre->save();
            }
        }

        return redirect()->route('resident', ['IdBatiment' => $IdBatiment, 'NumChambre' => $NumChambre]);
    }
    public function archiverResident($idResident)
    {
        $chambre = Chambre::where('IDRESIDENT',$idResident)->first();
        $resident = Resident::find($idResident);
        if ($resident) {
            $archivedResident = new ResidentArchive();
            $archivedResident->IDCHAMBRE = $chambre->IDCHAMBRE;
            $archivedResident->IDADRESSE = $resident->IDADRESSE;
            $archivedResident->NOMRESIDENTARCHIVE = $resident->NOMRESIDENT;
            $archivedResident->PRENOMRESIDENTARCHIVE = $resident->PRENOMRESIDENT;
            $archivedResident->TELRESIDENTARCHIVE = $resident->TELRESIDENT;
            $archivedResident->MAILRESIDENTARCHIVE = $resident->MAILRESIDENT;
            $archivedResident->DATENAISSANCEARCHIVE = $resident->DATENAISSANCE;
            $archivedResident->NATIONALITEARCHIVE = $resident->NATIONALITE;
            $archivedResident->ETABLISSEMENTARCHIVE = $resident->ETABLISSEMENT;
            $archivedResident->ANNEEETUDEARCHIVE = $resident->ANNEEETUDE;
            $archivedResident->PHOTOARCHIVE = $resident->PHOTO;
            $archivedResident->DATEINSCRIPTIONARCHIVE = $resident->DATEINSCRIPTION;
            $archivedResident->DATEARCHIVE = now();
            $archivedResident->save();

        }
        foreach ($resident->parents as $parentData) {
            
                $parent = Parents::find($parentData['IDPARENT']);
                $archivedResident->parents()->attach($parent->IDPARENT);
            
        }
        $fichiers = Fichier::where('IDRESIDENT', $idResident)->get();
        foreach ($fichiers as $fichier) {
            $fichier->IDRESIDENT = null;
            $fichier->IDRESIDENTARCHIVE = $archivedResident->IDRESIDENTARCHIVE;
            $fichier->save();
        }

    }
    public function planifierDepart(Request $request)
    {
        $request->validate([
            'idResident' => 'required|exists:RESIDENT,IDRESIDENT',
            'DATEDEPART' => 'required|date|after:today',
        ]);

        $resident = Resident::findOrFail($request->idResident);
        $resident->DATEDEPART = $request->DATEDEPART;
        $resident->save();

        return redirect()
            ->route('resident', ['IdBatiment' => $resident->chambre->IDBATIMENT, 'NumChambre' => $resident->chambre->NUMEROCHAMBRE])
            ->with('success', 'Le départ du résident a été planifié pour le ' . \Carbon\Carbon::parse($request->DATEEXPIRATION)->translatedFormat('d F Y'));
    }

    public function showDepartingResidents()
    {
        // Utilisez le mois et l'année actuels par défaut
        $month = now()->month;
        $year = now()->year;

        // Identifier les résidents qui vont partir ce mois-ci
        $departingResidents = Resident::whereMonth('DATEDEPART', $month)
            ->whereYear('DATEDEPART', $year)
            ->pluck('IDRESIDENT')
            ->toArray();

        // Récupérer uniquement les chambres des résidents qui partent
        $chambres = Chambre::whereIn('IDRESIDENT', $departingResidents)
            ->with('resident')
            ->get();
        
        // Récupérer également les chambres libres
        $chambresLibres = Chambre::whereNull('IDRESIDENT')->get();
        
        // Combiner les deux collections
        $allChambres = $chambres->merge($chambresLibres);
        
        // Trier par bâtiment puis par numéro de chambre
        $allChambres = $allChambres->sortBy([
            ['IDBATIMENT', 'asc'],
            ['NUMEROCHAMBRE', 'asc']
        ]);

        return view('chambresLibre', [
            'chambres' => $allChambres,
            'departingResidents' => $departingResidents,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function filterDepartingResidents(Request $request)
    {
        // Utiliser le mois et l'année actuels si non spécifiés
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $departingResidents = Resident::whereMonth('DATEDEPART', $month)
            ->whereYear('DATEDEPART', $year)
            ->pluck('IDRESIDENT')
            ->toArray();

        $chambres = Chambre::whereIn('IDRESIDENT', $departingResidents)
            ->with('resident')
            ->get();
        
        $chambresLibres = Chambre::whereNull('IDRESIDENT')->get();
        
        $allChambres = $chambres->merge($chambresLibres);
        
        $allChambres = $allChambres->sortBy([
            ['IDBATIMENT', 'asc'],
            ['NUMEROCHAMBRE', 'asc']
        ]);

        return view('chambresLibre', [
            'chambres' => $allChambres,
            'departingResidents' => $departingResidents,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }
}
