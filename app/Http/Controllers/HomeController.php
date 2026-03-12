<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Writer;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function reporteInventario(Request $r)
    {
        extract($r->all());
        $url = 'https://sistemasowari.com:8443/catalowari/api/reporte-inventario';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $existencias = ['' => ['CLAVE', 'SAE 1', 'PEDIDOS 1', 'ROJOS 1', 'STOCK 1', 'SAE 3', 'PEDIDOS 3', 'ROJOS 3', 'STOCK 3', 'TOTAL']] + json_decode($data, true);
        //dd($existencias);

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // Insertar los datos en el CSV
        $csv->insertAll($existencias);

        // Configurar las cabeceras HTTP para forzar la descarga
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="datos.csv"',
        ];

        // Retornar la respuesta con el archivo CSV
        return Response::make($csv->toString(), 200, $headers);


    }

    public function reporteNegados(Request $r)
    {
        extract($r->all());
        $url = 'https://sistemasowari.com:8443/catalowari/api/negados';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $json = curl_exec($ch);
        if ($json === false) {
            throw new \RuntimeException('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        // Para pruebas locales, comenta lo de arriba y usa el archivo:
        // $json = file_get_contents(__DIR__ . '/negados.json');

        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            throw new \RuntimeException('JSON inválido');
        }

        // Normaliza
        foreach ($rows as &$r) {
            $r['CVE_ART'] = trim((string) ($r['CVE_ART'] ?? ''));
            $r['CANT_FALTANTE'] = (float) preg_replace('/[^\d\.\-]/', '', (string) ($r['CANT_FALTANTE'] ?? 0));
        }
        unset($r);

        // Agrupa y suma
        $groups = [];
        foreach ($rows as $row) {
            $art = $row['CVE_ART'];
            if ($art === '')
                continue;
            if (!isset($groups[$art]))
                $groups[$art] = ['sum' => 0.0, 'rows' => []];
            $groups[$art]['sum'] += $row['CANT_FALTANTE'];
            $row['ROW_TYPE'] = 'row';
            $groups[$art]['rows'][] = $row;
        }

        // Orden por FALTANTE total DESC
        $arts = array_keys($groups);
        usort($arts, function ($a, $b) use ($groups) {
            $d = $groups[$b]['sum'] <=> $groups[$a]['sum']; // descendente
            return $d !== 0 ? $d : ($a <=> $b);             // desempate por CVE_ART
        });

        // Construye salida intercalada + totales ordenados
        $intercalado = [['PEDIDO', 'FECHA PEDIDO', 'PARTIDA', 'CLAVE', 'SOLICITADO', 'FACTURADO', 'NEGADO', 'ESTATUS', 'FACTURA', 'FECHA FACTURA', 'ULTIMA FECHA FACTURA', '--']];
        $totales = [];
        foreach ($arts as $art) {
            foreach ($groups[$art]['rows'] as $r)
                $intercalado[] = $r;
            $intercalado[] = [
                'ROW_TYPE' => 'subtotal',
                'CVE_ART' => $art,
                'FALTANTE_TOTAL' => $groups[$art]['sum'],
            ];
            $totales[$art] = $groups[$art]['sum'];
        }
        // Si quieres $totales también ordenado como lista:
        $totales_orden = [];
        foreach ($arts as $art) {
            $totales_orden[] = ['CVE_ART' => $art, 'FALTANTE_TOTAL' => $groups[$art]['sum']];
        }


        // Ejemplos de uso
# print_r($totales_orden);
//foreach ($intercalado as $r) echo json_encode($r, JSON_UNESCAPED_UNICODE).PHP_EOL;



        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // Insertar los datos en el CSV
        $csv->insertAll($intercalado);

        // Configurar las cabeceras HTTP para forzar la descarga
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="datos.csv"',
        ];

        // Retornar la respuesta con el archivo CSV
        return Response::make($csv->toString(), 200, $headers);


    }

    public function demoFactura(Request $request)
    {


        $clave_cliente = "L042M";
        $url = 'https://sistemasowari.com:8443/catalowari/api/pedidos_cliente?' . http_build_query(["clave" => $clave_cliente, "pedidos" => ['1W1011', '1W1012']]);

        //dd($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $pedidos = json_decode($data, true);



        dd($pedidos);

        /*$data = 'https://tu-url.com/folio/ABC123';
        $qr = base64_encode(
            QrCode::format('png')->generate($data)
        );
        $partidas = [];
        $pdf = PDF::loadView('pdf.factura',compact('partidas','qr'));
        return $pdf->stream();*/

    }

    public function reporteLargaVenta(Request $request)
    {
        $url = 'https://sistemasowari.com:8443/catalowari/api/larga-venta';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $json = curl_exec($ch);
        if ($json === false) {
            throw new \RuntimeException('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        // Para pruebas locales, comenta lo de arriba y usa el archivo:
        // $json = file_get_contents(__DIR__ . '/negados.json');

        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            throw new \RuntimeException('JSON inválido');
        }
        return view('productos.larga_venta', compact('rows'));

    }

}
