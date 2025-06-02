<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Fichier;
use App\Models\Resident;
use App\Services\FileEncryptionService;

class FichierController extends Controller
{
    protected $encryptionService;
    
    public function __construct(FileEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function uploadFichier(Request $request, $idResident)
    {
        $request->validate([
            'fichier.*' => 'required|mimes:jpg,jpeg,png,pdf,gif|max:2048',
        ]);

        if ($request->hasFile('fichier')) {
            foreach ($request->file('fichier') as $file) {
                try {
                    // Chiffrer et sauvegarder le fichier
                    $encryptedPath = $this->encryptionService->encryptAndStore($file);
                    
                    // Sauvegarder les informations en base
                    $fichier = new Fichier();
                    $fichier->NOMFICHIER = $file->getClientOriginalName();
                    $fichier->CHEMINFICHIER = $encryptedPath;
                    $fichier->IDRESIDENT = $idResident;
                    $fichier->save();
                } catch (\Exception $e) {
                    Log::error('Erreur lors du chiffrement du fichier: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Erreur lors du téléchargement du fichier.');
                }
            }
        }

        return redirect()->back()->with('success', 'Fichiers téléchargés et chiffrés avec succès.');
    }

    public function supprimerFichier($idFichier)
    {
        $fichier = Fichier::find($idFichier);

        if (!$fichier) {
            return redirect()->back()->with('error', 'Fichier non trouvé.');
        }

        // Supprimer le fichier chiffré du stockage
        if (Storage::exists($fichier->CHEMINFICHIER)) {
            Storage::delete($fichier->CHEMINFICHIER);
        }

        $fichier->delete();

        return redirect()->back()->with('success', 'Fichier supprimé avec succès.');
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
        $zipFilePath = storage_path('app/temp/' . $zipFileName);
        
        // Créer le dossier temp s'il n'existe pas
        if (!is_dir(dirname($zipFilePath))) {
            mkdir(dirname($zipFilePath), 0755, true);
        }
        
        // Créer une nouvelle archive ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
            return redirect()->back()->with('error', 'Impossible de créer l\'archive zip.');
        }
        
        // Ajouter les fichiers déchiffrés à l'archive
        foreach ($fichiers as $fichier) {
            try {
                $decryptedContent = $this->encryptionService->decrypt($fichier->CHEMINFICHIER);
                $zip->addFromString($fichier->NOMFICHIER, $decryptedContent);
            } catch (\Exception $e) {
                Log::error('Erreur lors du déchiffrement du fichier: ' . $e->getMessage());
            }
        }
        
        $zip->close();
        
        // Téléchargement du fichier ZIP
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
    
    /**
     * Afficher un fichier déchiffré
     */
    public function viewFile($idFichier)
    {
        $fichier = Fichier::find($idFichier);
        
        if (!$fichier) {
            abort(404);
        }
        
        try {
            $decryptedContent = $this->encryptionService->decrypt($fichier->CHEMINFICHIER);
            
            // Déterminer le type MIME
            $extension = pathinfo($fichier->NOMFICHIER, PATHINFO_EXTENSION);
            $mimeType = $this->getMimeType($extension);
            
            return response($decryptedContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $fichier->NOMFICHIER . '"');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du déchiffrement du fichier: ' . $e->getMessage());
            abort(500);
        }
    }
    
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}