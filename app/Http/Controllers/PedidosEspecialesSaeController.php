<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialPartida;
use App\Models\PedidoEspecialSae;
use App\Models\PedidoEspecialPartidaSae;
use App\Models\ProductoBusqueda;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PedidoEspecialPartidasExport;
use Maatwebsite\Excel\Facades\Excel;

class PedidosEspecialesSaeController extends Controller
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


    public function test(){

        return view('test.partidas');

    }


    public function guardarPedidoEspecialSae(Request $request)
    {
        extract($request->all());

       $data = [
           'cliente' => $cliente,
           'gran_total' => 0.00,
           'cadena_original' => strval(json_encode($request->all())),
           'capturo' => \Auth::user()->id,
           'pedido_sae' => $pedido_sae,
           'id_pedido_especial' => $pedido_especial
       ];

       

      
       $pedido = PedidoEspecialSae::create($data);

       $subtotal = 0;
       $iva = 0;
       $gran_total = 0;

       foreach ($partidas as $key => $value) {
           // code...
            $data = [
                'id_pedido' => $pedido->id,
                'clave' => $value['codigo'],
                'precio_unitario' => $value['precio'],
                'cantidad' => $value['cantidad'],
                'gran_total' => $value['total']
            ];

            PedidoEspecialPartidaSae::create($data);
            $partida_del_especial = PedidoEspecialPartida::where('id_pedido',$pedido_especial)->where('clave',$value['codigo'])->first();
            $partida_del_especial->fill(['surtido' => $partida_del_especial->surtido + $value['cantidad']])->save();


            $subtotal += ($value['cantidad'] * $value['precio_iva']);
            $iva += $value['cantidad'] * ($value['precio'] - $value['precio_iva']);
            $gran_total += ($value['cantidad'] * $value['precio']);

       }

       $pedido->fill([
        'subtotal'=> $subtotal,
        'iva'=> $iva,
        'gran_total'=> $gran_total,
       ])->save();


        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id
        ]);

    }


}
