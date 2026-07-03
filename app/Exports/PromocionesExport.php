<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel de promociones globales de la tienda. Recibe el arreglo ya armado
 * (primera fila = encabezados) desde el comando promociones:generar-excel.
 */
class PromocionesExport implements FromArray, ShouldAutoSize, WithStyles
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
        // Encabezado en negritas.
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
