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
use App\Exports\ResidentsExport;
use Maatwebsite\Excel\Facades\Excel;

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

        // Déterminer si le résident est l'occupant ACTUEL d'une chambre
        // ou s'il s'agit d'un futur résident
        $chambreOccupee = Chambre::where('IDRESIDENT', $idResident)->first();
        $chambreAssignee = Chambre::find($resident->CHAMBREASSIGNE);
        
        // Archiver le résident avant de le supprimer
        $this->archiverResident($idResident);

        // Ne vider la chambre QUE si le résident était l'occupant actuel
        if ($chambreOccupee) {
            $chambreOccupee->IDRESIDENT = null;
            $chambreOccupee->save();
        }
        
        // Gestion différente pour les groupes et les résidents individuels
        if ($resident->TYPE == 'group') {
            // Pour un groupe, on le retire juste de cette chambre sans le supprimer
            if ($chambreOccupee) {
                $chambreOccupee->IDRESIDENT = null;
                $chambreOccupee->save();
            }
            
            if ($fromCommand) {
                return true;
            }
            
            $successMessage = 'Groupe retiré de la chambre avec succès';
            
            if ($chambreOccupee) {
                return redirect()->route('chambre', ['IdBatiment' => $chambreOccupee->IDBATIMENT])
                    ->with('success', $successMessage);
            } elseif ($chambreAssignee) {
                return redirect()->route('chambre', ['IdBatiment' => $chambreAssignee->IDBATIMENT])
                    ->with('success', $successMessage);
            } else {
                return redirect()->route('allResident')
                    ->with('success', $successMessage);
            }
        } else {
            // Pour un résident individuel, on suit le processus standard
            // Supprimer les associations avec les parents
            if ($resident->parents) {
                foreach ($resident->parents as $parent) {
                    $parent->pivot->delete(); 
                }
            }
            
            // Supprimer le résident
            $resident->delete();
    
            if ($fromCommand) {
                return true;
            }
            
            // Rediriger vers la page appropriée selon le contexte
            if ($chambreOccupee) {
                // Si c'était un résident actuel, rediriger vers la page de la chambre
                return redirect()->route('chambre', ['IdBatiment' => $chambreOccupee->IDBATIMENT])
                    ->with('success', 'Résident supprimé avec succès');
            } elseif ($chambreAssignee) {
                // Si c'était un futur résident, rediriger vers la page de la chambre assignée
                return redirect()->route('chambre', ['IdBatiment' => $chambreAssignee->IDBATIMENT])
                    ->with('success', 'Futur résident supprimé avec succès');
            } else {
                // Si c'était un résident sans chambre assignée
                return redirect()->route('allResident')
                    ->with('success', 'Résident supprimé avec succès');
            }
        }
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
        $resident->MAILRESIDENT = $request->input('email');
        $resident->TELRESIDENT = ltrim($request->input('tel'));
        $resident->ETABLISSEMENT = $request->input('etablissement');
        $resident->ANNEEETUDE = $request->input('annee_etude');
        
        // Traitement différent selon le type de résident
        if ($resident->TYPE != 'group') {
            // Champs spécifiques aux résidents individuels
            $resident->PRENOMRESIDENT = $request->input('prenom');
            $resident->DATENAISSANCE = $request->input('anniversaire');
            $resident->NATIONALITE = $request->input('nationalite');
            
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('photos', 'public');
                $resident->PHOTO = $photoPath;
            }
        }
        
        $resident->save();

        // Gestion des parents uniquement pour les résidents individuels
        if ($resident->TYPE != 'group' && $request->has('parents')) {
            foreach ($request->input('parents') as $index => $parentData) {
                $parent = $resident->parents[$index] ?? new Parents();
                $parent->NOMPARENT = $parentData['nom'];
                $parent->TELPARENT = $parentData['tel'];
                $parent->PROFESSION = $parentData['profession'];
                $parent->save();
                
                // Si c'est un nouveau parent, l'attacher au résident
                if (!isset($resident->parents[$index])) {
                    $resident->parents()->attach($parent->IDPARENT);
                }
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

        // Après la modification, rediriger en fonction du type de résident
        if($resident->chambre){
            $chambre = Chambre::where('IDRESIDENT',$idResident)->first();
            // Si le résident a une chambre, rediriger vers la page de la chambre
            return redirect()->route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE])
                            ->with('success', 'Résident modifié avec succès');
        } else {
            // Si le résident n'a pas de chambre (futur résident), rediriger vers sa page individuelle
            return redirect()->route('allResident')
                            ->with('success', 'Résident modifié avec succès');
        }
    }
    
    public function store(Request $request, $IdBatiment, $NumChambre)
    {
        $chambre = Chambre::where('IDBATIMENT', $IdBatiment)->where('NUMEROCHAMBRE', $NumChambre)->first();
        $futureResident = Resident::where('CHAMBREASSIGNE', $chambre->IDCHAMBRE)
                             ->where('DATEINSCRIPTION', '>', now())
                             ->orderBy('DATEINSCRIPTION', 'asc')
                             ->first();
        
        $type = $request->input('type', 'individual');
        
        $rules = [
            'email' => 'required|email|max:255',
            'tel' => 'required|string|max:10',
            'adresse.code_postal' => 'required|string|max:10',
            'date_entree' => 'required|date',
        ];
        
        // Si c'est un membre de groupe existant, on ne valide que certains champs
        if ($type === 'group_member') {
            $rules = [
                'existing_group_id' => 'required|exists:RESIDENT,IDRESIDENT',
                'date_entree' => 'required|date'
            ];
        }
        
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
            'existing_group_id.required' => 'Veuillez sélectionner un groupe.',
            'existing_group_id.exists' => 'Le groupe sélectionné n\'existe pas.',
        ];
        
        $request->validate($rules, $messages);
        
        // Si on ajoute un membre à un groupe existant, pas besoin de créer un nouveau résident
        if ($type === 'group_member') {
            $group = Resident::findOrFail($request->input('existing_group_id'));
            
            // Vérifier que c'est bien un groupe
            if ($group->TYPE !== 'group') {
                return redirect()->back()->with('error', 'Le résident sélectionné n\'est pas un groupe.');
            }
            
            $dateEntree = \Carbon\Carbon::parse($request->input('date_entree'));
            $aujourdhui = now()->startOfDay();
            
            // Si la date d'entrée est aujourd'hui ou dans le passé, assigner directement le groupe à la chambre
            if ($dateEntree->lte($aujourdhui)) {
                $chambre->IDRESIDENT = $group->IDRESIDENT;
                $chambre->save();
            } else {
                // Futur occupant
                $group->CHAMBREASSIGNE = $chambre->IDCHAMBRE;
                $group->DATEINSCRIPTION = $request->input('date_entree');
                $group->DATEDEPART = $request->input('date_depart');
                $group->save();
            }
            
            return redirect()->route('resident', ['IdBatiment' => $IdBatiment, 'NumChambre' => $NumChambre])
                ->with('success', 'Groupe assigné à la chambre avec succès');
        }
        
        $resident = new Resident();
        $resident->TYPE = $type;
        
        
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
        $resident->MAILRESIDENT = $request->input('email');
        $resident->TELRESIDENT = ltrim($request->input('tel'));
        $resident->ETABLISSEMENT = $request->input('etablissement');
        $resident->ANNEEETUDE = $request->input('annee_etude');
        $resident->DATEINSCRIPTION = $request->input('date_entree', now());
        $resident->DATEDEPART = $request->input('date_depart'); 
        $resident->CHAMBREASSIGNE = $chambre->IDCHAMBRE;
        
        // Champs spécifiques au type individuel
        if ($type === 'individual') {
            $resident->PRENOMRESIDENT = $request->input('prenom');
            $resident->DATENAISSANCE = $request->input('anniversaire');
            $resident->NATIONALITE = $request->input('nationalite');
            
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('photos', 'public');
                $resident->PHOTO = $photoPath;
            }
        }

        $resident->save();

        // Ajouter les parents seulement pour les résidents individuels
        if ($type === 'individual' && $request->has('parents')) {
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

        $successMessage = $type === 'individual' ? 'Résident ajouté avec succès' : 'Groupe créé avec succès';
        return redirect()->route('resident', ['IdBatiment' => $IdBatiment, 'NumChambre' => $NumChambre])
                         ->with('success', $successMessage);
    }
    public function archiverResident($idResident)
    {
        $resident = Resident::find($idResident);
        
        if (!$resident) {
            return false;
        }
        
        // Trouver la chambre correcte, que ce soit l'occupant actuel ou un futur résident
        $chambreOccupee = Chambre::where('IDRESIDENT', $idResident)->first();
        $chambreAssignee = null; 
        
        if ($resident->CHAMBREASSIGNE) {
            $chambreAssignee = Chambre::find($resident->CHAMBREASSIGNE);
        }
        
        // Utiliser la chambre occupée en priorité, sinon la chambre assignée
        $chambre = $chambreOccupee ?? $chambreAssignee;
        
        $archivedResident = new ResidentArchive();
        
        // Vérifier que $chambre n'est pas null avant d'accéder à IDCHAMBRE
        $archivedResident->IDCHAMBRE = $chambre ? $chambre->IDCHAMBRE : null;
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

        // Vérifier si le résident a des parents avant de parcourir la collection
        if ($resident->parents && $resident->parents->count() > 0) {
            foreach ($resident->parents as $parentData) {
                $parent = Parents::find($parentData['IDPARENT']);
                if ($parent) {
                    $archivedResident->parents()->attach($parent->IDPARENT);
                }
            }
        }

        // Mettre à jour les fichiers
        $fichiers = Fichier::where('IDRESIDENT', $idResident)->get();
        foreach ($fichiers as $fichier) {
            $fichier->IDRESIDENT = null;
            $fichier->IDRESIDENTARCHIVE = $archivedResident->IDRESIDENTARCHIVE;
            $fichier->save();
        }
        
        return true;
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
        
        // Contraintes de date minimale : date d'aujourd'hui ou date de départ du résident actuel
        $minDate = now();
        if ($chambre->resident && $chambre->resident->DATEDEPART) {
            $minDate = Carbon::parse($chambre->resident->DATEDEPART)->addDay();
        }
        
        // Pour les chambres vides, vérifier s'il y a un futur résident AVANT celui-ci
        if (!$chambre->resident) {
            $previousFutureResident = Resident::where('CHAMBREASSIGNE', $chambre->IDCHAMBRE)
                ->where('IDRESIDENT', '!=', $resident->IDRESIDENT)
                ->where('DATEINSCRIPTION', '<', $resident->DATEINSCRIPTION)
                ->orderBy('DATEINSCRIPTION', 'desc')
                ->first();
            
            if ($previousFutureResident && $previousFutureResident->DATEDEPART) {
                $previousDepartDate = Carbon::parse($previousFutureResident->DATEDEPART)->addDay();
                if ($previousDepartDate->gt($minDate)) {
                    $minDate = $previousDepartDate;
                }
            }
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
            return redirect()->back()->with('error', 'La date d\'arrivée doit être après ' . $minDate->format('d/m/Y'));
        }
        
        if ($maxDate && $dateArrivee->gt($maxDate)) {
            return redirect()->back()->with('error', 'La date d\'arrivée doit être avant l\'arrivée du prochain futur résident (' . $maxDate->format('d/m/Y') . ')');
        }
        
        // Vérifier la date de départ si fournie
        if ($request->date_depart) {
            $dateDepart = Carbon::parse($request->date_depart);
            
            if ($dateDepart->lte($dateArrivee)) {
                return redirect()->back()->with('error', 'La date de départ doit être après la date d\'arrivée');
            }
            
            if ($maxDate && $dateDepart->gt($maxDate)) {
                return redirect()->back()->with('error', 'La date de départ doit être avant l\'arrivée du prochain futur résident (' . $maxDate->format('d/m/Y') . ')');
            }
            
            // Mettre à jour la date de départ
            $resident->DATEDEPART = $request->date_depart;
        }
        
        // Mettre à jour la date d'arrivée
        $resident->DATEINSCRIPTION = $request->date_arrivee;
        $resident->save();
        
        return redirect()->back()->with('success', 'Dates mises à jour avec succès');
    }
    
    public function exportExcel(Request $request)
    {
        $query = null;
        
        // Si une recherche est en cours, exporter uniquement les résultats de la recherche
        if ($request->has('query')) {
            $searchQuery = $request->input('query');
            $query = Resident::with(['chambre', 'parents','adresse'])
                ->where('NOMRESIDENT', 'like', "%{$searchQuery}%")
                ->orWhere('PRENOMRESIDENT', 'like', "%{$searchQuery}%")
                ->orWhere('MAILRESIDENT', 'like', "%{$searchQuery}%")
                ->get();
        }
        
        return Excel::download(new ResidentsExport($query), 'residents.xlsx');
    }
    
}
