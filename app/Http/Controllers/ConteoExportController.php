<?php

namespace App\Http\Controllers;

// ¡IMPORTANTE! Importamos la clase Request
use Illuminate\Http\Request;
use App\Exports\ConteoExport;
use Maatwebsite\Excel\Facades\Excel;

class ConteoExportController extends Controller
{
    /**
     * (NUEVO) Muestra el formulario para ingresar el número de conteo.
     */
    public function showForm()
    {
        // Esta función simplemente carga y devuelve el archivo
        // resources/views/reporte/form.blade.php
        return view('conteo.form');
    }

    public function exportar(Request $request)
    {
        $fecha = \Carbon::now()->format('Y-m-d');
        extract($request->all());

        // 1. Consumir APIs
        try {
            // Configuración base del cliente HTTP
            $client = \Http::timeout(120)          // hasta 120s
                ->retry(3, 2000)     // 3 intentos, 2s de espera entre ellos
                ->withoutVerifying(); // DESCOMENTA si tienes certificado self-signed y da problemas de SSL

            $stock1Response = $client->get('https://pedidos.owari.com.mx/STOCK1DURO.json');
            $stock3Response = $client->get('https://pedidos.owari.com.mx/STOCK3DURO.json');

            $conteosResponse = $client->get('https://sistemasowari.com:8443/catalowari/api/conteos', [
                'conteo' => $conteo,
            ]);

            if ($stock1Response->failed() || $stock3Response->failed() || $conteosResponse->failed()) {
                throw new \RuntimeException('Alguna de las APIs respondió con error HTTP');
            }

            $stock1 = collect($stock1Response->json());
            $stock3 = collect($stock3Response->json());
            $conteos = collect($conteosResponse->json());

        } catch (RequestException $e) {
            // Error del cliente HTTP, incl. timeout
            abort(500, 'No se pudo obtener información del servicio de stock/conteos: ' . $e->getMessage());
        } catch (\Throwable $e) {
            abort(500, 'Error inesperado al consumir las APIs: ' . $e->getMessage());
        }

        // 2. Indexar por clave/código
        $stock1ByClave = $stock1->keyBy('CLAVE_ARTICULO');
        $stock3ByClave = $stock3->keyBy('CLAVE_ARTICULO');
        $conteosByCodigo = $conteos->keyBy('codigo');

        // 3. Unir todas las claves
        $allClaves = $stock1ByClave->keys()
            ->merge($stock3ByClave->keys())
            ->merge($conteosByCodigo->keys())
            ->unique()
            ->sort()
            ->values();

        $rows = [];
        $meta = [];

        foreach ($allClaves as $index => $clave) {
            $e1 = $stock1ByClave->get($clave);
            $e3 = $stock3ByClave->get($clave);
            $c = $conteosByCodigo->get($clave);

            $e1Stock = $e1 ? (float) $e1['STOCK'] : 0;
            $e1UltCosto = $e1 ? (float) $e1['ULT_COSTO'] : 0;

            $e3Stock = $e3 ? (float) $e3['STOCK'] : 0;
            $e3UltCosto = $e3 ? (float) $e3['ULT_COSTO'] : 0;

            $stockFinal = $e1Stock + $e3Stock;

            $cant1 = $c['cant_1er'] ?? 0;
            $cant2 = $c['cant_2do'] ?? 0;
            $cant3 = $c['cant_3er'] ?? 0;

            $ubic1 = $c['ubicaciones_1er'] ?? null;
            $ubic2 = $c['ubicaciones_2do'] ?? null;
            $ubic3 = $c['ubicaciones_3er'] ?? null;

            $diff1 = ($stockFinal - $cant1) * -1;
            $diff2 = ($stockFinal - $cant2) * -1;
            $diff3 = ($stockFinal - $cant3) * -1;

            if ($e1 && !$e3) {
                $rowType = 'only_e1';
            } elseif (!$e1 && $e3) {
                $rowType = 'only_e3';
            } else {
                $rowType = 'both_or_none';
            }

            // helper para que los ceros se vean como "0"
            $z = function ($v) {
                return ($v === 0 || $v === 0.0) ? '0' : $v;
            };

            $rows[] = [
                $clave,
                $z($e1Stock),
                $z($e1UltCosto),
                $z($e3Stock),
                $z($e3UltCosto),
                $z($stockFinal),
                $z($cant1),
                $ubic1,
                $z($diff1),
                $z($cant2),
                $ubic2,
                $z($diff2),
                $z($cant3),
                $ubic3,
                $z($diff3),
            ];

            $meta[] = [
                'row_type' => $rowType,
                'diff1' => $diff1,
                'diff2' => $diff2,
                'diff3' => $diff3,
            ];
        }

        $fileName = "inventario_conteo_{$conteo}_{$fecha}.xlsx";

        return Excel::download(new ConteoExport($rows, $meta, $fecha), $fileName);
    }
}