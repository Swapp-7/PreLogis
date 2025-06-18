<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Adresse;
use App\Models\Batiment;
use App\Models\Chambre;
use App\Models\Dates;
use Illuminate\Support\Facades\DB;
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
use Barryvdh\DomPDF\Facade\Pdf;

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
        $resident = Resident::find($idResident);
        
        if (!$resident) {
            return redirect()->back()->with('error', 'Résident non trouvé.');
        }
        
        $rules = [
            'nom' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'email' => 'required|email|max:255',
            'tel' => 'required|string',
            'adresse.adresse' => 'required|string|min:5|max:255',
            'adresse.code_postal' => 'required|string|regex:/^[0-9]{5}$/',
            'adresse.ville' => 'required|string|min:2|max:70|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'adresse.pays' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        
        // Validations spécifiques pour les résidents individuels
        if ($resident->TYPE !== 'group') {
            $rules = array_merge($rules, [
                'prenom' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'anniversaire' => 'required|date|before:today',
                'nationalite' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'parents.*.nom' => 'nullable|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'parents.*.tel' => 'nullable|string',
                'parents.*.profession' => 'nullable|string|min:2|max:100|regex:/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/',
            ]);
        }
        
        $messages = [
            // Messages pour les champs communs
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères.',
            'nom.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 50 caractères.',
            'prenom.regex' => 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            
            'tel.required' => 'Le numéro de téléphone est obligatoire.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            
            'anniversaire.required' => 'La date de naissance est obligatoire.',
            'anniversaire.date' => 'La date de naissance n\'est pas valide.',
            'anniversaire.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            
            'nationalite.required' => 'La nationalité est obligatoire.',
            'nationalite.string' => 'La nationalité doit être une chaîne de caractères.',
            'nationalite.min' => 'La nationalité doit contenir au moins 2 caractères.',
            'nationalite.max' => 'La nationalité ne doit pas dépasser 50 caractères.',
            'nationalite.regex' => 'La nationalité ne peut contenir que des lettres, espaces, tirets et apostrophes.',
                        
            // Messages pour l'adresse
            'adresse.adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.adresse.min' => 'L\'adresse doit contenir au moins 5 caractères.',
            'adresse.adresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            
            'adresse.code_postal.required' => 'Le code postal est obligatoire.',
            'adresse.code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'adresse.code_postal.regex' => 'Le code postal doit contenir exactement 5 chiffres.',
            
            'adresse.ville.required' => 'La ville est obligatoire.',
            'adresse.ville.string' => 'La ville doit être une chaîne de caractères.',
            'adresse.ville.min' => 'La ville doit contenir au moins 2 caractères.',
            'adresse.ville.max' => 'La ville ne doit pas dépasser 50 caractères.',
            'adresse.ville.regex' => 'La ville ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'adresse.pays.required' => 'Le pays est obligatoire.',
            'adresse.pays.string' => 'Le pays doit être une chaîne de caractères.',
            'adresse.pays.min' => 'Le pays doit contenir au moins 2 caractères.',
            'adresse.pays.max' => 'Le pays ne doit pas dépasser 50 caractères.',
            'adresse.pays.regex' => 'Le pays ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            // Messages pour les parents
            'parents.*.nom.string' => 'Le nom du parent doit être une chaîne de caractères.',
            'parents.*.nom.min' => 'Le nom du parent doit contenir au moins 2 caractères.',
            'parents.*.nom.max' => 'Le nom du parent ne doit pas dépasser 50 caractères.',
            'parents.*.nom.regex' => 'Le nom du parent ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'parents.*.tel.string' => 'Le téléphone du parent doit être une chaîne de caractères.',
            
            'parents.*.profession.string' => 'La profession du parent doit être une chaîne de caractères.',
            'parents.*.profession.min' => 'La profession du parent doit contenir au moins 2 caractères.',
            'parents.*.profession.max' => 'La profession du parent ne doit pas dépasser 100 caractères.',
            'parents.*.profession.regex' => 'La profession du parent contient des caractères non autorisés.',
            
            // Messages pour la photo
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format: jpeg, png, jpg ou gif.',
            'photo.max' => 'L\'image ne doit pas dépasser 2MB.',
        ];
        
        $request->validate($rules, $messages);
        
        // Validations personnalisées supplémentaires pour les parents
        if ($resident->TYPE !== 'group' && $request->has('parents')) {
            $parentErrors = $this->validateParentsData($request->input('parents'));
            
            // Si des erreurs de cohérence sont trouvées, les retourner
            if (!empty($parentErrors)) {
                return redirect()->back()
                    ->withErrors($parentErrors)
                    ->withInput();
            }
        }
        
        // Validation personnalisée du téléphone principal
        if (!$this->isValidInternationalPhone($request->input('tel'))) {
            return redirect()->back()
                ->withErrors(['tel' => 'Le format du téléphone principal n\'est pas valide.'])
                ->withInput();
        }

        $resident = Resident::find($idResident);
        $resident->NOMRESIDENT = $request->input('nom');
        $resident->MAILRESIDENT = $request->input('email');
        $resident->TELRESIDENT = $this->cleanPhoneNumber($request->input('tel'));
        
        // Traitement différent selon le type de résident
        if ($resident->TYPE != 'group') {
            $resident->ETABLISSEMENT = $request->input('etablissement');
            $resident->ANNEEETUDE = $request->input('annee_etude');
            // Champs spécifiques aux résidents individuels
            $resident->PRENOMRESIDENT = $request->input('prenom');
            $resident->DATENAISSANCE = $request->input('anniversaire');
            $resident->NATIONALITE = $request->input('nationalite');
        }
        
        // Traitement de la photo pour tous les types de résidents
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoPath = $photo->store('photos', 'public');
            $resident->PHOTO = $photoPath;
        }
        
        $resident->save();

        // Gestion des parents uniquement pour les résidents individuels
        if ($resident->TYPE != 'group' && $request->has('parents')) {
            foreach ($request->input('parents') as $index => $parentData) {
                $parent = $resident->parents[$index] ?? new Parents();
                $parent->NOMPARENT = $parentData['nom'];
                $parent->TELPARENT = $this->cleanPhoneNumber($parentData['tel']);
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
            'nom' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'email' => 'required|email|max:255',
            'tel' => 'required|string',
            'date_entree' => 'required|date|after_or_equal:today',
            'date_depart' => 'nullable|date|after:date_entree',
            'adresse.adresse' => 'required|string|min:5|max:255',
            'adresse.code_postal' => 'required|string|regex:/^[0-9]{5}$/',
            'adresse.ville' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'adresse.pays' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        
        // Validations spécifiques selon le type
        if ($type === 'individual') {
            $rules = array_merge($rules, [
                'prenom' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'anniversaire' => 'required|date|before:today',
                'nationalite' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'parents.*.nom' => 'nullable|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'parents.*.tel' => 'nullable|string',
                'parents.*.profession' => 'nullable|string|min:2|max:100|regex:/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/',
            ]);
        }
        
        // Si c'est un membre de groupe existant, on ne valide que certains champs
        if ($type === 'group_member') {
            $rules = [
                'existing_group_id' => 'required|exists:RESIDENT,IDRESIDENT',
                'date_entree' => 'required|date|after_or_equal:today',
                'date_depart' => 'nullable|date|after:date_entree',
            ];
        }
        
        $messages = [
            // Messages pour les champs communs
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères.',
            'nom.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 50 caractères.',
            'prenom.regex' => 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            
            'tel.required' => 'Le numéro de téléphone est obligatoire.',
            'tel.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            
            'etablissement.required' => 'L\'établissement est obligatoire.',
            'etablissement.string' => 'L\'établissement doit être une chaîne de caractères.',
            'etablissement.min' => 'L\'établissement doit contenir au moins 2 caractères.',
            'etablissement.max' => 'L\'établissement ne doit pas dépasser 100 caractères.',
            'etablissement.regex' => 'L\'établissement contient des caractères non autorisés.',
            
            'annee_etude.required' => 'L\'année d\'étude est obligatoire.',
            'annee_etude.in' => 'L\'année d\'étude doit être l\'une des valeurs suivantes: 1re, 2e, 3e, 4e, 5e.',
            
            // Messages pour l'adresse
            'adresse.adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.adresse.min' => 'L\'adresse doit contenir au moins 5 caractères.',
            'adresse.adresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            
            'adresse.code_postal.required' => 'Le code postal est obligatoire.',
            'adresse.code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'adresse.code_postal.regex' => 'Le code postal doit contenir exactement 5 chiffres.',
            
            'adresse.ville.required' => 'La ville est obligatoire.',
            'adresse.ville.string' => 'La ville doit être une chaîne de caractères.',
            'adresse.ville.min' => 'La ville doit contenir au moins 2 caractères.',
            'adresse.ville.max' => 'La ville ne doit pas dépasser 50 caractères.',
            'adresse.ville.regex' => 'La ville ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'adresse.pays.required' => 'Le pays est obligatoire.',
            'adresse.pays.string' => 'Le pays doit être une chaîne de caractères.',
            'adresse.pays.min' => 'Le pays doit contenir au moins 2 caractères.',
            'adresse.pays.max' => 'Le pays ne doit pas dépasser 50 caractères.',
            'adresse.pays.regex' => 'Le pays ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            // Messages pour les dates
            'date_entree.required' => 'La date d\'entrée est obligatoire.',
            'date_entree.date' => 'La date d\'entrée n\'est pas valide.',
            'date_entree.after_or_equal' => 'La date d\'entrée ne peut pas être antérieure à aujourd\'hui.',
            
            'date_depart.date' => 'La date de départ n\'est pas valide.',
            'date_depart.after' => 'La date de départ doit être postérieure à la date d\'entrée.',
            
            // Messages pour les parents
            'parents.*.nom.string' => 'Le nom du parent doit être une chaîne de caractères.',
            'parents.*.nom.min' => 'Le nom du parent doit contenir au moins 2 caractères.',
            'parents.*.nom.max' => 'Le nom du parent ne doit pas dépasser 50 caractères.',
            'parents.*.nom.regex' => 'Le nom du parent ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            
            'parents.*.tel.string' => 'Le téléphone du parent doit être une chaîne de caractères.',
            
            'parents.*.profession.string' => 'La profession du parent doit être une chaîne de caractères.',
            'parents.*.profession.min' => 'La profession du parent doit contenir au moins 2 caractères.',
            'parents.*.profession.max' => 'La profession du parent ne doit pas dépasser 100 caractères.',
            'parents.*.profession.regex' => 'La profession du parent contient des caractères non autorisés.',
            
            // Messages pour la photo
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format: jpeg, png, jpg ou gif.',
            'photo.max' => 'L\'image ne doit pas dépasser 2MB.',
            
            // Messages pour groupe existant
            'existing_group_id.required' => 'Veuillez sélectionner un groupe.',
            'existing_group_id.exists' => 'Le groupe sélectionné n\'existe pas.',
        ];
        
        $request->validate($rules, $messages);
        
        // Validations personnalisées supplémentaires
        if ($type === 'individual' && $request->has('parents')) {
            $parentErrors = $this->validateParentsData($request->input('parents'));
            
            // Si des erreurs de cohérence sont trouvées, les retourner
            if (!empty($parentErrors)) {
                return redirect()->back()
                    ->withErrors($parentErrors)
                    ->withInput();
            }
        }
        
        // Validation personnalisée du téléphone principal (seulement pour individual et group)
        if ($type !== 'group_member' && !$this->isValidInternationalPhone($request->input('tel'))) {
            return redirect()->back()
                ->withErrors(['tel' => 'Le format du téléphone principal n\'est pas valide.'])
                ->withInput();
        }
        
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
        $resident->TELRESIDENT = $this->cleanPhoneNumber($request->input('tel'));
        $resident->DATEINSCRIPTION = $request->input('date_entree', now());
        $resident->DATEDEPART = $request->input('date_depart'); 
        $resident->CHAMBREASSIGNE = $chambre->IDCHAMBRE;
        
        // Champs spécifiques au type
        if ($type === 'individual') {
            $resident->ETABLISSEMENT = $request->input('etablissement');
            $resident->ANNEEETUDE = $request->input('annee_etude');
            $resident->PRENOMRESIDENT = $request->input('prenom');
            $resident->DATENAISSANCE = $request->input('anniversaire');
            $resident->NATIONALITE = $request->input('nationalite');
        } else if ($type === 'group') {
            $resident->ETABLISSEMENT = '';
            $resident->ANNEEETUDE = '';
        }
        
        // Traitement de la photo pour tous les types de résidents
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoPath = $photo->store('photos', 'public');
            $resident->PHOTO = $photoPath;
        }

        $resident->save();

        // Ajouter les parents seulement pour les résidents individuels
        if ($type === 'individual' && $request->has('parents')) {
            foreach ($request->input('parents') as $parentData) {
                if (!empty($parentData['nom']) && !empty($parentData['tel'])) {
                    $parent = new Parents();
                    $parent->NOMPARENT = $parentData['nom'];
                    $parent->TELPARENT = $this->cleanPhoneNumber($parentData['tel']);
                    $parent->PROFESSION = $parentData['profession'] ?? '';
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
        
        // Si c'est un groupe, ne pas l'archiver
        if ($resident->TYPE == 'group') {
            return true;
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
    
    public function archiverGroupe($idGroupe)
    {
        $groupe = Resident::with(['chambres', 'adresse'])->find($idGroupe);
        
        if (!$groupe || $groupe->TYPE !== 'group') {
            return false;
        }
        
        // Créer l'archive du groupe
        $archivedGroupe = new ResidentArchive();
        $archivedGroupe->IDCHAMBRE = null; // Les groupes peuvent avoir plusieurs chambres
        $archivedGroupe->IDADRESSE = $groupe->IDADRESSE;
        $archivedGroupe->NOMRESIDENTARCHIVE = $groupe->NOMRESIDENT;
        $archivedGroupe->PRENOMRESIDENTARCHIVE = null; // Les groupes n'ont pas de prénom
        $archivedGroupe->TELRESIDENTARCHIVE = $groupe->TELRESIDENT;
        $archivedGroupe->MAILRESIDENTARCHIVE = $groupe->MAILRESIDENT;
        $archivedGroupe->DATENAISSANCEARCHIVE = null; // Les groupes n'ont pas de date de naissance
        $archivedGroupe->NATIONALITEARCHIVE = null; // Les groupes n'ont pas de nationalité
        $archivedGroupe->ETABLISSEMENTARCHIVE = $groupe->ETABLISSEMENT;
        $archivedGroupe->ANNEEETUDEARCHIVE = $groupe->ANNEEETUDE;
        $archivedGroupe->PHOTOARCHIVE = $groupe->PHOTO;
        $archivedGroupe->DATEINSCRIPTIONARCHIVE = $groupe->DATEINSCRIPTION;
        $archivedGroupe->DATEARCHIVE = now();
        $archivedGroupe->TYPEARCHIVE = 'group'; // Nouveau champ pour identifier les groupes
        
        // Créer une liste des chambres occupées pour le nouveau champ texte
        $chambresOccupees = [];
        foreach ($groupe->chambres as $chambre) {
            $chambresOccupees[] = "{$chambre->IDBATIMENT} {$chambre->NUMEROCHAMBRE} ";
        }
        $archivedGroupe->CHAMBREOCCUPEESARCHIVE = implode(' | ', $chambresOccupees);
        
        $archivedGroupe->save();
        
        // Mettre à jour les fichiers pour pointer vers l'archive
        DB::table('FICHIER')
            ->where('IDRESIDENT', $idGroupe)
            ->update([
                'IDRESIDENT' => null,
                'IDRESIDENTARCHIVE' => $archivedGroupe->IDRESIDENTARCHIVE
            ]);
        
        return true;
    }
    
    /**
     * Nettoie et formate un numéro de téléphone
     * 
     * @param string $phone
     * @return string
     */
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone)) {
            return '';
        }
        
        // Supprimer tous les espaces, points, tirets, parenthèses
        $cleaned = preg_replace('/[\s\.\-\(\)]/', '', $phone);
        
        return $cleaned;
    }
    
    /**
     * Valide un numéro de téléphone français
     * 
     * @param string $phone
     * @return bool
     */
    private function isValidFrenchPhone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        
        $cleaned = $this->cleanPhoneNumber($phone);
        
        // Format français : +33 ou 0
        return preg_match('/^(?:\+33[1-9][0-9]{8}|0[1-9][0-9]{8})$/', $cleaned) === 1;
    }

    /**
     * Valide un numéro de téléphone international
     * 
     * @param string $phone
     * @return bool
     */
    private function isValidInternationalPhone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        
        $cleaned = $this->cleanPhoneNumber($phone);
        
        // Support international étendu avec indicatifs pays courants
        $patterns = [
            // France : +33 ou 0 (fixes: 01-05, 08-09; mobiles: 06-07)
            '/^(?:\+33[1-9][0-9]{8}|0[1-9][0-9]{8})$/',
            // USA/Canada : +1
            '/^\+1[2-9][0-9]{9}$/',
            // Royaume-Uni : +44
            '/^\+44[1-9][0-9]{8,9}$/',
            // Allemagne : +49
            '/^\+49[1-9][0-9]{8,11}$/',
            // Italie : +39
            '/^\+39[0-9]{8,11}$/',
            // Espagne : +34
            '/^\+34[6-9][0-9]{8}$/',
            // Portugal : +351
            '/^\+351[2-9][0-9]{8}$/',
            // Belgique : +32
            '/^\+32[2-9][0-9]{7,8}$/',
            // Suisse : +41
            '/^\+41[2-9][0-9]{8}$/',
            // Maroc : +212
            '/^\+212[5-7][0-9]{8}$/',
            // Algérie : +213
            '/^\+213[5-7][0-9]{8}$/',
            // Tunisie : +216
            '/^\+216[2-9][0-9]{7}$/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleaned) === 1) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Valide les données des parents et retourne les erreurs
     * 
     * @param array $parents
     * @return array
     */
    private function validateParentsData($parents)
    {
        $errors = [];
        
        foreach ($parents as $index => $parentData) {
            $parentNumber = $index + 1;
            
            // Si un nom est renseigné, vérifier que les autres champs importants le sont aussi
            if (!empty($parentData['nom'])) {
                if (empty($parentData['tel'])) {
                    $errors["parents.{$index}.tel"] = "Le téléphone est obligatoire si le nom du parent {$parentNumber} est renseigné.";
                } elseif (!$this->isValidInternationalPhone($parentData['tel'])) {
                    $errors["parents.{$index}.tel"] = "Le format du téléphone du parent {$parentNumber} n'est pas valide.";
                }
            }
            
            // Si un téléphone est renseigné, vérifier que le nom l'est aussi
            if (!empty($parentData['tel'])) {
                if (empty($parentData['nom'])) {
                    $errors["parents.{$index}.nom"] = "Le nom est obligatoire si le téléphone du parent {$parentNumber} est renseigné.";
                } elseif (!$this->isValidInternationalPhone($parentData['tel'])) {
                    $errors["parents.{$index}.tel"] = "Le format du téléphone du parent {$parentNumber} n'est pas valide.";
                }
            }
            
            // Si une profession est renseignée, vérifier que le nom l'est aussi
            if (!empty($parentData['profession'])) {
                if (empty($parentData['nom'])) {
                    $errors["parents.{$index}.nom"] = "Le nom est obligatoire si la profession du parent {$parentNumber} est renseignée.";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Génère un PDF "solde de tout compte" pour un résident avec date de départ
     */
    public function generateSoldeToutComptePdf(Request $request, $idResident)
    {
        $resident = Resident::with(['chambre', 'adresse'])->findOrFail($idResident);
        
        // Vérifier que le résident a une date de départ
        if (!$resident->DATEDEPART) {
            return redirect()->back()->with('error', 'Le résident doit avoir une date de départ pour générer le solde de tout compte.');
        }
        
        // Préparer les données pour le PDF
        $data = [
            'resident' => $resident,
            'dateGeneration' => now()->format('d/m/Y'),
            'heureGeneration' => now()->format('H:i'),
            'redevance' => $request->input('redevance', '0'),
            'depotGarantie' => $request->input('depot_garantie', '505'),
            'deductions' => $request->input('deductions', '0'),
            'soldeCaf' => $request->input('solde_caf', '0'),
            'montantDuAuResident' => $request->input('montant_du_au_resident', '505'),
            'montantDuParResident' => $request->input('montant_du_par_resident', '0'),
            'dateReglement' => $request->input('date_reglement', now()->format('d/m/Y'))
        ];
        
        // Générer le PDF
        $pdf = Pdf::loadView('pdf.solde-tout-compte', $data);
        
        // Nom du fichier avec nom du résident et date
        $fileName = 'Solde_tout_compte_' . 
                   str_replace(' ', '_', $resident->NOMRESIDENT) . '_' . 
                   str_replace(' ', '_', $resident->PRENOMRESIDENT ?? '') . '_' .
                   now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
}
