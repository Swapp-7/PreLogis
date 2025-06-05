<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load('public/templates/test_import.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    
    echo "=== CONTENU DU FICHIER EXCEL ===\n\n";
    
    // Lire les 5 premières lignes
    for ($row = 1; $row <= 5; $row++) {
        echo "Ligne $row:\n";
        for ($col = 'A'; $col <= 'Z'; $col++) {
            $cellValue = $worksheet->getCell($col . $row)->getValue();
            if ($cellValue !== null && $cellValue !== '') {
                echo "  $col$row: '$cellValue'\n";
            }
        }
        echo "\n";
    }
    
    // Afficher spécifiquement la ligne d'en-têtes (ligne 1)
    echo "=== EN-TÊTES (Ligne 1) ===\n";
    $headers = [];
    for ($col = 'A'; $col <= 'Z'; $col++) {
        $cellValue = $worksheet->getCell($col . '1')->getValue();
        if ($cellValue !== null && $cellValue !== '') {
            $headers[] = $cellValue;
            echo "$col: '$cellValue'\n";
        }
    }
    
    echo "\n=== LISTE DES EN-TÊTES ===\n";
    foreach ($headers as $index => $header) {
        echo ($index + 1) . ". '$header'\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
