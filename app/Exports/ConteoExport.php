<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ConteoExport implements FromArray, WithHeadings, WithEvents
{
    protected $rows;
    protected $meta;
    protected $fecha;

    /**
     * @param array $rows   Datos planos del Excel
     * @param array $meta   Info auxiliar para estilos (por fila)
     * @param string $fecha Fecha del reporte (Y-m-d)
     */
    public function __construct(array $rows, array $meta, string $fecha)
    {
        $this->rows  = $rows;
        $this->meta  = $meta;
        $this->fecha = $fecha;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'CLAVE',
            "E1 {$this->fecha}",
            'ULT. COSTO E1',
            "E3 {$this->fecha}",
            'ULT. COSTO E3',
            'STOCK FINAL',
            'CANT 1ER',
            'UBIC 1ER',
            'DIF 1ER',
            'CANT 2DO',
            'UBIC 2DO',
            'DIF 2DO',
            'CANT 3ER',
            'UBIC 3ER',
            'DIF 3ER',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Encabezados en negritas
                $sheet->getStyle('A1:O1')->getFont()->setBold(true);

                // 1) Congelar primera fila y primera columna
                $sheet->freezePane('B2'); // congela fila 1 y columna A

                // 2) Bordes para todas las celdas
                $lastRow = count($this->rows) + 1; // +1 por encabezado
                $range   = "A1:O{$lastRow}";

                $sheet->getStyle($range)->getBorders()->getAllBorders()
                      ->setBorderStyle(Border::BORDER_THIN);

                // 3) Colores de fondo por tipo de fila
                foreach ($this->meta as $index => $metaRow) {
                    $rowNumber = $index + 2;

                    if ($metaRow['row_type'] === 'only_e1') {
                        $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFE0');
                    } elseif ($metaRow['row_type'] === 'only_e3') {
                        $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('E0FFFF');
                    }

                    if ($metaRow['diff1'] != 0) {
                        $sheet->getStyle("I{$rowNumber}")
                            ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    }

                    if ($metaRow['diff2'] != 0) {
                        $sheet->getStyle("L{$rowNumber}")
                            ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    }

                    if ($metaRow['diff3'] != 0) {
                        $sheet->getStyle("O{$rowNumber}")
                            ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    }
                }

                // 4) Autosize columnas
                foreach (range('A', 'O') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}