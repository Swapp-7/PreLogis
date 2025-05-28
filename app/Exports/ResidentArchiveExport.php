<?php

namespace App\Exports;

use App\Models\ResidentArchive;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResidentArchiveExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        if ($this->query) {
            return $this->query;
        }
        
        return ResidentArchive::with(['chambre', 'parents', 'adresse'])->get();
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Date de naissance',
            'établissement',
            'Niveau',
            'Nationalité',
            'Adresse',
            'code postal',
            'Ville',
            'Pays',
            'Nom parent 1',
            'Téléphone parent 1',
            'Nom parent 2',
            'Téléphone parent 2',
            'Chambre',
            'Date d\'inscription',
            'Date d\'archivage'
        ];
    }

    public function map($resident): array
    {
        $chambre = $resident->chambre ? 
            $resident->chambre->IDBATIMENT . $resident->chambre->NUMEROCHAMBRE : 
            'Non assigné';
            
        $parent1 = $resident->parents->isNotEmpty() ? 
            $resident->parents->first() : null;
        $parent2 = $resident->parents->isNotEmpty() ? 
            $resident->parents->last() : null;

        return [
            $resident->NOMRESIDENTARCHIVE,
            $resident->PRENOMRESIDENTARCHIVE,
            $resident->MAILRESIDENTARCHIVE,
            $resident->TELRESIDENTARCHIVE,
            $resident->DATENAISSANCEARCHIVE,
            $resident->ETABLISSEMENTARCHIVE,
            $resident->ANNEEETUDEARCHIVE,
            $resident->NATIONALITEARCHIVE,
            $resident->adresse->ADRESSE ?? 'Non renseigné',
            $resident->adresse->CODEPOSTAL ?? 'Non renseigné',
            $resident->adresse->VILLE ?? 'Non renseigné',
            $resident->adresse->PAYS ?? 'Non renseigné',
            $parent1 ? $parent1->NOMPARENT : 'Non renseigné',
            $parent1 ? $parent1->TELPARENT : 'Non renseigné',
            $parent2 ? $parent2->NOMPARENT : 'Non renseigné',
            $parent2 ? $parent2->TELPARENT : 'Non renseigné',
            $chambre,
            $resident->DATEINSCRIPTIONARCHIVE ?? 'Non renseigné',
            $resident->DATEARCHIVE
        ];
    }

    public function title(): string
    {
        return 'Liste des Résidents Archivés';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Récupérer la plage de cellules utilisées
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = 'A1:' . $highestColumn . $highestRow;
                
                // Appliquer les bordures à toutes les cellules
                $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Centrer les en-têtes
                $sheet->getStyle('A1:' . $highestColumn . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Zebra striping (lignes alternées)
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A'.$row.':'.$highestColumn.$row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('F5F5F5');
                    }
                }
                
                // Figer la première ligne pour garder les en-têtes visibles pendant le défilement
                $sheet->freezePane('A2');
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style d'en-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Hauteur de la ligne d'en-tête
            'A1:S1' => ['height' => 25],
        ];
    }
}
