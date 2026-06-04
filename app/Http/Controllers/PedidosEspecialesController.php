<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialSae;
use App\Models\PedidoEspecialPartida;
use App\Models\ProductoBusqueda;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PedidoEspecialPartidasExport;
use Maatwebsite\Excel\Facades\Excel;
use League\Csv\Writer;
use Illuminate\Support\Facades\Response;

use App\DataTables\PedidosEspecialesDataTable;

class PedidosEspecialesController extends Controller
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

    public function index(PedidosEspecialesDataTable $dataTable){
         /*if(!\Auth::user()->can('usuarios_eliminar'))
            abort(403, 'No tienes autorizacion');*/
         return $dataTable->render('pedidos_especiales.index');
    }

     public function ver($pedido){
        /*if(!\Auth::user()->can('usuarios_editar'))
            abort(403, 'No tienes autorizacion');*/


        $pedido = PedidoEspecial::find($pedido);
        $pedidos_sae = PedidoEspecialSae::where('id_pedido_especial',$pedido->id)->get();
        return view('pedidos_especiales.editar',compact('pedido','pedidos_sae'));
    }

    public function eliminar(Request $r){
         /*if(!\Auth::user()->can('usuarios_eliminar'))
            abort(403, 'No tienes autorizacion');*/

        extract($r->all());
        $pedido_especial=PedidoEspecial::find($pedido);
        $pedido_especial->delete();

        return json_encode([
            'code' => 1,
            'mensaje' => 'El pedido fue eliminado correctamente'
        ]);
    }

    /**
     * Genera un CSV con las partidas de pedidos especiales que quedaron
     * PENDIENTES de surtir (cantidad - surtido > 0) dentro del mes seleccionado.
     *
     * - "cantidad" es lo que pidio el cliente; "surtido" es lo que ya llego del
     *   proveedor y se le vendio. Pendiente = cantidad - surtido.
     * - El mes se filtra sobre pedidos_especiales.created_at (cuando se levanto
     *   el pedido especial).
     * - Solo se usa la informacion que vive en pedidos_especiales(_partidas):
     *   la clave del producto, el cliente, las cantidades. No se cruza con el
     *   CMS para traer descripciones.
     *
     * GET /pedidos_especiales/reporte_pendientes?mes=YYYY-MM
     */
    public function reportePendientes(Request $request)
    {
        // Mes en formato YYYY-MM (input type="month"); default al mes actual.
        $mes = $request->input('mes', now()->format('Y-m'));
        try {
            $inicio = \Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        } catch (\Throwable $e) {
            $inicio = now()->startOfMonth();
        }
        $fin = $inicio->copy()->endOfMonth();

        // Partidas pendientes del mes. El SoftDeletes global de
        // PedidoEspecialPartida ya excluye partidas borradas; agregamos el
        // filtro explicito de pedidos_especiales.deleted_at por el join crudo.
        $partidas = PedidoEspecialPartida::query()
            ->join('pedidos_especiales', 'pedidos_especiales_partidas.id_pedido', '=', 'pedidos_especiales.id')
            ->whereNull('pedidos_especiales.deleted_at')
            ->whereBetween('pedidos_especiales.created_at', [$inicio, $fin])
            ->whereRaw('pedidos_especiales_partidas.cantidad - COALESCE(pedidos_especiales_partidas.surtido, 0) > 0')
            ->orderBy('pedidos_especiales.cliente')
            ->orderBy('pedidos_especiales_partidas.clave')
            ->get([
                'pedidos_especiales_partidas.clave',
                'pedidos_especiales_partidas.cantidad',
                'pedidos_especiales_partidas.surtido',
                'pedidos_especiales.cliente',
                'pedidos_especiales.clave_proveedor',
                'pedidos_especiales.id as id_pedido_especial',
                'pedidos_especiales.created_at',
            ]);

        // Armado del CSV (primera fila = encabezados).
        $filas = [[
            'PEDIDO ESPECIAL', 'FECHA', 'CLIENTE', 'CLAVE',
            'CLAVE PROVEEDOR', 'SOLICITADO', 'SURTIDO', 'PENDIENTE',
        ]];

        foreach ($partidas as $p) {
            $solicitado = (float) $p->cantidad;
            $surtido    = (float) ($p->surtido ?? 0);
            $pendiente  = $solicitado - $surtido;

            $filas[] = [
                $p->id_pedido_especial,
                \Carbon::parse($p->created_at)->format('d/m/Y'),
                $p->cliente,
                $p->clave,
                $p->clave_proveedor ?? '',
                $solicitado + 0,
                $surtido + 0,
                $pendiente + 0,
            ];
        }

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Writer::BOM_UTF8);   // acentos legibles en Excel
        $csv->insertAll($filas);

        $nombre = 'pedidos_especiales_pendientes_' . $inicio->format('Y_m') . '.csv';

        return Response::make($csv->toString(), 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }


}
