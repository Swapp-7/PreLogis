<?php

namespace App\Exports;

use App\Models\Occupation;
use App\Models\Salle;
use App\Models\MomentEvenement;
use App\Models\Dates;
use App\Models\Evenement;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class OccupationsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $year;
    protected $occupationColors = [];
    protected $monthHeaders = [];
    protected $dateHeaders = [];

    public function __construct($year = null)
    {
        $this->year = $year ?? now()->year;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->addMonthHeaders($event->sheet);
                $this->applyCellColors($event->sheet);
            },
        ];
    }

    public function array(): array
    {
        $startDate = Carbon::createFromDate($this->year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($this->year, 12, 31)->endOfYear();

        // Récupérer toutes les salles et moments
        $salles = Salle::orderBy('LIBELLESALLE')->get();
        $moments = MomentEvenement::orderBy('IDMOMENT')->get();

        // Générer toutes les dates de l'année (tous les jours, y compris weekends)
        $allDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Inclure tous les jours de l'année
            $allDates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Stocker les dates pour les en-têtes
        $this->dateHeaders = $allDates;

        // Récupérer toutes les occupations de l'année
        $occupations = Occupation::with(['evenement'])
            ->whereBetween('DATEPLANNING', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy(function ($item) {
                return $item->IDSALLE . '_' . $item->DATEPLANNING . '_' . $item->IDMOMENT;
            });

        $data = [];
        $rowIndex = 0;

        foreach ($salles as $salle) {
            foreach ($moments as $moment) {
                $row = [
                    $salle->LIBELLESALLE,
                    $moment->LIBELLEMOMENT
                ];

                $colIndex = 3; // Commencer à la colonne C (après Salle et Moment)
                // Pour chaque date de l'année
                foreach ($allDates as $date) {
                    $key = $salle->IDSALLE . '_' . $date . '_' . $moment->IDMOMENT;
                    $occupation = $occupations->get($key)?->first();
                    
                    if ($occupation && $occupation->evenement && $occupation->ESTOCCUPEE) {
                        $row[] = $occupation->evenement->NOMEVENEMENT;
                        // Stocker la couleur pour cette cellule (ligne 2 + rowIndex car ligne 1 = mois, ligne 2 = dates, données à partir ligne 3)
                        $this->occupationColors[$rowIndex + 2][$colIndex] = $occupation->evenement->COULEUR ?? '#FFFFFF';
                    } else {
                        $row[] = ''; // Cellule vide pour les créneaux libres
                    }
                    $colIndex++;
                }

                $data[] = $row;
                $rowIndex++;
            }
        }

        return $data;
    }

    public function headings(): array
    {
        $startDate = Carbon::createFromDate($this->year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($this->year, 12, 31)->endOfYear();

        // Générer les en-têtes des dates
        $dateHeaders = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Inclure tous les jours de l'année
            $dateHeaders[] = $currentDate->format('d/m');
            $currentDate->addDay();
        }

        // Les en-têtes de base (sans les mois qui seront ajoutés séparément)
        return array_merge(['Salle', 'Moment'], $dateHeaders);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style pour l'en-tête des dates (ligne 2 après insertion de la ligne des mois)
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '20364B']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FDC11F']
                ]
            ]
        ]);

        // Style pour les colonnes Salle et Moment (à partir de la ligne 3)
        $sheet->getStyle('A3:B' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 9
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style pour toutes les cellules de données
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'font' => [
                'size' => 8
            ]
        ]);

        // Appliquer des couleurs alternées pour améliorer la compréhension
        $this->applyAlternatingColors($sheet, $lastColumn, $lastRow);

        // Fixer les deux premières colonnes (après la ligne des mois)
        $sheet->freezePane('C2');

        return [];
    }

    public function columnWidths(): array
    {
        $startDate = Carbon::createFromDate($this->year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($this->year, 12, 31)->endOfYear();

        // Compter le nombre total de jours dans l'année
        $totalDaysCount = 0;
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $totalDaysCount++;
            $currentDate->addDay();
        }

        $widths = [
            'A' => 25,  // Salle - élargie pour les noms longs
            'B' => 20,  // Moment - élargie pour les libellés
        ];

        // Largeur élargie pour toutes les colonnes de dates pour améliorer la lisibilité
        for ($i = 0; $i < min($totalDaysCount, 400); $i++) {
            if ($i < 24) { // C à Z
                $column = chr(67 + $i);
                $widths[$column] = 12; // Doublé pour permettre la lecture du contenu
            } else { // AA, AB, AC, etc.
                $firstChar = chr(65 + intval(($i - 24) / 26));
                $secondChar = chr(65 + (($i - 24) % 26));
                $widths[$firstChar . $secondChar] = 12; // Doublé pour permettre la lecture du contenu
            }
        }

        return $widths;
    }

    private function addMonthHeaders($sheet)
    {
        $worksheet = $sheet->getDelegate();
        
        // Insérer une ligne au début pour les mois
        $worksheet->insertNewRowBefore(1, 1);
        
        // Fusionner les cellules pour "Salle" et "Moment"
        $worksheet->mergeCells('A1:A2');
        $worksheet->mergeCells('B1:B2');
        $worksheet->setCellValue('A1', 'Salle');
        $worksheet->setCellValue('B1', 'Moment');

        $currentMonth = null;
        $monthStartCol = 3; // Colonne C
        $colIndex = 3;
        $monthSeparators = []; // Pour stocker les positions des séparateurs de mois

        foreach ($this->dateHeaders as $date) {
            $dateCarbon = Carbon::parse($date);
            $month = $dateCarbon->translatedFormat('F Y');
            
            if ($currentMonth !== $month) {
                if ($currentMonth !== null) {
                    // Fusionner les cellules du mois précédent
                    if ($colIndex > $monthStartCol) {
                        $startCol = $this->getColumnName($monthStartCol);
                        $endCol = $this->getColumnName($colIndex - 1);
                        $worksheet->mergeCells($startCol . '1:' . $endCol . '1');
                        $worksheet->setCellValue($startCol . '1', $currentMonth);
                        
                        // Marquer la position pour le séparateur de mois (côté droit du mois précédent)
                        $monthSeparators[] = $colIndex - 1;
                    }
                }
                $currentMonth = $month;
                $monthStartCol = $colIndex;
            }
            $colIndex++;
        }

        // Fusionner le dernier mois
        if ($currentMonth !== null && $colIndex > $monthStartCol) {
            $startCol = $this->getColumnName($monthStartCol);
            $endCol = $this->getColumnName($colIndex - 1);
            $worksheet->mergeCells($startCol . '1:' . $endCol . '1');
            $worksheet->setCellValue($startCol . '1', $currentMonth);
        }

        // Styliser la ligne des mois
        $lastColumn = $this->getColumnName($colIndex - 1);
        $worksheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '375672']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FDC11F']
                ]
            ]
        ]);

        // Ajouter les traits verticaux épais pour séparer les mois
        $lastRow = $worksheet->getHighestRow();
        foreach ($monthSeparators as $separatorCol) {
            $columnName = $this->getColumnName($separatorCol + 1); // Décaler d'une colonne vers la droite
            // Appliquer une bordure gauche épaisse à la colonne suivante
            $worksheet->getStyle($columnName . '1:' . $columnName . $lastRow)->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => '20364B'] // Couleur sombre pour le séparateur
                    ]
                ]
            ]);
        }

        // Ajouter un séparateur épais après les colonnes Salle et Moment
        $worksheet->getStyle('C1:C' . $lastRow)->applyFromArray([
            'borders' => [
                'left' => [
                    'borderStyle' => Border::BORDER_THICK,
                    'color' => ['rgb' => '20364B']
                ]
            ]
        ]);

        // Ajouter des bordures horizontales entre les salles pour la lisibilité
        $this->addSalleSeparators($worksheet);
    }

    private function addSalleSeparators($worksheet)
    {
        $salles = Salle::orderBy('LIBELLESALLE')->get();
        $moments = MomentEvenement::orderBy('IDMOMENT')->get();
        
        $currentRow = 3; // Commencer après l'en-tête et les mois (ligne 3 maintenant)
        $lastSalleId = null;
        $endColumn = $this->getColumnName(count($this->dateHeaders) + 2);
        
        foreach ($salles as $index => $salle) {
            if ($index > 0) {
                // Ajouter une bordure horizontale épaisse au-dessus de chaque nouvelle salle
                $worksheet->getStyle('A' . $currentRow . ':' . $endColumn . $currentRow)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '20364B']
                        ]
                    ]
                ]);
            }
            $currentRow += count($moments);
        }
    }

    private function applyCellColors($sheet)
    {
        $worksheet = $sheet->getDelegate();
        
        foreach ($this->occupationColors as $row => $columns) {
            foreach ($columns as $col => $color) {
                $cellAddress = $this->getColumnName($col) . ($row + 1); // +1 car on a inséré une ligne pour les mois
                $worksheet->getStyle($cellAddress)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => ltrim($color, '#')]
                    ]
                ]);
            }
        }
    }

    private function getColumnName($columnNumber)
    {
        $columnName = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $columnName = chr(65 + ($columnNumber % 26)) . $columnName;
            $columnNumber = intval($columnNumber / 26);
        }
        return $columnName;
    }

    private function applyAlternatingColors($sheet, $lastColumn, $lastRow)
    {
        $salles = Salle::orderBy('LIBELLESALLE')->get();
        $moments = MomentEvenement::orderBy('IDMOMENT')->get();
        
        // Couleurs de fond alternées pour les groupes de salles
        $currentRow = 2; // Ligne 2 car on a les mois en ligne 1 et on commence les données en ligne 2
        
        foreach ($salles as $index => $salle) {
            $startRow = $currentRow;
            $endRow = $currentRow + count($moments) - 1;
            
            // Appliquer une couleur de fond très légère pour les salles paires
            if ($index % 2 == 1) {
                $sheet->getStyle('C' . $startRow . ':' . $lastColumn . $endRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'] // Gris très clair
                    ]
                ]);
            }
            
            // Ajouter une bordure épaisse au-dessus de chaque nouvelle salle (sauf la première)
            if ($index > 0) {
                $sheet->getStyle('A' . $startRow . ':' . $lastColumn . $startRow)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '20364B'] // Couleur sombre pour la séparation
                        ]
                    ]
                ]);
            }
            
            // Ajouter des bordures latérales pour encadrer chaque groupe de salle
            $sheet->getStyle('A' . $startRow . ':B' . $endRow)->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '20364B']
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '20364B']
                    ]
                ]
            ]);
            
            $currentRow += count($moments);
        }
        
        // Ajouter une bordure épaisse en bas du dernier groupe de salle
        if (!empty($salles)) {
            $lastSalleRow = $currentRow - 1;
            $sheet->getStyle('A' . $lastSalleRow . ':' . $lastColumn . $lastSalleRow)->applyFromArray([
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '20364B']
                    ]
                ]
            ]);
        }
        
        // Appliquer des couleurs pour différencier les weekends
        $this->applyWeekendColors($sheet, $lastRow);
    }

    private function applyWeekendColors($sheet, $lastRow)
    {
        $colIndex = 3; // Commencer à la colonne C
        
        foreach ($this->dateHeaders as $date) {
            $dateCarbon = Carbon::parse($date);
            $columnName = $this->getColumnName($colIndex);
            
            // Appliquer une couleur différente pour les weekends
            if ($dateCarbon->isWeekend()) {
                $sheet->getStyle($columnName . '2:' . $columnName . $lastRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8F4FD'] // Bleu très clair pour les weekends
                    ]
                ]);
            }
            
            // Appliquer une couleur spéciale pour les jours fériés potentiels (1er janvier, 1er mai, etc.)
            $month = $dateCarbon->month;
            $day = $dateCarbon->day;
            
            // Quelques jours fériés français communs
            $holidays = [
                ['month' => 1, 'day' => 1],   // Nouvel An
                ['month' => 5, 'day' => 1],   // Fête du Travail
                ['month' => 5, 'day' => 8],   // Victoire 1945
                ['month' => 7, 'day' => 14],  // Fête Nationale
                ['month' => 8, 'day' => 15],  // Assomption
                ['month' => 11, 'day' => 1],  // Toussaint
                ['month' => 11, 'day' => 11], // Armistice
                ['month' => 12, 'day' => 25], // Noël
            ];
            
            foreach ($holidays as $holiday) {
                if ($month == $holiday['month'] && $day == $holiday['day']) {
                    $sheet->getStyle($columnName . '2:' . $columnName . $lastRow)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE6E6'] // Rose très clair pour les jours fériés
                        ]
                    ]);
                    break;
                }
            }
            
            $colIndex++;
        }
    }
}
