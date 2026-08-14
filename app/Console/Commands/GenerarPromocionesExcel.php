<?php

namespace App\Console\Commands;

use App\Exports\PromocionesExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Genera el Excel de promociones globales de la tienda en linea, que el cliente
 * descarga desde /tienda_online/promociones.xlsx.
 *
 * Fuentes:
 *  - Precios y vigencia: SAE, via el externo /api/promociones-global (solo
 *    promociones de publico general, precios CON IVA).
 *  - Catalogo (marca, subgrupo, descripcion): SOMA (owari_soma).
 *
 * El archivo se guarda en storage/app/promociones.xlsx y la ruta lo entrega.
 *
 * Uso:  php artisan promociones:generar-excel
 * Cron: todas las noches a las 02:00 (App\Console\Kernel::schedule + Plesk).
 */
class GenerarPromocionesExcel extends Command
{
    protected $signature   = 'promociones:generar-excel';
    protected $description  = 'Genera el Excel diario de promociones globales de la tienda';

    // SOMA es la fuente: mismas llaves que el endpoint historico de SAE, pero
    // calculado con las politicas/listas de SOMA (precio publico con IVA).
    private const URL_EXTERNO = 'https://owari.appsoma.online/somma/v2.0/api/promociones-global';
    private const ARCHIVO     = 'promociones.xlsx';   // en el disco 'local' -> storage/app/

    public function handle(): int
    {
        // 1) Promociones (precios + vigencia) desde SAE via externo.
        $ch = curl_init(self::URL_EXTERNO);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT        => 120,
        ]);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            $this->error("No se pudo obtener promociones del externo (HTTP {$status}) {$err}");
            Log::warning('promociones:generar-excel externo fallo', ['status' => $status, 'error' => $err]);
            return self::FAILURE;
        }

        $data  = json_decode($body, true);
        $items = (is_array($data) && isset($data['productos']) && is_array($data['productos'])) ? $data['productos'] : [];

        // clave => {precio_normal, precio_promo, vigencia}
        $porClave = [];
        foreach ($items as $it) {
            if (!is_array($it) || empty($it['clave'])) continue;
            $porClave[$it['clave']] = $it;
        }
        $claves = array_keys($porClave);

        // 2) Catalogo desde SOMA (marca, subgrupo, descripcion) para esas claves.
        $catalogo = collect();
        if (!empty($claves)) {
            $catalogo = DB::connection('owari_soma')->table('productos as p')
                ->leftJoin('productos_web as pw', function ($j) {
                    $j->on('pw.id_producto', '=', 'p.id')->whereNull('pw.deleted_at');
                })
                ->leftJoin('marcas as m', function ($j) {
                    $j->on('m.id', '=', 'p.id_marca')->whereNull('m.deleted_at');
                })
                ->whereIn('p.clave', $claves)
                ->whereNull('p.deleted_at')
                ->get([
                    'p.clave',
                    'm.nombre as marca',
                    'pw.subgrupo',
                    'pw.descripcion_1', 'pw.descripcion_2', 'pw.descripcion_3',
                ])
                ->keyBy('clave');
        }

        // 3) Armar filas (encabezado + una por producto), ordenadas por subgrupo/clave.
        $filas = [[
            'CLAVE', 'DESCRIPCION', 'PRECIO PROMOCION', 'MINIMO DE COMPRA', 'VIGENCIA', 'MARCA', 'SUBGRUPO',
        ]];

        $rows = [];
        foreach ($porClave as $clave => $it) {
            $cat = $catalogo->get($clave);

            // SAE devuelve miles de claves promocionales que no son productos
            // vendibles de la tienda (paquetes, catalogos, baleros de importacion,
            // claves tipo PROMOCIONAL/DESCUENTO, etc.). Igual que la pagina de
            // descuentos, solo incluimos las que existen como producto en SOMA;
            // asi el Excel coincide con lo que ve el cliente.
            if ($cat === null) continue;

            $desc = trim(implode(' ', array_filter([
                $cat->descripcion_1 ?? null,
                $cat->descripcion_2 ?? null,
                $cat->descripcion_3 ?? null,
            ])));

            $rows[] = [
                'clave'         => $clave,
                'descripcion'   => $desc,
                'precio_promo'  => round((float) ($it['precio_promo'] ?? 0), 2),
                // VOL_MIN de la politica; si no trae minimo, es 1 unidad.
                'minimo'        => max(1, (int) ($it['minimo'] ?? 0)),
                'vigencia'      => !empty($it['vigencia'])
                    ? Carbon::parse($it['vigencia'])->format('d/m/Y')
                    : 'Sin vencimiento',
                'marca'         => $cat->marca ?? '',
                'subgrupo'      => $cat->subgrupo ?? '',
            ];
        }

        usort($rows, function ($a, $b) {
            return [$a['subgrupo'], $a['clave']] <=> [$b['subgrupo'], $b['clave']];
        });

        foreach ($rows as $r) {
            $filas[] = array_values($r);
        }

        // 4) Guardar el Excel (disco local -> storage/app/promociones.xlsx).
        Excel::store(new PromocionesExport($filas), self::ARCHIVO);

        $total = count($rows);
        Log::info('promociones:generar-excel', ['productos' => $total]);
        $this->info("Excel de promociones generado con {$total} productos.");

        return self::SUCCESS;
    }
}
