<?php

namespace App\Http\Controllers;

use App\Services\QuezadaService;
use Illuminate\Http\JsonResponse;

/**
 * Puente HTTP de Quezada para SOMA cloud.
 *
 * SOMA cloud no tiene ruta directa al MySQL de Quezada (LAN privada), así
 * que consume estos endpoints vía HTTPS con header X-API-Key (validado por
 * el middleware soma.api).
 *
 * Endpoints expuestos en routes/api.php bajo prefix `qz`:
 *   GET /api/qz/disponible          → health check
 *   GET /api/qz/verificados-hoy     → lista de hoy
 *   GET /api/qz/folio/{folio}       → datos de un folio puntual
 */
class QzApiController extends Controller
{
    public function __construct(private readonly QuezadaService $qz)
    {
    }

    /** GET /api/qz/disponible */
    public function disponible(): JsonResponse
    {
        return response()->json([
            'response'   => 1,
            'disponible' => $this->qz->disponible(),
        ]);
    }

    /** GET /api/qz/verificados-hoy */
    public function verificadosHoy(): JsonResponse
    {
        return response()->json([
            'response'    => 1,
            'verificados' => $this->qz->verificadosHoy(),
        ]);
    }

    /** GET /api/qz/folio/{folio} */
    public function obtenerPorFolio(string $folio): JsonResponse
    {
        $row = $this->qz->obtenerPorFolio($folio);
        if (!$row) {
            return response()->json([
                'response' => 0,
                'message'  => 'Folio no encontrado o no verificado',
            ], 404);
        }
        return response()->json([
            'response' => 1,
            'qz'       => $row,
        ]);
    }
}
