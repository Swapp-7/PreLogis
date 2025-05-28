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

class FichierController extends Controller
{
    public function uploadFichier(Request $request, $idResident){
        $request->validate([
            'fichier.*' => 'required|mimes:jpg,jpeg,png,pdf,gif|max:2048', // Valider chaque fichier
        ]);
    
        if ($request->hasFile('fichier')) {
            foreach ($request->file('fichier') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = public_path('uploads');
                $file->move($path, $filename);
    
                $fichier = new Fichier();
                $fichier->NOMFICHIER = $file->getClientOriginalName();
                $fichier->CHEMINFICHIER = 'uploads/' . $filename;
                $fichier->IDRESIDENT = $idResident;
                $fichier->save();
            }
        }
        

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }
    public function supprimerFichier($idFichier)
    {
        $fichier = Fichier::find($idFichier);

        if (!$fichier) {
            return redirect()->back()->with('error', 'File not found.');
        }

        $filePath = public_path($fichier->CHEMINFICHIER);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $fichier->delete();

        return redirect()->back()->with('success', 'File deleted successfully.');
    }
    
    public function telechargerTousFichiers($idResident)
    {
        $resident = Resident::find($idResident);
        
        if (!$resident) {
            return redirect()->back()->with('error', 'Résident non trouvé.');
        }
        
        $fichiers = $resident->fichiers;
        
        if ($fichiers->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun document disponible pour ce résident.');
        }
        
        $zipFileName = $resident->NOMRESIDENT . '_' . $resident->PRENOMRESIDENT . '_documents.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);
        
        // Créer une nouvelle archive ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
            return redirect()->back()->with('error', 'Impossible de créer l\'archive zip.');
        }
        
        // Ajouter les fichiers à l'archive
        foreach ($fichiers as $fichier) {
            $filePath = public_path($fichier->CHEMINFICHIER);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $fichier->NOMFICHIER);
            }
        }
        
        $zip->close();
        
        // Téléchargement du fichier ZIP
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}