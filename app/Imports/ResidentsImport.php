<?php

namespace App\Imports;

use App\Models\Resident;
use App\Models\Adresse;
use App\Models\Parents;
use App\Models\Chambre;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ResidentsImport implements ToCollection, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $chambreId;
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $importResults = [];

    public function __construct($chambreId)
    {
        $this->chambreId = $chambreId;
        Log::info('ResidentsImport initialized with chambreId: ' . $chambreId);
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        Log::info('=== DÉBUT IMPORTATION ===');
        Log::info('Starting import with ' . $collection->count() . ' rows');
        
        if ($collection->isEmpty()) {
            Log::warning('Collection is empty - no data to import');
            return;
        }
        
        // Log the first few rows to see the structure
        foreach ($collection->take(2) as $index => $row) {
            Log::info('Row ' . ($index + 1) . ' structure:', $row->toArray());
        }
        
        DB::beginTransaction();

        try {
            foreach ($collection as $index => $row) {
                Log::info('=== PROCESSING ROW ' . ($index + 1) . ' ===');
                Log::info('Row data:', $row->toArray());
                $this->processRow($row);
            }

            DB::commit();
            Log::info('=== Import completed successfully ===');
            Log::info('Success count: ' . $this->successCount);
            Log::info('Error count: ' . $this->errorCount);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== Import failed ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    protected function processRow($row)
    {
        try {
            Log::info('=== DÉBUT PROCESSROW ===');
            Log::info('Processing resident data', [
                'nom' => $row['nom'] ?? 'missing',
                'prenom' => $row['prenom'] ?? 'missing', 
                'email' => $row['email'] ?? 'missing'
            ]);

            // Vérifier si la ligne est vide
            $rowArray = $row->toArray();
            $hasData = false;
            foreach ($rowArray as $value) {
                if (!empty(trim($value))) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                Log::info('Row is empty, skipping');
                return;
            }

            // Vérifier les champs obligatoires
            $requiredFields = ['nom', 'prenom', 'email', 'telephone', 'date_naissance'];
            foreach ($requiredFields as $field) {
                if (empty($row[$field])) {
                    Log::warning("Missing required field: $field", ['row' => $rowArray]);
                    throw new \Exception("Champ obligatoire manquant: $field");
                }
            }

            // Validation des formats AVANT traitement
            $validationErrors = $this->validateRowFormats($row, $this->successCount + $this->errorCount + 1);
            if (!empty($validationErrors)) {
                Log::error('Validation errors for row', $validationErrors);
                throw new \Exception('Erreurs de format : ' . implode(', ', $validationErrors));
            }

            Log::info('All required fields present, creating address...');

            // Créer ou récupérer l'adresse
            $adresse = $this->createOrUpdateAdresse($row);
            Log::info('Address created with ID: ' . $adresse->IDADRESSE);

            Log::info('Creating resident...');
            // Créer le résident
            $resident = new Resident();
            $resident->NOMRESIDENT = $row['nom'] ?? '';
            $resident->PRENOMRESIDENT = $row['prenom'] ?? '';
            $resident->MAILRESIDENT = $row['email'] ?? '';
            $resident->TELRESIDENT = $row['telephone'] ?? '';
            
            // Parser les dates et les formater pour la base de données
            $dateNaissance = $this->parseDate($row['date_naissance'] ?? '');
            $resident->DATENAISSANCE = $dateNaissance ? $dateNaissance->format('Y-m-d') : null;
            
            $resident->NATIONALITE = $row['nationalite'] ?? '';
            $resident->ETABLISSEMENT = $row['etablissement'] ?? '';
            $resident->ANNEEETUDE = $row['annee_etude'] ?? '';
            
            $dateInscription = $this->parseDate($row['date_entree'] ?? '');
            $resident->DATEINSCRIPTION = $dateInscription ? $dateInscription->format('Y-m-d') : null;
            
            $dateDepart = $this->parseDate($row['date_depart'] ?? '');
            $resident->DATEDEPART = $dateDepart ? $dateDepart->format('Y-m-d') : null;
            
            $resident->CHAMBREASSIGNE = $this->chambreId;
            $resident->IDADRESSE = $adresse->IDADRESSE;
            $resident->TYPE = Resident::TYPE_INDIVIDUAL;
            $resident->PHOTO = null; // Photo par défaut
            
            Log::info('Saving resident...', [
                'NOMRESIDENT' => $resident->NOMRESIDENT,
                'PRENOMRESIDENT' => $resident->PRENOMRESIDENT,
                'CHAMBREASSIGNE' => $resident->CHAMBREASSIGNE
            ]);
            
            $resident->save();
            Log::info('Resident created with ID: ' . $resident->IDRESIDENT);

            // Logique d'assignation de chambre comme dans le contrôleur
            $dateEntree = \Carbon\Carbon::parse($resident->DATEINSCRIPTION);
            $aujourdhui = now()->startOfDay();

            if ($dateEntree->lte($aujourdhui)) {
                // Date d'entrée dans le passé ou aujourd'hui -> occupant actuel
                $chambre = Chambre::find($this->chambreId);
                if ($chambre) {
                    Log::info('Assigning resident as current occupant (IDRESIDENT)', [
                        'chambre_id' => $chambre->IDCHAMBRE,
                        'resident_id' => $resident->IDRESIDENT
                    ]);
                    $chambre->IDRESIDENT = $resident->IDRESIDENT;
                    $chambre->save();
                    // Réinitialiser CHAMBREASSIGNE car il est maintenant occupant actuel
                    $resident->CHAMBREASSIGNE = null;
                    $resident->save();
                }
            } else {
                // Date d'entrée dans le futur -> futur résident (CHAMBREASSIGNE déjà défini)
                Log::info('Resident assigned as future occupant (CHAMBREASSIGNE)', [
                    'chambre_id' => $this->chambreId,
                    'date_entree' => $resident->DATEINSCRIPTION
                ]);
            }

            // Créer les parents
            Log::info('Creating parents...');
            $this->createParents($resident, $row);

            $this->successCount++;
            $this->importResults[] = [
                'status' => 'success',
                'row' => $this->successCount + $this->errorCount,
                'message' => "Résident {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT} importé avec succès"
            ];
            
            Log::info('=== ROW PROCESSED SUCCESSFULLY ===');

        } catch (\Exception $e) {
            Log::error('=== ERROR PROCESSING ROW ===');
            Log::error('Error processing row: ' . $e->getMessage(), [
                'row_data' => $row->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorCount++;
            $this->importResults[] = [
                'status' => 'error',
                'row' => $this->successCount + $this->errorCount,
                'message' => "Erreur lors de l'importation : " . $e->getMessage()
            ];
        }
    }

    protected function createOrUpdateAdresse($row)
    {
        $adresse = new Adresse();
        $adresse->ADRESSE = $row['adresse'] ?? '';
        $adresse->CODEPOSTAL = $row['code_postal'] ?? '';
        $adresse->VILLE = $row['ville'] ?? '';
        $adresse->PAYS = $row['pays'] ?? 'France';
        $adresse->save();

        return $adresse;
    }

    protected function createParents($resident, $row)
    {
        // Parent 1
        if (!empty($row['parent1_nom']) || !empty($row['parent1_telephone'])) {
            $parent1 = new Parents();
            $parent1->NOMPARENT = $row['parent1_nom'] ?? '';
            $parent1->TELPARENT = $row['parent1_telephone'] ?? '';
            $parent1->PROFESSION = $row['parent1_profession'] ?? '';
            $parent1->save();

            // Associer le parent au résident
            DB::table('APOURPARENT')->insert([
                'IDRESIDENT' => $resident->IDRESIDENT,
                'IDPARENT' => $parent1->IDPARENT
            ]);
        }

        // Parent 2
        if (!empty($row['parent2_nom']) || !empty($row['parent2_telephone'])) {
            $parent2 = new Parents();
            $parent2->NOMPARENT = $row['parent2_nom'] ?? '';
            $parent2->TELPARENT = $row['parent2_telephone'] ?? '';
            $parent2->PROFESSION = $row['parent2_profession'] ?? '';
            $parent2->save();

            // Associer le parent au résident
            DB::table('APOURPARENT')->insert([
                'IDRESIDENT' => $resident->IDRESIDENT,
                'IDPARENT' => $parent2->IDPARENT
            ]);
        }
    }

    protected function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Essayer différents formats de date
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
            
            foreach ($formats as $format) {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date !== false) {
                    return $date; // Retourner l'objet Carbon
                }
            }

            // Si c'est un nombre (Excel serialized date)
            if (is_numeric($dateString)) {
                $date = Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($dateString - 2);
                return $date; // Retourner l'objet Carbon
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        // Temporairement désactivé pour le debug
        return [];
        /*
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'date_naissance' => 'required',
            'nationalite' => 'required|string|max:50',
            'etablissement' => 'required|string|max:100',
            'annee_etude' => 'required|string|max:10',
            'date_entree' => 'required',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:100',
            'pays' => 'nullable|string|max:100',
        ];
        */
    }

    public function customValidationMessages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'telephone.required' => 'Le téléphone est obligatoire',
            'date_naissance.required' => 'La date de naissance est obligatoire',
            'nationalite.required' => 'La nationalité est obligatoire',
            'etablissement.required' => 'L\'établissement est obligatoire',
            'annee_etude.required' => 'L\'année d\'étude est obligatoire',
            'date_entree.required' => 'La date d\'entrée est obligatoire',
            'adresse.required' => 'L\'adresse est obligatoire',
            'code_postal.required' => 'Le code postal est obligatoire',
            'ville.required' => 'La ville est obligatoire',
        ];
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getImportResults()
    {
        return $this->importResults;
    }

    /**
     * Valide les formats des données d'une ligne
     */
    protected function validateRowFormats($row, $rowNumber)
    {
        $errors = [];
        
        // Validation du nom (lettres, espaces, tirets, apostrophes seulement)
        if (!empty($row['nom']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', trim($row['nom']))) {
            $errors[] = "Nom invalide (caractères non autorisés)";
        }
        
        // Validation du prénom (lettres, espaces, tirets, apostrophes seulement)
        if (!empty($row['prenom']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', trim($row['prenom']))) {
            $errors[] = "Prénom invalide (caractères non autorisés)";
        }
        
        // Validation de l'email
        if (!empty($row['email']) && !filter_var(trim($row['email']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        
        // Validation du téléphone (chiffres, espaces, tirets, points, parenthèses, + seulement)
        if (!empty($row['telephone']) && !preg_match('/^[\d\s\-\.\(\)\+]+$/', trim($row['telephone']))) {
            $errors[] = "Numéro de téléphone invalide (caractères non autorisés)";
        }
        
        // Validation longueur téléphone (entre 8 et 20 caractères après nettoyage)
        if (!empty($row['telephone'])) {
            $cleanPhone = preg_replace('/[\s\-\.\(\)]/', '', trim($row['telephone']));
            if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 20) {
                $errors[] = "Numéro de téléphone doit contenir entre 8 et 20 chiffres";
            }
        }
        
        // Validation de la date de naissance
        if (!empty($row['date_naissance'])) {
            $date = $this->parseDate($row['date_naissance']);
            if (!$date) {
                $errors[] = "Date de naissance invalide (format attendu: DD/MM/YYYY ou YYYY-MM-DD)";
            } else {
                // Vérifier que la date est réaliste (entre 1900 et aujourd'hui)
                $year = $date->year;
                if ($year < 1900 || $year > now()->year) {
                    $errors[] = "Date de naissance non réaliste (année entre 1900 et " . now()->year . ")";
                }
                
                // Vérifier que la personne n'est pas née dans le futur
                if ($date->isFuture()) {
                    $errors[] = "Date de naissance ne peut pas être dans le futur";
                }
            }
        }
        
        // Validation des téléphones des parents
        foreach (['parent1_telephone', 'parent2_telephone'] as $parentPhone) {
            if (!empty($row[$parentPhone]) && !preg_match('/^[\d\s\-\.\(\)\+]+$/', trim($row[$parentPhone]))) {
                $errors[] = "Téléphone parent invalide (caractères non autorisés)";
            }
            
            if (!empty($row[$parentPhone])) {
                $cleanPhone = preg_replace('/[\s\-\.\(\)]/', '', trim($row[$parentPhone]));
                if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 20) {
                    $errors[] = "Téléphone parent doit contenir entre 8 et 20 chiffres";
                }
            }
        }
        
        // Validation des noms des parents (lettres, espaces, tirets, apostrophes seulement)
        foreach (['parent1_nom', 'parent2_nom'] as $parentName) {
            if (!empty($row[$parentName]) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', trim($row[$parentName]))) {
                $errors[] = "Nom de parent invalide (caractères non autorisés)";
            }
        }
        
        // Validation du code postal (chiffres seulement, longueur appropriée)
        if (!empty($row['code_postal']) && !preg_match('/^\d{4,6}$/', trim($row['code_postal']))) {
            $errors[] = "Code postal invalide (4 à 6 chiffres requis)";
        }
        
        // Validation de la nationalité (lettres et espaces seulement)
        if (!empty($row['nationalite']) && !preg_match('/^[a-zA-ZÀ-ÿ\s]+$/u', trim($row['nationalite']))) {
            $errors[] = "Nationalité invalide (lettres et espaces seulement)";
        }
        
        // Validation de l'année d'étude (chiffres seulement, entre 1 et 10)
        if (!empty($row['annee_etude'])) {
            $anneeEtude = trim($row['annee_etude']);
            if (!is_numeric($anneeEtude) || intval($anneeEtude) < 1 || intval($anneeEtude) > 10) {
                $errors[] = "Année d'étude invalide (nombre entre 1 et 10) - Valeur reçue: '" . $anneeEtude . "'";
            }
        }
        
        return $errors;
    }
}
