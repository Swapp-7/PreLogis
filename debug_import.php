<?php

require_once 'vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

// Créer une classe d'import simple pour le debug
class DebugImport implements \Maatwebsite\Excel\Concerns\ToCollection, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function collection(Collection $collection)
    {
        echo "=== DEBUG IMPORT ===\n";
        echo "Collection count: " . $collection->count() . "\n";
        
        if ($collection->isEmpty()) {
            echo "Collection is EMPTY!\n";
            return;
        }
        
        foreach ($collection as $index => $row) {
            echo "Row " . ($index + 1) . ":\n";
            echo "  Raw data: " . json_encode($row->toArray()) . "\n";
            echo "  Keys: " . implode(', ', array_keys($row->toArray())) . "\n";
            echo "\n";
        }
    }
}

// Créer un fichier Excel simple avec PhpSpreadsheet
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes
$headers = ['nom', 'prenom', 'email', 'telephone', 'date_naissance', 'nationalite', 'etablissement', 'annee_etude', 'date_entree', 'adresse', 'code_postal', 'ville'];
$sheet->fromArray([$headers], NULL, 'A1');

// Données test
$data = [
    ['Dupont', 'Jean', 'jean@test.com', '0123456789', '2000-01-15', 'Française', 'Université Test', '2e', '2024-12-01', '123 Rue Test', '75001', 'Paris']
];
$sheet->fromArray($data, NULL, 'A2');

// Sauvegarder
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save('public/templates/debug_test.xlsx');

echo "Fichier debug_test.xlsx créé\n";

// Tester l'import
echo "\n=== TEST IMPORT AVEC MAATWEBSITE/EXCEL ===\n";

// Simuler Laravel
if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

// Créer une instance Laravel minimale
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Tester l'import
try {
    $import = new DebugImport();
    Excel::import($import, 'public/templates/debug_test.xlsx');
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
