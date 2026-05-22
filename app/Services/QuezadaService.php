<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Único punto de acceso al MySQL de Quezada (QZ).
 *
 * Este proyecto (pedidos) vive en la oficina, en la misma LAN que QZ, y
 * sirve como PUENTE hacia SOMA cloud — SOMA cloud no tiene ruta directa a
 * la IP privada de QZ (10.10.0.160), así que consume los datos vía
 * /api/qz/* expuestos en este proyecto.
 *
 * Todas las consultas son SELECT y la sesión arranca con TRANSACTION READ
 * ONLY (config/database.php) para garantizar que QZ jamás reciba escrituras.
 */
class QuezadaService
{
    /**
     * Pedidos verificados en QZ dentro del rango dado.
     *
     * @return array<int,object>
     */
    public function verificadosEnRango(Carbon $desde, Carbon $hasta): array
    {
        try {
            return DB::connection('quezada')->select(
                "SELECT
                    cs.documento     AS folio,
                    cs.verificador   AS verificador,
                    cs.fechatermino  AS fechatermino,
                    cs.verificadas   AS piezas,
                    cs.observaciones AS observaciones,
                    cs.referencia    AS referencia,
                    p.cliente        AS cliente_clave,
                    p.nombrecliente  AS cliente_nombre
                FROM contenedorsurtido cs
                LEFT JOIN pedido p ON p.flpedido = cs.flpedido
                WHERE cs.status = 'FA'
                  AND cs.fechatermino BETWEEN ? AND ?
                ORDER BY cs.fechatermino DESC",
                [$desde->toDateTimeString(), $hasta->toDateTimeString()]
            );
        } catch (\Throwable $e) {
            Log::warning('QuezadaService::verificadosEnRango falló', [
                'error' => $e->getMessage(),
                'desde' => $desde->toDateTimeString(),
                'hasta' => $hasta->toDateTimeString(),
            ]);
            return [];
        }
    }

    /** Pedidos verificados HOY (00:00 hasta ahora) en zona México. */
    public function verificadosHoy(): array
    {
        return $this->verificadosEnRango(
            Carbon::today('America/Mexico_City'),
            Carbon::now('America/Mexico_City')
        );
    }

    /**
     * Datos de un folio específico verificado en QZ.
     * Devuelve null si no existe o si QZ está caído.
     */
    public function obtenerPorFolio(string $folio): ?object
    {
        try {
            $row = DB::connection('quezada')->selectOne(
                "SELECT
                    cs.documento     AS folio,
                    cs.verificador   AS verificador,
                    cs.fechatermino  AS fechatermino,
                    cs.verificadas   AS piezas,
                    cs.observaciones AS observaciones,
                    cs.referencia    AS referencia,
                    p.cliente        AS cliente_clave,
                    p.nombrecliente  AS cliente_nombre
                FROM contenedorsurtido cs
                LEFT JOIN pedido p ON p.flpedido = cs.flpedido
                WHERE cs.documento = ?
                  AND cs.status = 'FA'
                LIMIT 1",
                [$folio]
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            Log::warning('QuezadaService::obtenerPorFolio falló', [
                'folio' => $folio,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /** Health check rápido: ¿QZ responde? */
    public function disponible(): bool
    {
        try {
            DB::connection('quezada')->select('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
