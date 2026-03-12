<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoPendiente;
use App\Models\ProductoBusqueda;
use App\Models\Registrado;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PedidoPendienteExport;
use Maatwebsite\Excel\Facades\Excel;

use App\DataTables\PedidosPendientesDataTable;

class PedidosPendientesController extends Controller
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

    public function index(PedidosPendientesDataTable $dataTable){
         /*if(!\Auth::user()->can('usuarios_eliminar'))
            abort(403, 'No tienes autorizacion');*/
         return $dataTable->render('pedidos_pendientes.index');
    }

     public function ver($pedido){
        /*if(!\Auth::user()->can('usuarios_editar'))
            abort(403, 'No tienes autorizacion');*/
        $pedido = PedidoPendiente::find($pedido);
        $partidas = json_decode($pedido->partidas);
        $partidas_especiales = json_decode($pedido->partidas_especiales);
        $registrado = Registrado::where('id_usuario',$pedido->id_usuario)->first();

       
        return view('pedidos_pendientes.editar',compact('pedido','partidas','partidas_especiales','registrado'));
    }

    public function eliminar(Request $r){
         /*if(!\Auth::user()->can('usuarios_eliminar'))
            abort(403, 'No tienes autorizacion');*/

        extract($r->all());
        $pedido=PedidoPendiente::find($pedido);
        $pedido->delete();

        return json_encode([
            'code' => 1,
            'mensaje' => 'El pedido fue eliminado correctamente'
        ]);
    }


}
