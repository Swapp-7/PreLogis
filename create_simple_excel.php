<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Créer un nouveau fichier Excel simple
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes exactement comme attendu par ResidentsImport
$headers = [
    'nom', 'prenom', 'email', 'telephone', 'date_naissance', 
    'nationalite', 'etablissement', 'annee_etude', 'date_entree', 'date_depart',
    'adresse', 'code_postal', 'ville', 'pays'
];

// Ajouter les en-têtes en ligne 1
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Ajouter une ligne de données simples
$data = [
    'Dupont',                    // nom
    'Jean',                      // prenom  
    'jean.dupont@test.com',      // email
    '0123456789',                // telephone
    '2000-01-15',                // date_naissance
    'Française',                 // nationalite
    'Université Test',           // etablissement
    '2e',                        // annee_etude
    '2024-12-01',                // date_entree
    '2025-06-30',                // date_depart
    '123 Rue de Test',           // adresse
    '75001',                     // code_postal
    'Paris',                     // ville
    'France'                     // pays
];

$col = 'A';
foreach ($data as $value) {
    $sheet->setCellValue($col . '2', $value);
    $col++;
}

// Sauvegarder le fichier
$writer = new Xlsx($spreadsheet);
$writer->save('public/templates/simple_test.xlsx');

echo "Fichier simple_test.xlsx créé avec succès!\n";
echo "Colonnes: " . implode(', ', $headers) . "\n";
echo "Données: " . implode(', ', $data) . "\n";
