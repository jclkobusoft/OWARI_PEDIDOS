<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Empacador;
use App\Models\Etiqueta;

class EtiquetasController extends Controller
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
        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sistemasowari.com:8443/catalowari/api/todos_productos'); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        $data = curl_exec($ch); 
        curl_close($ch); 
        $data = json_decode($data);*/
        if(!\Auth::user()->can('etiquetas_producto'))
            abort(403, 'No tienes autorizacion');

        return view('etiquetas.crear');
    }

    public function pdf(Request $r){
        $data = $r->all();
        $customPaper = array(0,0,72,144);
        $pdf = Pdf::loadView('etiquetas.moldes.etiqueta_producto', $data)->setPaper($customPaper, 'landscape')->setWarnings(false)->save('etiquetas/etiqueta.pdf');
        return json_encode([
            'archivo' => '/etiquetas/etiqueta.pdf'
        ]);
    }

    public function verPdf(Request $r){
            extract($r->all());
            $file = \File::get($archivo);
            $type = \File::mimeType($archivo);
            
            $response = \Response::make($file, 200);
            $response->header("Content-Type", $type);
            
            return $response;
    }

    public function crearPaquetes(){
        if(!\Auth::user()->can('etiquetas_paquetes'))
            abort(403, 'No tienes autorizacion');
        $empacadores = Empacador::orderBy('iniciales')->get();
        return view('etiquetas.crear_paquetes',compact('empacadores'));
    }

    public function pdfPaquetes(Request $r){
        extract($r->all());

        $total_etiquetas = $cajas + $atados + $bolsas;
    
        $pedazos_pedido = explode("--", $pedido);
        $pedazos_nombre_cliente = explode("--", $nombre_cliente);

        $etiqueta = Etiqueta::where('pedido',trim($pedazos_pedido[1]))->where('cliente',$cliente)->first();

        if($etiqueta)
            return json_encode([
            'code' => 0,
            'mensaje' => 'Ya se generaron etiquetas de este pedido.'
        ]);


        $data = [
            'pedido' => trim($pedazos_pedido[1]),
            'cliente' => $cliente,
            'nombre_cliente' =>  trim($pedazos_nombre_cliente[1]),
            'empaca' => $empaca,
            'total_etiquetas' =>  $total_etiquetas,
            'etiquetas' => [
                'caja' => $cajas,
                'atado' => $atados,
                'bolsa' => $bolsas
            ]
        ];

        Etiqueta::create(['pedido' => $data['pedido'],'cliente' => $data['cliente'], 'data' =>  strval(json_encode($data))]);
        
    
        $customPaper = array(0,0,180,216);
        $pdf = Pdf::loadView('etiquetas.moldes.etiqueta_paquetes', $data)->setPaper($customPaper, 'landscape')->setWarnings(false)->save('etiquetas/etiqueta_paquetes.pdf');
        return json_encode([
            'code' => 1,
            'archivo' => '/etiquetas/etiqueta_paquetes.pdf'
        ]);
    }

     public function crearCompra(){
        if(!\Auth::user()->can('etiquetas_compras'))
            abort(403, 'No tienes autorizacion');
       return view('etiquetas.crear_compra');
    
    }

    public function pdfCompra(Request $r){
        $data = $r->all();
        $customPaper = array(0,0,72,144);
        $pdf = Pdf::loadView('etiquetas.moldes.etiqueta_compra', $data)->setPaper($customPaper, 'landscape')->setWarnings(false)->save('etiquetas/etiqueta_compra.pdf');
        return json_encode([
            'archivo' => '/etiquetas/etiqueta_compra.pdf'
        ]);

    }

   
}
