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


}
