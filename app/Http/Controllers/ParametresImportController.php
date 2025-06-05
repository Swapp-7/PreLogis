<?php

namespace App\Http\Controllers;

use App\Imports\MultiResidentsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ParametresImportController extends Controller
{
    /**
     * Affiche la page d'importation Excel
     */
    public function index()
    {
        return view('parametres.import-excel');
    }

    /**
     * Traite l'importation Excel
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ], [
            'excel_file.required' => 'Veuillez sélectionner un fichier Excel.',
            'excel_file.mimes' => 'Le fichier doit être au format Excel (.xlsx, .xls) ou CSV.',
            'excel_file.max' => 'Le fichier ne doit pas dépasser 5MB.',
        ]);

        try {
            $import = new MultiResidentsImport();
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $importResults = $import->getImportResults();

            // Stocker les résultats en session pour les afficher
            session(['import_results' => $importResults]);

            if ($errorCount > 0) {
                return redirect()->back()->with('warning', 
                    "Importation terminée avec {$successCount} succès et {$errorCount} erreurs. Consultez les détails ci-dessous.");
            } else {
                return redirect()->back()->with('success', 
                    "Importation réussie ! {$successCount} résidents ont été importés.");
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 
                'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Télécharge le modèle Excel pour l'importation multi-résidents
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/modele_import_multi_residents.xlsx');
        
        if (!file_exists($filePath)) {
            // Créer le fichier de template à la volée
            return $this->generateTemplate();
        }

        return response()->download($filePath, 'modele_import_multi_residents.xlsx');
    }

    /**
     * Génère un template Excel à la volée pour l'import multi-résidents
     */
    private function generateTemplate()
    {
        $headers = [
            'nom',
            'prenom', 
            'email',
            'telephone',
            'date_naissance',
            'batiment',
            'numero_chambre',
            'nationalite',
            'etablissement',
            'annee_etude',
            'date_entree',
            'date_depart',
            'adresse',
            'code_postal',
            'ville',
            'pays',
            'parent1_nom',
            'parent1_telephone',
            'parent1_profession',
            'parent2_nom',
            'parent2_telephone',
            'parent2_profession'
        ];

        // Données d'exemple
        $exampleData = [
            [
                'Dupont',
                'Jean',
                'jean.dupont@email.com',
                '01.23.45.67.89',
                '15/01/2000',
                'A',
                '101',
                'Française',
                'Université Paris',
                '2',
                '01/09/2024',
                '30/06/2025',
                '123 Rue de la Paix',
                '75001',
                'Paris',
                'France',
                'Marie Dupont',
                '01-23-45-67-88',
                'Ingénieur',
                'Pierre Dupont',
                '+33123456787',
                'Médecin'
            ],
            [
                'Martin',
                'Sophie',
                'sophie.martin@email.com',
                '09-87-65-43-21',
                '20/05/1999',
                'B',
                '205',
                'Française',
                'INSA Lyon',
                '3',
                '01/09/2024',
                '30/06/2025',
                '456 Avenue des Champs',
                '69000',
                'Lyon',
                'France',
                'Claire Martin',
                '0987654320',
                'Professeur',
                'Paul Martin',
                '0987654319',
                'Avocat'
            ]
        ];

        // Créer un fichier CSV temporaire
        $filename = 'modele_import_multi_residents_' . date('Y_m_d_H_i_s') . '.csv';
        $filePath = public_path('templates/' . $filename);
        
        // Créer le dossier templates s'il n'existe pas
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');
        
        // Ajouter l'en-tête
        fputcsv($file, $headers, ';');
        
        // Ajouter les données d'exemple
        foreach ($exampleData as $row) {
            fputcsv($file, $row, ';');
        }
        
        fclose($file);

        return response()->download($filePath, 'modele_import_multi_residents.csv')->deleteFileAfterSend(true);
    }
}
