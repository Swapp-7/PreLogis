<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileEncryptionService;

class CleanupTempFiles extends Command
{
    protected $signature = 'files:cleanup-temp';
    protected $description = 'Nettoie les fichiers temporaires anciens';

    public function handle()
    {
        $encryptionService = new FileEncryptionService();
        $encryptionService->cleanupTempFiles();
        
        $this->info('Fichiers temporaires nettoyés avec succès.');
    }
}