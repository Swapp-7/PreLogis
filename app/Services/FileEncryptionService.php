<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class FileEncryptionService
{
    /**
     * Chiffrer et sauvegarder un fichier
     */
    public function encryptAndStore($file, $directory = 'encrypted_files')
    {
        // Lire le contenu du fichier
        $fileContent = file_get_contents($file->getRealPath());
        
        // Chiffrer le contenu
        $encryptedContent = Crypt::encrypt($fileContent);
        
        // Générer un nom de fichier unique
        $filename = time() . '_' . uniqid() . '.enc';
        
        // Sauvegarder le fichier chiffré
        $path = $directory . '/' . $filename;
        Storage::put($path, $encryptedContent);
        
        return $path;
    }
    
    /**
     * Déchiffrer un fichier
     */
    public function decrypt($encryptedPath)
    {
        if (!Storage::exists($encryptedPath)) {
            throw new \Exception('Fichier chiffré non trouvé');
        }
        
        // Lire le contenu chiffré
        $encryptedContent = Storage::get($encryptedPath);
        
        // Déchiffrer
        $decryptedContent = Crypt::decrypt($encryptedContent);
        
        return $decryptedContent;
    }
    
    /**
     * Créer un fichier temporaire déchiffré pour affichage
     */
    public function createTempDecryptedFile($encryptedPath, $originalName)
    {
        $decryptedContent = $this->decrypt($encryptedPath);
        
        // Créer un fichier temporaire
        $tempPath = 'temp/' . uniqid() . '_' . $originalName;
        Storage::put($tempPath, $decryptedContent);
        
        return $tempPath;
    }
    
    /**
     * Nettoyer les fichiers temporaires
     */
    public function cleanupTempFiles()
    {
        $tempFiles = Storage::files('temp');
        foreach ($tempFiles as $file) {
            // Supprimer les fichiers temporaires de plus d'une heure
            if (Storage::lastModified($file) < now()->subHour()->timestamp) {
                Storage::delete($file);
            }
        }
    }
}