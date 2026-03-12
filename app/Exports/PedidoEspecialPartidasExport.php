<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;


class PedidoEspecialPartidasExport implements FromArray,ShouldAutoSize,WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }



    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $dataRows = $this->data;

        foreach ($dataRows as $rowIndex => $row) {
            if($rowIndex != 0){
                if ($row['sae'] == 'NO ESTA EN SAE') { // Cambia la lógica condicional según tus necesidades
                    $styles[$rowIndex+1] = [
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['argb' => 'ffffb7a8'],
                        ]
                    ];
                }
            }
        }

        return $styles;
    }
}
