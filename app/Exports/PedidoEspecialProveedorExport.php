<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel REDUCIDO del pedido especial que se envia al proveedor.
 * Mismo contenido que PedidoEspecialPartidasExport pero recortado:
 *   CLIENTE, PEDIDO ESPECIAL, CLAVE, CANTIDAD, CLAVE <nombre_proveedor>
 * Recibe el arreglo ya armado (primera fila = encabezados).
 */
class PedidoEspecialProveedorExport implements FromArray, ShouldAutoSize, WithStyles
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
