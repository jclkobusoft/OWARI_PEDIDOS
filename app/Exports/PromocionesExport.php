<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel de promociones globales de la tienda. Recibe el arreglo ya armado
 * (primera fila = encabezados) desde el comando promociones:generar-excel.
 *
 * Columnas: A CLAVE | B DESCRIPCION | C PRECIO PROMOCION | D MINIMO DE COMPRA
 *           | E VIGENCIA | F MARCA | G SUBGRUPO
 *
 * Anchos fijos (sin ShouldAutoSize) para que la DESCRIPCION no crezca de mas y
 * el cliente vea todo sin scroll horizontal; la descripcion ajusta el texto.
 */
class PromocionesExport implements FromArray, WithColumnWidths, WithStyles
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

    public function columnWidths(): array
    {
        return [
            'A' => 16,   // CLAVE
            'B' => 42,   // DESCRIPCION (acotada, con ajuste de texto)
            'C' => 15,   // PRECIO PROMOCION
            'D' => 15,   // MINIMO DE COMPRA
            'E' => 13,   // VIGENCIA
            'F' => 16,   // MARCA
            'G' => 20,   // SUBGRUPO
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezado en negritas.
        $sheet->getStyle(1)->getFont()->setBold(true);

        // La descripcion ajusta el texto dentro de su ancho para leerse completa
        // sin ensanchar la columna.
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);

        return [];
    }
}
