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
use Carbon\Carbon;

class ResidentController extends Controller
{
    public function index($IdBatiment, $NumChambre)
    {
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
 
        return view('resident-chambre', ['chambre' => $chambre]);
    }
    public function getResident($IdResident)
    {
        $resident = Resident::with(['adresse', 'chambre', 'parents'])->find($IdResident);
        if (!$resident) {
            return redirect()->back()->with('error', 'Résident non trouvé');
        }
        return view('resident', ['resident' => $resident]);
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
                $q->where('NUMEROCHAMBRE', 'like', '%' . $query . '%')
                ->orWhere('IDBATIMENT', 'like', '%' . $query . '%')
                ->orWhereRaw("CONCAT(IDBATIMENT, NUMEROCHAMBRE) LIKE ?", ['%' . $query . '%']);
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

        $lastResident = $chambre->futureResidents->last();
        
        
        
        if ($chambre->IDRESIDENT != null && (!$residentPartant || $residentPartant->DATEDEPART == null)) {
            return redirect()->route('resident', ['IdBatiment' => $IdBatiment, 'NumChambre' => $NumChambre]);
        }
        if ($lastResident==null) {
            $lastResident = $residentPartant;
        }
        $dateEntreeSuggestion = $lastResident->DATEDEPART ?? null ;
        return view('nouveauResident', [
            'chambre' => $chambre,
            'dateEntreeSuggestion' => $dateEntreeSuggestion,
            'residentPartant' => $residentPartant,
            'futureResident' => $lastResident
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
        if($resident->chambre){
            return redirect()->route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]);

        }else{
            return redirect()->route('getResident', ['IdResident' => $resident->IDRESIDENT]);
        }
    }
    
    public function store(Request $request, $IdBatiment, $NumChambre)
    {
        
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
        $futureResident = Resident::where('CHAMBREASSIGNE', $chambre->IDCHAMBRE)
                             ->where('DATEINSCRIPTION', '>', now())
                             ->orderBy('DATEINSCRIPTION', 'asc')
                             ->first();
    
        $rules = [
            'email' => 'required|email|max:255',
            'tel' => 'required|string|max:10',
            'adresse.code_postal' => 'required|string|max:10',
            'date_entree' => 'required|date',
        ];
        
        $messages = [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'tel.required' => 'Le numéro de téléphone est obligatoire.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'tel.max' => 'Le numéro de téléphone ne doit pas dépasser 10 caractères.',
            'adresse.code_postal.required' => 'Le code postal est obligatoire.',
            'adresse.code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'adresse.code_postal.max' => 'Le code postal ne doit pas dépasser 10 caractères.',
            'date_entree.required' => 'La date d\'entrée est obligatoire.',
            'date_entree.date' => 'La date d\'entrée n\'est pas valide.',
        ];
        
        // Ajouter la règle de validation pour la date de départ s'il y a un futur résident
       
        
        $request->validate($rules, $messages);
        
        
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
        $resident->DATEDEPART = $request->input('date_depart'); 

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

        // Mise à jour de la redirection pour utiliser getResident si disponible
        if ($resident->chambre) {
            return redirect()
                ->route('resident', ['IdBatiment' => $resident->chambre->IDBATIMENT, 'NumChambre' => $resident->chambre->NUMEROCHAMBRE])
                ->with('success', 'Le départ du résident a été planifié pour le ' . \Carbon\Carbon::parse($request->DATEDEPART)->translatedFormat('d F Y'));
        } else {
            return redirect()
                ->route('getResident', ['IdResident' => $resident->IDRESIDENT])
                ->with('success', 'Le départ du résident a été planifié pour le ' . \Carbon\Carbon::parse($request->DATEDEPART)->translatedFormat('d F Y'));
        }
    }
    public function updateFutureResidentDates(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:RESIDENT,IDRESIDENT',
            'date_arrivee' => 'required|date',
            'date_depart' => 'nullable|date|after:date_arrivee'
        ]);
        
        $resident = Resident::find($request->resident_id);
        
        if (!$resident) {
            return redirect()->back()->with('error', 'Résident non trouvé');
        }
        
        // Récupérer la chambre assignée pour vérifier les contraintes
        $chambre = Chambre::find($resident->CHAMBREASSIGNE);
        
        if (!$chambre) {
            return redirect()->back()->with('error', 'Chambre non trouvée');
        }
        
        // Contraintes de date minimale : date de départ du résident actuel
        $minDate = now();
        if ($chambre->resident && $chambre->resident->DATEDEPART) {
            $minDate = Carbon::parse($chambre->resident->DATEDEPART)->addDay();
        }
        
        // Contraintes de date maximale : date d'arrivée du prochain futur résident
        $nextFutureResident = Resident::where('CHAMBREASSIGNE', $chambre->IDCHAMBRE)
            ->where('IDRESIDENT', '!=', $resident->IDRESIDENT)
            ->where('DATEINSCRIPTION', '>', $resident->DATEINSCRIPTION) // Find resident AFTER this one
            ->orderBy('DATEINSCRIPTION', 'asc')
            ->first();
        
        $maxDate = null;
        if ($nextFutureResident) {
            $maxDate = Carbon::parse($nextFutureResident->DATEINSCRIPTION)->subDay();
        }
        
        // Valider les contraintes de date
        $dateArrivee = Carbon::parse($request->date_arrivee);
        
        if ($dateArrivee->lt($minDate)) {
            return redirect()->back()->with('error', 'La date d\'arrivée doit être après la date de départ du résident actuel');
        }
        
        if ($maxDate && $dateArrivee->gt($maxDate)) {
            return redirect()->back()->with('error', 'La date d\'arrivée doit être avant l\'arrivée du prochain futur résident');
        }
        
        // Vérifier la date de départ si fournie
        if ($request->date_depart) {
            $dateDepart = Carbon::parse($request->date_depart);
            
            if ($dateDepart->lte($dateArrivee)) {
                return redirect()->back()->with('error', 'La date de départ doit être après la date d\'arrivée');
            }
            
            if ($maxDate && $dateDepart->gt($maxDate)) {
                return redirect()->back()->with('error', 'La date de départ doit être avant l\'arrivée du prochain futur résident');
            }
            
            // Mettre à jour la date de départ
            $resident->DATEDEPART = $request->date_depart;
        }
        
        // Mettre à jour la date d'arrivée
        $resident->DATEINSCRIPTION = $request->date_arrivee;
        $resident->save();
        
        return redirect()->back()->with('success', 'Dates mises à jour avec succès');
    }
    

    
}
