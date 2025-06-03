<?php


namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\ResidentArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    /**
     * Display a listing of all resident groups
     */
    public function index()
    {
        $groups = Resident::where('TYPE', Resident::TYPE_GROUP)
            ->withCount(['chambres'])
            ->get();
            
        return view('groups.index', ['groups' => $groups]);
    }
    
    /**
     * Show the details of a specific group
     */
    public function show($id)
    {
        $group = Resident::with(['chambres', 'adresse'])
            ->where('TYPE', Resident::TYPE_GROUP)
            ->findOrFail($id);
            
        return view('groups.show', [
            'group' => $group
        ]);
    }
    
    /**
     * Show form to create a new group
     */
    public function create()
    {
        return view('groups.create');
    }
    
    /**
     * Store a newly created group
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'tel' => 'nullable|string|max:10',
            'photo' => 'nullable|image|max:5120',
            'adresse.adresse' => 'required|string',
            'adresse.code_postal' => 'required|string|max:10',
            'adresse.ville' => 'required|string',
            'adresse.pays' => 'required|string',
        ], [
            'adresse.adresse.required' => 'L\'adresse est obligatoire',
            'adresse.code_postal.required' => 'Le code postal est obligatoire',
            'adresse.ville.required' => 'La ville est obligatoire',
            'adresse.pays.required' => 'Le pays est obligatoire',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create the address first
            $adresse = new \App\Models\Adresse();
            $adresse->ADRESSE = $request->input('adresse.adresse');
            $adresse->CODEPOSTAL = $request->input('adresse.code_postal');
            $adresse->VILLE = $request->input('adresse.ville');
            $adresse->PAYS = $request->input('adresse.pays');
            $adresse->save();
            
            // Create the group
            $group = new Resident();
            $group->NOMRESIDENT = $request->input('nom');
            $group->TYPE = Resident::TYPE_GROUP;
            $group->IDADRESSE = $adresse->IDADRESSE;
            
            // Set default values for required fields
            $group->MAILRESIDENT = $request->input('email') ?: 'groupe@example.com';
            $group->TELRESIDENT = $request->input('tel') ?: '0000000000';
            
            // Traitement de la photo pour le groupe
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $photo->storeAs('photos', $filename, 'public');
                $group->PHOTO = $filename;
            } else {
                $group->PHOTO = 'photo'; // valeur par défaut
            }
            
            $group->save();
            
            DB::commit();
            
            return redirect()->route('groups.show', $group->IDRESIDENT)
                ->with('success', 'Groupe créé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du groupe: ' . $e->getMessage());
        }
    }
    
    /**
     * Update the specified group
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'tel' => 'nullable|string|max:10',
            'photo' => 'nullable|image|max:5120',
            'adresse.adresse' => 'required|string',
            'adresse.code_postal' => 'required|string|max:10',
            'adresse.ville' => 'required|string',
            'adresse.pays' => 'required|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $group = Resident::with('adresse')
                ->where('TYPE', Resident::TYPE_GROUP)
                ->findOrFail($id);
                
            $group->NOMRESIDENT = $request->input('nom');
            $group->MAILRESIDENT = $request->input('email');
            $group->TELRESIDENT = $request->input('tel');
            
            // Traitement de la photo pour le groupe
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                // Supprimer l'ancienne photo si elle existe
                if ($group->PHOTO && $group->PHOTO !== 'photo' && Storage::disk('public')->exists('photos/' . $group->PHOTO)) {
                    Storage::disk('public')->delete('photos/' . $group->PHOTO);
                }
                
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $photo->storeAs('photos', $filename, 'public');
                $group->PHOTO = $filename;
            }
            
            // Update address
            if ($group->adresse) {
                $adresse = $group->adresse;
                $adresse->ADRESSE = $request->input('adresse.adresse');
                $adresse->CODEPOSTAL = $request->input('adresse.code_postal');
                $adresse->VILLE = $request->input('adresse.ville');
                $adresse->PAYS = $request->input('adresse.pays');
                $adresse->save();
            }
            
            $group->save();
            
            DB::commit();
            
            return redirect()->route('groups.show', $group->IDRESIDENT)
                ->with('success', 'Groupe mis à jour avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du groupe: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove a group from a room
     */
    public function removeFromRoom($id, $roomId)
    {
        try {
            $group = Resident::where('TYPE', Resident::TYPE_GROUP)
                ->findOrFail($id);
                
            $chambre = \App\Models\Chambre::findOrFail($roomId);
            
            if ($chambre->IDRESIDENT == $group->IDRESIDENT) {
                $chambre->IDRESIDENT = null;
                $chambre->save();
                
                return redirect()->route('groups.show', $group->IDRESIDENT)
                    ->with('success', 'Groupe retiré de la chambre avec succès');
            }
            
            return redirect()->route('groups.show', $group->IDRESIDENT)
                ->with('error', 'Le groupe n\'est pas assigné à cette chambre');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du retrait du groupe: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a group
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $group = Resident::with(['chambres'])
                ->where('TYPE', Resident::TYPE_GROUP)
                ->findOrFail($id);
            
            // 1. Archiver le groupe avant de le supprimer
            $residentController = new \App\Http\Controllers\ResidentController();
            $archiveSuccess = $residentController->archiverGroupe($group->IDRESIDENT);
            
            if (!$archiveSuccess) {
                throw new \Exception('Erreur lors de l\'archivage du groupe');
            }
            
            // 2. Supprimer les fichiers physiques (mais garder les références dans FICHIER pour l'archive)
            $fichiers = DB::table('FICHIER')->where('IDRESIDENTARCHIVE', '!=', null)->get();
            foreach ($fichiers as $fichier) {
                // Les fichiers restent liés à l'archive, on ne les supprime que physiquement si nécessaire
                if ($fichier->NOMFICHIER && !Storage::disk('public')->exists('documents/' . $fichier->NOMFICHIER)) {
                    // Le fichier physique n'existe plus, on peut nettoyer la référence
                }
            }
            
            // 3. Retirer le groupe de toutes les chambres
            DB::table('CHAMBRE')->where('IDRESIDENT', $group->IDRESIDENT)->update(['IDRESIDENT' => null]);
            
      
            // 6. Supprimer le groupe lui-même
            $group->delete();
            
            DB::commit();
            
            return redirect()->route('groups.index')
                ->with('success', 'Groupe archivé et supprimé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'archivage du groupe: ' . $e->getMessage());
        }
    }
}