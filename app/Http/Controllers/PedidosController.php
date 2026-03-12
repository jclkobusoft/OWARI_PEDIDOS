<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialPartida;
use App\Models\ProductoBusqueda;
use App\Models\PedidoPendiente;
use App\Models\Registrado;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PedidoEspecialPartidasExport;
use App\Exports\PedidoPendienteExport;
use Maatwebsite\Excel\Facades\Excel;

class PedidosController extends Controller
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

    public function crear()
    {
        if(!\Auth::user()->can('pedidos_crear'))
            abort(403, 'No tienes autorizacion');
        return view('pedidos.crear');
    }

    public function demo()
    {
        return view('pedidos.demo');
    }

    public function guardar(Request $request)
    {
        extract($request->all());
        $partidas_uno = [];
        $partidas_tres = [];
        $pedido = Pedido::create([
            'entrada' => strval(json_encode($request->all()))
        ]);
        foreach ($partidas as $key => $value) {
            foreach ($partidas_detalle as $llave => $valor) {
            	if(!isset($valor['clave']))
            	   continue;
            	
                if (trim($value['codigo']) == trim($valor['clave'])) {

                    if(!isset($valor['existencia_remision']) || !isset($valor['existencia_factura']))
                       continue;
                    if(isset($tipo)){
                        if($tipo == 'normal'){
                            if ($valor['existencia_remision'] >= 0) {
                                array_push($partidas_tres, [
                                    'almacen' => $valor['almacen'],
                                    'clave' => $valor['clave'],
                                    'cantidad' => $value['cantidad'],
                                    'precio' => $value['precio'],
                                    'total' => $value['total'],
                                ]);
                            } elseif ($valor['existencia_factura'] > 0 && $value['cantidad'] <= $valor['existencia_factura']) {
                                array_push($partidas_uno, [
                                    'almacen' => $valor['almacen'],
                                    'clave' => $valor['clave'],
                                    'cantidad' => $value['cantidad'],
                                    'precio' => $value['precio_iva'],
                                    'total' => $value['total'],
                                ]);
                            }

                            break;
                        }

                        if($tipo == 'factura'){
                            array_push($partidas_uno, [
                                'almacen' => $valor['almacen'],
                                'clave' => $valor['clave'],
                                'cantidad' => $value['cantidad'],
                                'precio' => $value['precio_iva'],
                                'total' => $value['total'],
                            ]);
                            break;
                        }
                    }
                    else{

                         if ($valor['existencia_remision'] >= 0) {
                                array_push($partidas_tres, [
                                    'almacen' => $valor['almacen'],
                                    'clave' => $valor['clave'],
                                    'cantidad' => $value['cantidad'],
                                    'precio' => $value['precio'],
                                    'total' => $value['total'],
                                ]);
                            } elseif ($valor['existencia_factura'] > 0 && $value['cantidad'] <= $valor['existencia_factura']) {
                                array_push($partidas_uno, [
                                    'almacen' => $valor['almacen'],
                                    'clave' => $valor['clave'],
                                    'cantidad' => $value['cantidad'],
                                    'precio' => $value['precio_iva'],
                                    'total' => $value['total'],
                                ]);
                            }

                            break;
                    }                   
                }
            }
        }


        array_multisort(array_column($partidas_uno, 'almacen'), SORT_DESC, $partidas_uno);
        array_multisort(array_column($partidas_tres, 'almacen'), SORT_DESC, $partidas_tres);
       



        $pedido->fill([
            'partidas_a' => strval(json_encode($partidas_uno)),
            'partidas_b' => strval(json_encode($partidas_tres))
        ])->save();

        

        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id,
            'partidas_a' => $partidas_uno,
            'partidas_b' => $partidas_tres
        ]);

    }

    public function guardarPedidoEspecial(Request $r){
        extract($r->all());
        
        if(is_array($cliente))
            $clave_cliente = $cliente['clave'];
        else
            $clave_cliente = $cliente;

        $url = 'https://sistemasowari.com:8443/catalowari/api/datos_cliente?' . http_build_query(["clave" =>  $clave_cliente]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $info_cliente = json_decode($data,true);

        $data = [
            'cliente' => $clave_cliente,
            'gran_total' => floatval("0.00"),
            'cadena_original' => strval(json_encode($r->all())),
            'capturo' => \Auth::user()->id
        ];
        $pedido = PedidoEspecial::create($data);

        $elaboro = \Auth::user()->name;
        if(\Auth::user()->cliente)
            $elaboro = \Auth::user()->clave_cliente." ".$elaboro;


        $gran_total = 0;
        $arreglo = [[
            'CLIENTE',
            'PEDIDO ESPECIAL',
            'CLAVE',
            'CANTIDAD',
            'CLAVE PROVEEDOR',
            'PROVEEDOR',
            'PRECIO UNITARIO',
            'TOTAL',
            'SAE',
            'ELABORO'
        ]];
        foreach ($partidas as $key => $value) {
            // code...
            $producto = ProductoBusqueda::where('codigo_nikko',$value['codigo'])->first();

            $data = [
                'id_pedido' => $pedido->id,
                'clave' => $value['codigo'],
                'precio_unitario' => floatval($value['precio']),
                'cantidad' => floatval($value['cantidad']),
                'gran_total' => floatval($value['total'])
            ];
            array_push($arreglo,[
                'cliente' => $clave_cliente,
                'id_pedido' => $pedido->id,
                'clave' => $value['codigo'],
                'cantidad' => floatval($value['cantidad']),
                'clave_proveedor' => $producto ? $producto->clave_producto_proveedor : 'SIN CLAVE' ,
                'proveedor' => $producto ? $producto->proveedor : 'Desconocido',
                'precio_unitario' => floatval($value['precio']),
                'gran_total' => floatval($value['total']),
                'sae' => $value['sae'],
                'elaboro' => $elaboro
            ]);
            PedidoEspecialPartida::create($data);
            $gran_total+=$value['total'];
        }

        $pedido->fill(['gran_total' => floatval($gran_total)])->save();

        $archivo = date('YmdHis').".xlsx";
        $archivo_excel = "pedidos_especiales/".$archivo;
       

        $export = new PedidoEspecialPartidasExport($arreglo);
        Excel::store($export, $archivo_excel);


        
         \Mail::send('emails.pedido_especial', compact('pedido','info_cliente'), function ($message) use ($pedido,$archivo){
                $message->from('pedido_especial@owari.com.mx', 'Pedido Especial');
                $message->subject("Pedido especial ".$pedido->id);
                $message->attach(storage_path()."/app/pedidos_especiales/".$archivo);
                $message->to(['direccion@owari.com.mx','ventas2@owari.com.mx','ventas3@owari.com.mx','compras@owari.com.mx']);                
            });

         
            \Session::put('cartEspecial', []);
         

        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id,
            'mensaje' => 'Correo enviado correctamente'
        ]);


    }

    public function guardarPedidoPendienteWeb(Request $request){
        extract($request->all());
        $registrado = Registrado::where('id_usuario',\Auth::user()->id)->first();

        $data = [
            'cliente' => $registrado->nombre,
            'gran_total' => $gran_total,
            'partidas' => strval(json_encode($partidas)),
            'partidas_detalle' => strval(json_encode($partidas_detalle)),
            'estado' => 'original',
            'telefono' => $registrado->telefono,
            'email' => $registrado->email,
            'partidas_especiales' => strval(json_encode($partidas_especiales)),
            'partidas_especiales_detalle' =>  strval(json_encode($partidas_especiales_detalle)),
            'fecha_recoge' => str_replace("T"," ",$fecha_recoge),
            'metodo_pago' => $metodo_pago,
            'forma_pago' => $forma_pago,
            'uso_cfdi' => $uso_cfdi,
            'id_usuario' => \Auth::user()->id
        ];

        $pedido_pendiente = PedidoPendiente::create($data);


        $archivo = 'Pendiente_'.$pedido_pendiente->id.'_'.date('YmdHis').".xlsx";
        $archivo_excel = "pedidos_pendientes/".$archivo;

        $encabezados = [[
            'CLAVE',
            'DESCRIPCION',
            'CANTIDAD',
            'PRECIO UNITARIO',
            'SIN IVA',
            'TOTAL',
            'SAE',
            'TIPO'
        ]];

        foreach($partidas as $key => $value){
            $partidas[$key]['sae'] = '';
            $partidas[$key]['tipo'] = 'BODEGA';
        }

        foreach($partidas_especiales as $key => $value){
            $partidas_especiales[$key]['tipo'] = 'ESPECIAL';
        }
       
        $arreglo = array_merge($encabezados, $partidas, $partidas_especiales);


        $export = new PedidoPendienteExport($arreglo);
        Excel::store($export, $archivo_excel);

         \Mail::send('emails.pedido_pendiente', compact('pedido_pendiente','registrado'), function ($message) use ($pedido_pendiente,$archivo){
                $message->from('pedido_especial@owari.com.mx', 'Pedido Pendiente');
                $message->subject("IGNORAME ESTAMOS EN PRUEBAS Pedido cliente nuevo ".$pedido_pendiente->id);
                $message->attach(storage_path()."/app/pedidos_pendientes/".$archivo);
                $message->to(['direccion@owari.com.mx','ventas2@owari.com.mx','ventas3@owari.com.mx','compras@owari.com.mx']);                
            });


        return response()->json([
            'code' => 1,
            'id_pedido' => $pedido_pendiente->id
        ]);
        
    }


}
