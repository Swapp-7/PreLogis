<?php

// Test direct de l'importation sans passer par Excel
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\ResidentsImport;
use Illuminate\Support\Collection;

echo "=== TEST DIRECT RESIDENTIMPORT ===\n";

// Simuler des données comme si elles venaient d'Excel
$testData = collect([
    collect([
        'nom' => 'Dupont',
        'prenom' => 'Jean', 
        'email' => 'jean.dupont@test.com',
        'telephone' => '0123456789',
        'date_naissance' => '2000-01-15',
        'nationalite' => 'Française',
        'etablissement' => 'Université Test',
        'annee_etude' => '2e',
        'date_entree' => '2024-12-01',
        'date_depart' => '2025-06-30',
        'adresse' => '123 Rue de Test',
        'code_postal' => '75001',
        'ville' => 'Paris',
        'pays' => 'France'
    ])
]);

echo "Données à importer:\n";
echo json_encode($testData->first()->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Récupérer une chambre existante
$chambre = \App\Models\Chambre::first();
if (!$chambre) {
    echo "ERREUR: Aucune chambre trouvée dans la base de données!\n";
    exit(1);
}

echo "Chambre utilisée: ID = " . $chambre->IDCHAMBRE . "\n\n";

// Créer l'import et tester
$import = new ResidentsImport($chambre->IDCHAMBRE);

try {
    $import->collection($testData);
    echo "Succès: " . $import->getSuccessCount() . "\n";
    echo "Erreurs: " . $import->getErrorCount() . "\n";
    
    $results = $import->getImportResults();
    foreach ($results as $result) {
        echo "- " . $result['status'] . ": " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
