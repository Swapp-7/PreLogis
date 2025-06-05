<?php

namespace App\Http\Controllers;

use App\Imports\ResidentsImport;
use App\Models\Chambre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ResidentImportController extends Controller
{
    /**
     * Importe des résidents depuis un fichier Excel
     */
    public function importExcel(Request $request, $IdBatiment, $NumChambre)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'excel_file.required' => 'Veuillez sélectionner un fichier Excel.',
            'excel_file.mimes' => 'Le fichier doit être au format Excel (.xlsx, .xls) ou CSV.',
            'excel_file.max' => 'Le fichier ne doit pas dépasser 2MB.',
        ]);

        try {
            $chambre = Chambre::where('IDBATIMENT', $IdBatiment)
                              ->where('NUMEROCHAMBRE', $NumChambre)
                              ->first();

            if (!$chambre) {
                return redirect()->back()->with('error', 'Chambre non trouvée.');
            }

            $import = new ResidentsImport($chambre->IDCHAMBRE);
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
     * Télécharge le modèle Excel pour l'importation
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/modele_import_residents.xlsx');
        
        if (!file_exists($filePath)) {
            // Créer le fichier de template à la volée
            return $this->generateTemplate();
        }

        return response()->download($filePath, 'modele_import_residents.xlsx');
    }

    /**
     * Génère un template Excel à la volée
     */
    private function generateTemplate()
    {
        $headers = [
            'nom',
            'prenom', 
            'email',
            'telephone',
            'date_naissance',
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

        $exampleData = [
            [
                'Martin',
                'Jean',
                'jean.martin@email.com',
                '0123456789',
                '2000-01-15',
                'Française',
                'Université de Paris',
                '2e',
                '2024-09-01',
                '2025-06-30',
                '123 Rue de la Paix',
                '75001',
                'Paris',
                'France',
                'Martin Pierre',
                '0123456780',
                'Ingénieur',
                'Martin Marie',
                '0123456781',
                'Professeure'
            ]
        ];

        return Excel::download(new class($headers, $exampleData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $headers;
            private $data;

            public function __construct($headers, $data)
            {
                $this->headers = $headers;
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headers;
            }
        }, 'modele_import_residents.xlsx');
    }
}
