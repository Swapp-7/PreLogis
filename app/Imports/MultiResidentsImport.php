<?php

namespace App\Imports;

use App\Models\Resident;
use App\Models\Adresse;
use App\Models\Parents;
use App\Models\Chambre;
use App\Models\Batiment;
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
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class MultiResidentsImport implements ToCollection, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $successCount = 0;
    protected $errorCount = 0;
    protected $importResults = [];

    public function __construct()
    {
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        if ($collection->isEmpty()) {
            return;
        }
        
        DB::beginTransaction();

        try {
            foreach ($collection as $index => $row) {
                $this->processRow($row, $index + 1);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function processRow($row, $rowNumber)
    {
        try {
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
                return;
            }

            // Validation des formats AVANT traitement
            $validationErrors = $this->validateRowFormats($row, $rowNumber);
            if (!empty($validationErrors)) {
                $this->errorCount++;
                $this->importResults[] = [
                    'status' => 'error',
                    'row' => $rowNumber,
                    'message' => 'Erreurs de format : ' . implode(', ', $validationErrors)
                ];
                return;
            }

            // Vérifier les champs obligatoires
            $requiredFields = ['nom', 'prenom', 'email', 'telephone', 'date_naissance', 'batiment', 'numero_chambre'];
            foreach ($requiredFields as $field) {
                if (empty($row[$field])) {
                    throw new \Exception("Champ obligatoire manquant: $field");
                }
            }

            // Vérifier que le bâtiment existe
            $batiment = Batiment::where('IDBATIMENT', $row['batiment'])->first();
            if (!$batiment) {
                throw new \Exception("Bâtiment {$row['batiment']} non trouvé");
            }

            // Vérifier que la chambre existe dans ce bâtiment
            $chambre = Chambre::where('IDBATIMENT', $row['batiment'])
                              ->where('NUMEROCHAMBRE', $row['numero_chambre'])
                              ->first();
            
            if (!$chambre) {
                throw new \Exception("Chambre {$row['numero_chambre']} non trouvée dans le bâtiment {$row['batiment']}");
            }

            // Créer ou récupérer l'adresse
            $adresse = $this->createOrUpdateAdresse($row);            // Vérifier d'abord la disponibilité de la chambre AVANT de créer le résident
            $dateInscription = $this->parseDate($row['date_entree'] ?? '');
            $dateEntree = $dateInscription ? \Carbon\Carbon::parse($dateInscription->format('Y-m-d')) : null;
            $aujourdhui = now()->startOfDay();

            // Si la date d'entrée est dans le passé ou aujourd'hui, vérifier que la chambre est libre
            if ($dateEntree && $dateEntree->lte($aujourdhui)) {
                if ($chambre->IDRESIDENT !== null) {
                    throw new \Exception("La chambre {$row['numero_chambre']} du bâtiment {$row['batiment']} est déjà occupée");
                }
            }

            // Créer le résident
            $resident = new Resident();

            $resident->NOMRESIDENT = $row['nom'] ?? '';
            $resident->PRENOMRESIDENT = $row['prenom'] ?? '';
            $resident->MAILRESIDENT = $row['email'] ?? '';
            $resident->TELRESIDENT = $row['telephone'] ?? '';
            $dateNaissance = $this->parseDate($row['date_naissance'] ?? '');
            $resident->DATENAISSANCE = $dateNaissance ? $dateNaissance->format('Y-m-d') : null;
            $resident->NATIONALITE = $row['nationalite'] ?? '';
            $resident->ETABLISSEMENT = $row['etablissement'] ?? '';
            $resident->ANNEEETUDE = $row['annee_etude'] ?? '';
            $resident->DATEINSCRIPTION = $dateEntree ? $dateEntree->format('Y-m-d') : null;
            $dateDepart = $this->parseDate($row['date_depart'] ?? '');
            $resident->DATEDEPART = $dateDepart ? $dateDepart->format('Y-m-d') : null;
            $resident->CHAMBREASSIGNE = $chambre->IDCHAMBRE;
            $resident->IDADRESSE = $adresse->IDADRESSE;
            $resident->TYPE = Resident::TYPE_INDIVIDUAL;
            $resident->PHOTO = null; 
            
            $resident->save();

            // Logique d'assignation de chambre
            if ($dateEntree && $dateEntree->lte($aujourdhui)) {
                // Date d'entrée dans le passé ou aujourd'hui -> occupant actuel
                $chambre->IDRESIDENT = $resident->IDRESIDENT;
                $chambre->save();
                // Réinitialiser CHAMBREASSIGNE car il est maintenant occupant actuel
                $resident->CHAMBREASSIGNE = null;
                $resident->save();
            } else {
                // Date d'entrée dans le futur -> futur résident (CHAMBREASSIGNE déjà défini)
            }

            // Créer les parents
            $this->createParents($resident, $row);

            $this->successCount++;
            $this->importResults[] = [
                'status' => 'success',
                'row' => $rowNumber,
                'message' => "Résident {$resident->NOMRESIDENT} {$resident->PRENOMRESIDENT} importé avec succès dans la chambre {$row['numero_chambre']} du bâtiment {$row['batiment']}"
            ];

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->importResults[] = [
                'status' => 'error',
                'row' => $rowNumber,
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
        $adresse->PAYS = $row['pays'] ?? '';
        $adresse->save();

        return $adresse;
    }

    protected function createParents($resident, $row)
    {
        // Parent 1
        if (!empty($row['parent1_nom'])) {
            $parent1 = new Parents();
            $parent1->NOMPARENT = $row['parent1_nom'] ?? '';
            $parent1->TELPARENT = $row['parent1_telephone'] ?? '';
            $parent1->PROFESSION = $row['parent1_profession'] ?? '';
            $parent1->save();
            
            // Associer le parent au résident via la table de liaison
            DB::table('APOURPARENT')->insert([
                'IDRESIDENT' => $resident->IDRESIDENT,
                'IDPARENT' => $parent1->IDPARENT
            ]);
        }

        // Parent 2
        if (!empty($row['parent2_nom'])) {
            $parent2 = new Parents();
            $parent2->NOMPARENT = $row['parent2_nom'] ?? '';
            $parent2->TELPARENT = $row['parent2_telephone'] ?? '';
            $parent2->PROFESSION = $row['parent2_profession'] ?? '';
            $parent2->save();
            
            // Associer le parent au résident via la table de liaison
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
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s'];
            
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date !== false) {
                    // Retourner un objet Carbon au lieu d'une chaîne
                    return Carbon::createFromFormat($format, $dateString);
                }
            }
            
            // Si aucun format ne fonctionne, essayer Carbon
            $carbonDate = Carbon::parse($dateString);
            return $carbonDate;
            
        } catch (\Exception $e) {
            throw new \Exception("Format de date invalide: $dateString");
        }
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

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'date_naissance' => 'required',
            'batiment' => 'required',
            'numero_chambre' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'telephone.required' => 'Le téléphone est obligatoire',
            'date_naissance.required' => 'La date de naissance est obligatoire',
            'batiment.required' => 'Le bâtiment est obligatoire',
            'numero_chambre.required' => 'Le numéro de chambre est obligatoire',
        ];
    }

    /**
     * Valide les formats des données d'une ligne
     */
    protected function validateRowFormats($row, $rowNumber)
    {
        $errors = [];
        
        if (!empty($row['nom']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', trim($row['nom']))) {
            $errors[] = "Nom invalide (caractères non autorisés)";
        }
        
        if (!empty($row['prenom']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', trim($row['prenom']))) {
            $errors[] = "Prénom invalide (caractères non autorisés)";
        }
        
        if (!empty($row['email']) && !filter_var(trim($row['email']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        
        if (!empty($row['telephone']) && !preg_match('/^[\d\s\-\.\(\)\+]+$/', trim($row['telephone']))) {
            $errors[] = "Numéro de téléphone invalide (caractères non autorisés)";
        }
        
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
        
        // Validation de l'identifiant de bâtiment (lettres et/ou chiffres)
        if (!empty($row['batiment'])) {
            $batiment = trim($row['batiment']);
            if (!preg_match('/^[A-Za-z0-9]+$/', $batiment)) {
                $errors[] = "Identifiant de bâtiment invalide (lettres et chiffres uniquement) - Valeur reçue: '" . $batiment . "'";
            }
        }
        
        // Validation du numéro de chambre (entier positif)
        if (!empty($row['numero_chambre'])) {
            $numeroChambre = trim($row['numero_chambre']);
            if (!is_numeric($numeroChambre) || intval($numeroChambre) <= 0) {
                $errors[] = "Numéro de chambre invalide (doit être un nombre entier positif) - Valeur reçue: '" . $numeroChambre . "'";
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
        
        return $errors;
    }
}
