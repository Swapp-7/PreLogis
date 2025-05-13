<?php

namespace App\Exports;

use App\Models\Chambre;
use App\Models\Resident;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PlanningResidentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $year;
    protected $cellValues = [];
    protected $monthsInfo = [];
    protected $residentColors = [];
    protected $daysInMonth = [];

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->prepareDaysInMonth();
        $this->prepareMonthsInfo();
    }

    /**
     * Prépare le nombre de jours dans chaque mois
     */
    private function prepareDaysInMonth()
    {
        for ($month = 1; $month <= 12; $month++) {
            $this->daysInMonth[$month] = Carbon::createFromDate($this->year, $month, 1)->daysInMonth;
        }
    }

    /**
     * Prépare les informations sur les mois pour les en-têtes et fusions
     */
    private function prepareMonthsInfo()
    {
        $colPosition = 0;
        
        // Pour chaque mois de l'année
        for ($month = 1; $month <= 12; $month++) {
            $daysInMonth = $this->daysInMonth[$month];
            
            $this->monthsInfo[] = [
                'month' => $month,
                'name' => Carbon::createFromDate($this->year, $month, 1)->translatedFormat('F'),
                'startCol' => $colPosition,
                'endCol' => $colPosition + $daysInMonth - 1,
                'days' => $daysInMonth
            ];
            
            $colPosition += $daysInMonth;
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Tri correctement les chambres par bâtiment puis par numéro en tant que nombre
        return Chambre::orderBy('IDBATIMENT')
                     ->orderByRaw('CAST(NUMEROCHAMBRE AS UNSIGNED) ASC')
                     ->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Pour l'en-tête, nous utilisons juste les numéros de jour
        // Les mois seront ajoutés par l'événement AfterSheet
        $headings = ['Chambre'];
        
        // Ajouter le dernier jour du mois précédent (invisible, pour l'alignement)
        $lastDayOfPrevYear = Carbon::createFromDate($this->year - 1, 12, 31);
        $headings[] = $lastDayOfPrevYear->format('j');
        
        $startDate = Carbon::createFromDate($this->year, 1, 1);
        $endDate = Carbon::createFromDate($this->year, 12, 31);
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Juste les numéros de jour (1-31)
            $headings[] = $date->format('j');
        }
        
        return $headings;
    }

    /**
    * @param Chambre $chambre
    * @return array
    */
    public function map($chambre): array
    {
        $row = [
            $chambre->IDBATIMENT . str_pad($chambre->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT)
        ];
        
        // Ajouter le dernier jour de l'année précédente
        $lastDayOfPrevYear = Carbon::createFromDate($this->year - 1, 12, 31);
        $occupantPrevYear = $this->getOccupantForDate($chambre, $lastDayOfPrevYear);
        $valuePrevYear = $occupantPrevYear ? $occupantPrevYear->NOMRESIDENT : '';
        $row[] = '';
        
        $rowValues = [];
        $rowValues[] = [
            'value' => $valuePrevYear,
            'month' => 0,
            'isPrevYear' => true
        ];
        
        // Pour chaque jour de l'année
        $startDate = Carbon::createFromDate($this->year, 1, 1);
        $endDate = Carbon::createFromDate($this->year, 12, 31);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $occupant = $this->getOccupantForDate($chambre, $date);
            $value = $occupant ? $occupant->NOMRESIDENT : '';
            $row[] = ''; // On laisse vide ici, on remplira lors de la fusion
            $rowValues[] = [
                'value' => $value,
                'month' => $date->month,
                'day' => $date->day
            ];
            
            // Attribuer une couleur cohérente à chaque résident
            if ($value && !isset($this->residentColors[$value])) {
                $this->residentColors[$value] = $this->generatePastelColor($value);
            }
        }
        
        $this->cellValues[$chambre->IDBATIMENT . $chambre->NUMEROCHAMBRE] = $rowValues;
        
        return $row;
    }

    /**
    * @param Chambre $chambre
    * @param Carbon $date
    * @return Resident|null
    */
    private function getOccupantForDate($chambre, $date)
    {
        // Vérifier le résident actuellement assigné
        if ($chambre->IDRESIDENT) {
            $currentResident = Resident::find($chambre->IDRESIDENT);
            
            if ($currentResident) {
                $dateInscription = Carbon::parse($currentResident->DATEINSCRIPTION);
                
                // Modification: ajouter un jour à la date de départ pour inclure le jour même
                $dateDepart = null;
                if ($currentResident->DATEDEPART) {
                    $dateDepart = Carbon::parse($currentResident->DATEDEPART)->addDay();
                }
                
                if ($dateInscription->lte($date) && (!$dateDepart || $dateDepart->gt($date))) {
                    return $currentResident;
                }
            }
        }
        
        // Vérifier les autres résidents qui ont occupé cette chambre à cette date
        $otherResident = Resident::where('CHAMBREASSIGNE', $chambre->IDCHAMBRE)
            ->where(function($query) use ($chambre) {
                if ($chambre->IDRESIDENT) {
                    $query->where('IDRESIDENT', '!=', $chambre->IDRESIDENT);
                }
            })
            ->whereDate('DATEINSCRIPTION', '<=', $date)
            ->where(function($query) use ($date) {
                // Modification: ajuster la requête pour inclure le jour de départ
                $query->whereNull('DATEDEPART')
                      ->orWhereRaw('DATE_ADD(DATEDEPART, INTERVAL 1 DAY) > ?', [$date]);
            })
            ->first();
            
        return $otherResident;
    }

    /**
    * Génère une couleur pastel en évitant le blanc
    * @param string $value
    * @return string Code couleur hexadécimal
    */
    private function generatePastelColor($value)
    {
        $colorHash = substr(md5($value), 0, 6);
        $r = hexdec(substr($colorHash, 0, 2));
        $g = hexdec(substr($colorHash, 2, 2));
        $b = hexdec(substr($colorHash, 4, 2));
        
        // Assurer que la couleur n'est pas trop claire (pas proche du blanc)
        $r = min(220, max(100, $r));
        $g = min(220, max(100, $g));
        $b = min(220, max(100, $b));
        
        return sprintf("%02X%02X%02X", $r, $g, $b);
    }

    /**
    * @param Worksheet $sheet
    */
    public function styles(Worksheet $sheet)
    {
        // Styles de base appliqués ici
        // Les styles pour les en-têtes de mois seront appliqués dans registerEvents
    }
    
    /**
    * @return string
    */
    public function title(): string
    {
        return 'Planning Résidents ' . $this->year;
    }
    
    /**
    * @return array
    */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                
                // Insérer une ligne au-dessus pour les mois
                $sheet->insertNewRowBefore(1, 1);
                
                // Fusionner la cellule pour "Chambre" sur les deux lignes
                $sheet->mergeCells('A1:A2');
                $sheet->setCellValue('A1', 'Chambre');
                
                // Masquer la colonne du jour précédent l'année (colonne B)
                $sheet->getColumnDimension('B')->setVisible(false);
                
                // Ajouter et fusionner les mois
                $colIndex = 3; // Commencer à la colonne C (après "Chambre" et la colonne cachée)
                foreach ($this->monthsInfo as $monthInfo) {
                    $startColLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $endColLetter = Coordinate::stringFromColumnIndex($colIndex + $monthInfo['days'] - 1);
                    
                    // Fusionner les cellules pour le mois
                    $sheet->mergeCells($startColLetter . '1:' . $endColLetter . '1');
                    
                    // Définir la valeur du mois
                    $sheet->setCellValue($startColLetter . '1', $monthInfo['name']);
                    
                    // Styles pour l'en-tête de mois
                    $sheet->getStyle($startColLetter . '1:' . $endColLetter . '1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '20364B'],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    
                    $colIndex += $monthInfo['days'];
                }
                
                // Style pour les en-têtes de jours (ligne 2)
                $sheet->getStyle('C2:' . $lastColumn . '2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A6B8A'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Style pour la première colonne (numéros de chambre)
                $sheet->getStyle('A1:A' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FDC11F'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Ajuster la hauteur des lignes d'en-tête
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                // Ajuster la largeur des colonnes de dates
                for ($col = 'C'; $col <= $lastColumn; $col++) {
                    $sheet->getColumnDimension($col)->setWidth(3);
                }
                
                // Colorer les weekends
                $startDate = Carbon::createFromDate($this->year, 1, 1);
                $colIndex = 3; // La colonne C est la première date visible (1er janvier)
                
                for ($date = $startDate; $date->year == $this->year; $date->addDay()) {
                    if ($date->isWeekend()) {
                        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
                        $sheet->getStyle($columnLetter . '2:' . $columnLetter . ($lastRow + 1))
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('EFEFEF');
                    }
                    $colIndex++;
                }
                
                // ===== FUSION DES CELLULES PAR MOIS =====
                $rowNum = 3; // Commencer à la ligne 3 (après les deux lignes d'en-tête)
                
                foreach ($this->cellValues as $chambreId => $rowData) {
                    // Traiter mois par mois
                    foreach ($this->monthsInfo as $monthIndex => $monthInfo) {
                        $monthNumber = $monthInfo['month'];
                        
                        // Filtrer les données du mois actuel
                        $monthCells = [];
                        
                        // Position actuelle dans le rowData
                        $offset = 1; // On commence après la cellule 0 (année précédente)
                        
                        // Ajouter l'offset des mois précédents
                        for ($m = 1; $m < $monthNumber; $m++) {
                            $offset += $this->daysInMonth[$m];
                        }
                        
                        // Récupérer les jours pour ce mois
                        for ($day = 0; $day < $monthInfo['days']; $day++) {
                            $pos = $offset + $day;
                            if (isset($rowData[$pos])) {
                                $monthCells[] = $rowData[$pos];
                            }
                        }
                        
                        // Si ce mois n'a pas de données, passer au suivant
                        if (empty($monthCells)) {
                            continue;
                        }
                                                
                        // Premier passage: identifier les séquences
                        $sequences = [];
                        $seqStart = 0;
                        $currentSeqValue = $monthCells[0]['value']; // On prend la valeur du premier jour
                        
                        for ($i = 1; $i < count($monthCells); $i++) {
                            $cellValue = $monthCells[$i]['value'];
                            
                            // Changement de résident
                            if ($cellValue !== $currentSeqValue) {
                                if ($currentSeqValue !== '') {
                                    $sequences[] = [
                                        'value' => $currentSeqValue,
                                        'start' => $seqStart,
                                        'end' => $i - 1
                                    ];
                                }
                                
                                $currentSeqValue = $cellValue;
                                $seqStart = $i;
                            }
                        }
                        
                        // Ajouter la dernière séquence
                        if ($currentSeqValue !== '') {
                            $sequences[] = [
                                'value' => $currentSeqValue,
                                'start' => $seqStart,
                                'end' => count($monthCells) - 1
                            ];
                        }
                        
                        // Deuxième passage: appliquer les fusions et styles
                        // Définir la colonne de départ du mois actuel (C pour janvier, puis décalage selon les jours des mois précédents)
                        $monthStartCol = 3; // Colonne C
                        for ($m = 0; $m < $monthIndex; $m++) {
                            $monthStartCol += $this->monthsInfo[$m]['days'];
                        }
                        
                        foreach ($sequences as $seq) {
                            $startColIndex = $monthStartCol + $seq['start'];
                            $endColIndex = $monthStartCol + $seq['end'];
                            
                            $startColLetter = Coordinate::stringFromColumnIndex($startColIndex);
                            $endColLetter = Coordinate::stringFromColumnIndex($endColIndex);
                            
                            // Fusionner les cellules
                            if ($startColLetter !== $endColLetter) {
                                $sheet->mergeCells($startColLetter . $rowNum . ':' . $endColLetter . $rowNum);
                            }
                            
                            // Placer la valeur uniquement dans la première cellule
                            $sheet->setCellValue($startColLetter . $rowNum, $seq['value']);
                            
                            // Appliquer les styles
                            $sheet->getStyle($startColLetter . $rowNum . ':' . $endColLetter . $rowNum)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            
                            // Couleur de fond
                            $color = $this->residentColors[$seq['value']];
                            $sheet->getStyle($startColLetter . $rowNum . ':' . $endColLetter . $rowNum)
                                ->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB($color);
                        }
                    }
                    
                    $rowNum++;
                }
                
                // Ajouter des bordures à tout le tableau
                $sheet->getStyle('A1:' . $lastColumn . ($lastRow + 1))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Bordure plus épaisse entre les mois
                $colIndex = 3; // Commencer à la colonne C (après "Chambre" et colonne cachée)
                foreach ($this->monthsInfo as $index => $monthInfo) {
                    if ($index > 0) { // Pas besoin de bordure pour le premier mois
                        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
                        $sheet->getStyle($columnLetter . '1:' . $columnLetter . ($lastRow + 1))
                            ->getBorders()
                            ->getLeft()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                    }
                    $colIndex += $monthInfo['days'];
                }
                
                // Figer les panneaux pour toujours voir la colonne des chambres et les lignes d'en-tête
                $sheet->freezePane('C3');
            },
        ];
    }
}