<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel simple del carrito auxiliar: CLAVE y CANTIDAD. Recibe el arreglo ya
 * armado (primera fila = encabezados) desde el controlador, tomado solo de la
 * sesion (sin analisis de precios/existencias), para que sea instantaneo.
 */
class CarritoRapidoExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected array $data;

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
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
