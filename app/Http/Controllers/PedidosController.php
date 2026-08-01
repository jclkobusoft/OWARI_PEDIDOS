<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialPartida;
use App\Models\PedidoSaePendiente;
use App\Models\PedidoWeb;
use App\Models\ProductoBusqueda;
use App\Models\PedidoPendiente;
use App\Models\Registrado;
use App\DataTables\PedidosSaePendientesDataTable;
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

    private function quitarAcentos($texto) {
        $buscar =    ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'];
        $reemplazar = ['a','e','i','o','u','A','E','I','O','U','n','N','u','U'];
        return str_replace($buscar, $reemplazar, $texto);
    }

    public function apiBusqueda(Request $request)
    {
        $q = $this->quitarAcentos($request->input('q', ''));
        if (empty($q)) return response()->json([]);

        if (stripos($q, ' ') !== false) {
            // Multi-palabra: ILIKE AND
            $palabras = array_filter(explode(" ", trim($q)));
            $where = [];
            $bindings = [];
            foreach ($palabras as $p) {
                $where[] = "pb.buscador ILIKE ?";
                $bindings[] = '%' . $p . '%';
            }
            $whereClause = implode(' AND ', $where);
        } else {
            $whereClause = "pb.buscador ILIKE ?";
            $bindings = ['%' . $q . '%'];
        }

        $resultados = \DB::connection('owari_soma')->select("
            SELECT
                m.nombre as marca_comercial,
                p.clave as codigo_nikko,
                pw.grupo,
                pw.subgrupo,
                pw.descripcion_1,
                COALESCE(ppr.precio, 0) as precio_normal,
                pb.buscador,
                p.id as producto_id,
                COALESCE(prov.clave, '') as clave_proveedor,
                -- Stock que tiene el proveedor externo (KIMS), sincronizado por SOMA.
                -- Va APARTE de la existencia de SAE: no se suma nunca, porque esa
                -- mercancia no es nuestra y su partida se va a pedido especial.
                COALESCE(CASE WHEN pse.estado = 'ok' THEN pse.existencia ELSE 0 END, 0) as stock_externo,
                (pse.id IS NOT NULL) as tiene_proveedor_externo
            FROM productos_busqueda pb
            INNER JOIN productos p ON pb.producto_id = p.id
            LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
            LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
            LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
            LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
            LEFT JOIN productos_stock_externo pse ON pse.id_producto = p.id AND pse.deleted_at IS NULL
            WHERE {$whereClause}
            ORDER BY COALESCE(p.prioridad, 0) DESC NULLS LAST
        ", $bindings);

        // Deduplicar por codigo_nikko
        $vistos = [];
        $unicos = [];
        foreach ($resultados as $r) {
            if (!isset($vistos[$r->codigo_nikko])) {
                $vistos[$r->codigo_nikko] = true;

                // Cargar equivalencias con marca para este producto
                $equivs = \DB::connection('owari_soma')->select("
                    SELECT pe.clave, COALESCE(m.nombre, '') as marca
                    FROM productos_equivalencias pe
                    LEFT JOIN marcas m ON pe.id_marca = m.id AND m.deleted_at IS NULL
                    WHERE pe.id_producto = ? AND pe.deleted_at IS NULL AND pe.id_marca IS NOT NULL AND pe.id_marca > 0
                    ORDER BY pe.id LIMIT 3
                ", [$r->producto_id]);

                $r->extra_clave_1 = $equivs[0]->clave ?? null;
                $r->extra_clave_2 = $equivs[1]->clave ?? null;
                $r->extra_clave_3 = $equivs[2]->clave ?? null;
                $r->disponibilidad = '';

                $unicos[] = $r;
            }
        }

        return response()->json(array_slice($unicos, 0, 250));
    }

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
        $partidas = $request->input('partidas', []);
        $partidas_detalle = $request->input('partidas_detalle', []);
        $partidas_uno = [];
        $partidas_tres = [];

        if (empty($partidas)) {
            return json_encode(['code' => 1, 'id_pedido' => 0, 'partidas_a' => [], 'partidas_b' => []]);
        }

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

        $clave_proveedor_especial = $r->input('clave_proveedor');
        $clave_proveedor_especial = ($clave_proveedor_especial && trim($clave_proveedor_especial) !== '') ? trim($clave_proveedor_especial) : null;

        $data = [
            'cliente' => $clave_cliente,
            'gran_total' => floatval("0.00"),
            'cadena_original' => strval(json_encode($r->all())),
            'capturo' => \Auth::user()->id,
            'clave_proveedor' => $clave_proveedor_especial,
        ];
        $pedido = PedidoEspecial::create($data);

        $elaboro = \Auth::user()->name;
        if(\Auth::user()->cliente)
            $elaboro = \Auth::user()->clave_cliente." ".$elaboro;


        $gran_total = 0;
        // Candidatas para la cola de envio agrupado de SOMA
        // (pedidos_especiales_partidas_envio). Se filtran mas abajo: las de
        // proveedores especiales con envio inmediato por correo NO se encolan.
        $filasEnvio = [];
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
            // La clave del proveedor debe ser la del PROVEEDOR PRINCIPAL del
            // producto (productos.id_proveedor en SOMA), no la primera fila que
            // aparezca en productos_proveedores. Por eso arrancamos desde
            // `productos` y unimos productos_proveedores solo con el proveedor
            // principal (pp.id_proveedor = p.id_proveedor). Si el principal no
            // tiene fila en productos_proveedores, clave_proveedor queda null y
            // el fallback de abajo pone 'SIN CLAVE', pero el nombre del
            // proveedor (prov.clave) igual se resuelve por p.id_proveedor.
            $provInfo = \DB::connection('owari_soma')->select("
                SELECT pp.clave_proveedor,
                       prov.clave as proveedor,
                       prov.id    as id_proveedor,
                       p.id       as id_producto,
                       TRIM(CONCAT_WS(' ', pw.descripcion_1, pw.descripcion_2, pw.descripcion_3)) as descripcion
                FROM productos p
                LEFT JOIN proveedores prov
                       ON prov.id = p.id_proveedor
                LEFT JOIN productos_proveedores pp
                       ON pp.id_producto  = p.id
                      AND pp.id_proveedor = p.id_proveedor
                      AND pp.deleted_at IS NULL
                LEFT JOIN productos_web pw
                       ON pw.id_producto = p.id
                      AND pw.deleted_at IS NULL
                WHERE p.clave = ? AND p.deleted_at IS NULL
                LIMIT 1
            ", [$value['codigo']]);
            $provData = $provInfo[0] ?? null;

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
                'clave_proveedor' => $provData ? $provData->clave_proveedor : 'SIN CLAVE',
                'proveedor' => $provData ? $provData->proveedor : 'Desconocido',
                'precio_unitario' => floatval($value['precio']),
                'gran_total' => floatval($value['total']),
                'sae' => $value['sae'] ?? '',
                'elaboro' => $elaboro
            ]);
            $partidaCreada = PedidoEspecialPartida::create($data);

            // Candidata a la cola de envio agrupado de SOMA. El proveedor es el
            // PRINCIPAL del producto (mismo criterio que el Excel). Si el
            // producto no tiene proveedor resuelto en SOMA se marca para que
            // sea visible allá en vez de perderse.
            $filasEnvio[] = [
                'clave_proveedor_sistema' => ($provData && !empty($provData->proveedor)) ? $provData->proveedor : 'SIN PROVEEDOR',
                'id_proveedor'            => $provData->id_proveedor ?? null,
                'clave_owari'             => $value['codigo'],
                'id_producto'             => $provData->id_producto ?? null,
                'clave_proveedor'         => $provData->clave_proveedor ?? null,
                'descripcion'             => ($provData && !empty($provData->descripcion))
                                                ? $provData->descripcion
                                                : ($value['descripcion'] ?? null),
                'cantidad_solicitada'     => floatval($value['cantidad']),
                'precio_unitario'         => floatval($value['precio']),
                'total'                   => floatval($value['total']),
                'id_partida_origen'       => $partidaCreada->id,
                'existencia_sae'          => $value['sae'] ?? null,
                'observaciones'           => ($provData && !empty($provData->proveedor))
                                                ? null
                                                : 'Producto sin proveedor principal en SOMA',
            ];

            $gran_total+=$value['total'];
        }

        $pedido->fill(['gran_total' => floatval($gran_total)])->save();

        // Encolar en SOMA las partidas que NO son de proveedores con envio
        // inmediato por correo (esas ya se mandan al generarse el pedido).
        // Cubre carrito y telemarketing: ambos entran por este mismo endpoint.
        $this->encolarPartidasEnvioSoma(
            $pedido,
            $filasEnvio,
            $r->has('carrito') ? 'carrito' : 'telemarketing',
            $clave_cliente,
            is_array($info_cliente) ? ($info_cliente['NOMBRE'] ?? null) : null,
            $elaboro
        );

        $archivo = date('YmdHis').".xlsx";
        $archivo_excel = "pedidos_especiales/".$archivo;


        $export = new PedidoEspecialPartidasExport($arreglo);
        Excel::store($export, $archivo_excel);



         $esSYD = $pedido->clave_proveedor == 'S227';
         $subjectMail = $esSYD ? ("Pedido especial SYD ".$pedido->id) : ("Pedido especial ".$pedido->id);
         $fromName = $esSYD ? 'Pedido Especial SYD' : 'Pedido Especial';

         \Mail::send('emails.pedido_especial', compact('pedido','info_cliente'), function ($message) use ($pedido,$archivo,$subjectMail,$fromName){
                $message->from('pedido_especial@owari.com.mx', $fromName);
                $message->subject($subjectMail);
                $message->attach(storage_path()."/app/pedidos_especiales/".$archivo);
                $message->to(['direccion@owari.com.mx','ventas2@owari.com.mx','ventas3@owari.com.mx','compras@owari.com.mx']);
            });

            // Envio ADICIONAL directo: si el proveedor especial tiene correo y
            // enviar_excel en SOMA, se le manda el Excel REDUCIDO en el momento
            // (no toca el correo de arriba). Va envuelto en try/catch para no
            // romper el pedido si el correo falla.
            $this->enviarExcelReducidoProveedor($pedido, $clave_proveedor_especial, $arreglo);

            // Al insertar un pedido especial siempre se limpia cartEspecial,
            // sin importar el proveedor (incluido SYD) ni si el pedido normal
            // logro insertarse o no. Asi evitamos que el cliente vea las
            // mismas partidas en una proxima visita y las vuelva a generar.
            // El carrito ahora vive en BD (carrito_items), ligado al cliente.
            (new \App\Services\CarritoService())->vaciarTipo('especial');


        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id,
            'mensaje' => 'Correo enviado correctamente'
        ]);


    }

    /**
     * Encola en SOMA (pedidos_especiales_partidas_envio) las partidas del
     * pedido especial para que SOMA las AGRUPE y las mande al proveedor en su
     * corrida periodica.
     *
     * Se EXCLUYE el pedido completo cuando su proveedor de grupo ya recibe el
     * correo de forma inmediata (proveedores_especiales con enviar_excel = true
     * y correo, p.ej. SYD/S227): ese pedido ya se envio al generarse, y
     * encolarlo provocaria un envio duplicado.
     *
     * El criterio es a nivel PEDIDO (no por partida) porque el correo inmediato
     * se manda por pedido especial completo segun su clave_proveedor de grupo:
     * o se enviaron todas sus partidas, o ninguna. Asi ninguna partida queda
     * sin correo y sin encolar.
     *
     * Aplica igual a carrito y telemarketing (ambos usan guardarPedidoEspecial).
     *
     * Defensivo: si SOMA no responde o la config no se puede leer, NO encola
     * nada (fail-closed, para no arriesgar duplicados) y solo deja un warning.
     * Nunca rompe el guardado del pedido.
     */
    private function encolarPartidasEnvioSoma($pedido, array $filas, string $sistemaOrigen, string $claveCliente, ?string $nombreCliente, string $elaboro): void
    {
        if (empty($filas)) return;

        // Proveedores con envio inmediato por correo = los que hay que excluir.
        // Mismo criterio que enviarExcelReducidoProveedor(), para que lo que se
        // manda al instante y lo que se agrupa nunca se traslapen.
        try {
            $cfg = \DB::connection('owari_soma')->table('proveedores_especiales')
                ->where('activo', true)
                ->get(['clave', 'correo', 'enviar_excel']);
        } catch (\Throwable $e) {
            \Log::warning('encolarPartidasEnvioSoma: no se pudo leer proveedores_especiales, no se encola nada: ' . $e->getMessage());
            return;
        }

        $envioInmediato = [];
        foreach ($cfg as $c) {
            if (!empty($c->enviar_excel) && trim((string) $c->correo) !== '') {
                $envioInmediato[] = $c->clave;
            }
        }

        // Si el grupo de este pedido ya recibio el correo inmediato, no se
        // encola nada (evita el envio duplicado).
        $grupo = $pedido->clave_proveedor;
        if (!empty($grupo) && in_array($grupo, $envioInmediato, true)) {
            \Log::info('encolarPartidasEnvioSoma: omitido, el proveedor ya recibe correo inmediato', [
                'pedido_especial' => $pedido->id, 'proveedor' => $grupo, 'partidas' => count($filas),
            ]);
            return;
        }

        $porEncolar = [];
        foreach ($filas as $f) {
            $porEncolar[] = array_merge($f, [
                'sistema_origen' => $sistemaOrigen,
                'folio_origen'   => (string) $pedido->id,
                'clave_cliente'  => $claveCliente,
                'nombre_cliente' => $nombreCliente,
                'elaboro'        => $elaboro,
                'estatus'        => 'pendiente',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        try {
            \DB::connection('owari_soma')->table('pedidos_especiales_partidas_envio')->insert($porEncolar);
            \Log::info('encolarPartidasEnvioSoma', [
                'pedido_especial' => $pedido->id,
                'origen'          => $sistemaOrigen,
                'encoladas'       => count($porEncolar),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('encolarPartidasEnvioSoma: insert fallo (pedido ' . $pedido->id . '): ' . $e->getMessage());
        }
    }

    /**
     * Envio ADICIONAL directo al proveedor especial: si en SOMA
     * (proveedores_especiales) ese proveedor tiene `enviar_excel = true` y un
     * `correo`, le manda el Excel REDUCIDO del pedido especial:
     * CLIENTE, PEDIDO ESPECIAL, CLAVE, CANTIDAD, CLAVE <nombre_comercial>.
     * Data-driven; envuelto en try/catch para no romper el pedido si falla.
     *
     * @param array $arreglo  El mismo arreglo del Excel completo (fila 0 =
     *                        encabezados; filas siguientes = partidas assoc).
     */
    private function enviarExcelReducidoProveedor($pedido, ?string $claveProveedor, array $arreglo): void
    {
        if (empty($claveProveedor)) return;

        try {
            $cfg = \DB::connection('owari_soma')->table('proveedores_especiales')
                ->where('clave', $claveProveedor)
                ->where('activo', true)
                ->first(['nombre', 'correo', 'enviar_excel']);
        } catch (\Throwable $e) {
            \Log::warning('proveedores_especiales correo/enviar_excel no disponible: ' . $e->getMessage());
            return;
        }

        if (!$cfg || empty($cfg->enviar_excel) || empty($cfg->correo)) return;

        // Uno o varios correos separados por comas.
        $destinos = array_values(array_filter(array_map('trim', explode(',', $cfg->correo))));
        if (empty($destinos)) return;

        // Encabezado dinamico "CLAVE <nombre>": nombre comercial de
        // proveedores_especiales; si esta vacio, cae a la clave.
        $nombreProveedor = !empty($cfg->nombre) ? $cfg->nombre : $claveProveedor;

        // Excel reducido: mismo contenido del completo, solo las columnas pedidas.
        $reducido = [[
            'CLIENTE', 'PEDIDO ESPECIAL', 'CLAVE', 'CANTIDAD', 'CLAVE ' . $nombreProveedor,
        ]];
        foreach ($arreglo as $i => $row) {
            if ($i === 0) continue; // fila de encabezados del arreglo completo
            $reducido[] = [
                $row['cliente'] ?? '',
                $row['id_pedido'] ?? '',
                $row['clave'] ?? '',
                $row['cantidad'] ?? '',
                $row['clave_proveedor'] ?? '',
            ];
        }

        $archivo = date('YmdHis') . '_prov_' . $pedido->id . '.xlsx';
        try {
            Excel::store(new \App\Exports\PedidoEspecialProveedorExport($reducido), 'pedidos_especiales/' . $archivo);

            \Mail::send('emails.pedido_especial_proveedor', ['pedido' => $pedido, 'nombreProveedor' => $nombreProveedor],
                function ($message) use ($pedido, $archivo, $destinos, $nombreProveedor) {
                    $message->from('pedido_especial@owari.com.mx', 'Pedido para surtir');
                    $message->subject('Pedido para surtir ' . $nombreProveedor . ' #' . $pedido->id);
                    $message->attach(storage_path() . '/app/pedidos_especiales/' . $archivo);
                    $message->to($destinos);
                });
        } catch (\Throwable $e) {
            \Log::warning('Envio de Excel reducido a proveedor fallo (pedido ' . $pedido->id . '): ' . $e->getMessage());
        }
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

    /**
     * Proxy server-side hacia SOMA /api/pedidos/capturar.
     * Evita CORS y mantiene la API key fuera del navegador.
     * Recibe el mismo payload que la API de SOMA y lo reenvía.
     */
    public function proxyCapturarSoma(Request $request)
    {
        $payload = $request->all();
        unset($payload['_token']);

        $url = rtrim(config('services.somma.api_url'), '/') . '/api/pedidos/capturar';
        $apiKey = config('services.somma.api_key');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status === 0) {
            \Log::warning('proxyCapturarSoma fallo: ' . $err);
            return response()->json(['response' => 0, 'message' => 'Error de red al proxy SOMA: ' . $err], 502);
        }

        $decoded = json_decode($body, true);
        return response()->json($decoded ?: ['response' => 0, 'message' => 'Respuesta SOMA no JSON', 'raw' => $body], $status);
    }

    /**
     * Encola un pedido SAE que el frontend no logro insertar tras 5 reintentos.
     * Un comando artisan (pedidos:procesar-sae-pendientes) lo retomara despues
     * y lo intentara hasta lograrlo o marcarlo como fallido.
     *
     * El payload debe ser identico al que recibiria /catalowari/api/guardar_v2
     * para poder reintentar tal cual.
     */
    public function encolarSaePendiente(Request $r)
    {
        $r->validate([
            'cliente'       => 'required|string|max:50',
            'empresa'       => 'required|integer|in:1,3',
            'usuario'       => 'nullable|string|max:100',
            'su_pedido'     => 'nullable|string|max:50',
            'origen'        => 'nullable|string|max:5',
            'partidas'      => 'required|array|min:1',
            'ultimo_error'  => 'nullable|string',
            'id_pedido_web' => 'nullable|integer',
        ]);

        $payload = [
            'empresa'   => intval($r->input('empresa')),
            'cliente'   => $r->input('cliente'),
            'usuario'   => $r->input('usuario', \Auth::user()->name ?? ''),
            'su_pedido' => $r->input('su_pedido', ''),
            'origen'    => $r->input('origen', 'W'),
            'partidas'  => $r->input('partidas'),
        ];

        $pendiente = PedidoSaePendiente::create([
            'cliente'       => $r->input('cliente'),
            'empresa'       => intval($r->input('empresa')),
            'payload'       => $payload,
            'intentos'      => 0,
            'ultimo_error'  => $r->input('ultimo_error'),
            'estado'        => PedidoSaePendiente::ESTADO_PENDIENTE,
            'id_pedido_web' => $r->input('id_pedido_web'),
        ]);

        return response()->json([
            'code'         => 1,
            'id_pendiente' => $pendiente->id,
            'mensaje'      => 'Pedido encolado para reintento',
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // UI: Cola de pedidos SAE pendientes
    // ────────────────────────────────────────────────────────────────────

    public function saePendientesIndex(PedidosSaePendientesDataTable $dataTable)
    {
        // Conteos por estado para los chips de la cabecera
        $conteos = PedidoSaePendiente::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        return $dataTable->render('pedidos_sae_pendientes.index', compact('conteos'));
    }

    public function saePendienteDetalle(Request $r, $id)
    {
        $pendiente = PedidoSaePendiente::find($id);
        if (!$pendiente) {
            return response()->json(['code' => 0, 'mensaje' => 'No encontrado'], 404);
        }
        return response()->json([
            'code'      => 1,
            'pendiente' => $pendiente,
        ]);
    }

    /**
     * Reintenta un pendiente especifico de forma sincrona desde la UI.
     * Usa la misma logica del comando artisan pero contra UN registro.
     */
    public function saePendienteReintentar(Request $r, $id)
    {
        $pendiente = PedidoSaePendiente::find($id);
        if (!$pendiente) {
            return response()->json(['code' => 0, 'mensaje' => 'No encontrado'], 404);
        }

        // Lock optimista
        $pendiente->estado = PedidoSaePendiente::ESTADO_EN_PROCESO;
        $pendiente->save();

        $payload = $pendiente->payload;

        $ch = curl_init('https://sistemasowari.com:8443/catalowari/api/guardar_v2');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status === 0) {
            $pendiente->fill([
                'estado'       => PedidoSaePendiente::ESTADO_PENDIENTE,
                'intentos'     => $pendiente->intentos + 1,
                'ultimo_error' => 'cURL: ' . $err,
            ])->save();
            return response()->json(['code' => 0, 'mensaje' => 'Error de red al contactar SAE: ' . $err]);
        }

        if ($status >= 400) {
            $pendiente->fill([
                'estado'       => PedidoSaePendiente::ESTADO_PENDIENTE,
                'intentos'     => $pendiente->intentos + 1,
                'ultimo_error' => 'HTTP ' . $status . ': ' . substr($body, 0, 500),
            ])->save();
            return response()->json(['code' => 0, 'mensaje' => 'SAE respondio HTTP ' . $status]);
        }

        $data = json_decode($body, true);
        if (!is_array($data) || ($data['code'] ?? 0) !== 1 || empty($data['pedido'])) {
            $pendiente->fill([
                'estado'       => PedidoSaePendiente::ESTADO_PENDIENTE,
                'intentos'     => $pendiente->intentos + 1,
                'ultimo_error' => 'SAE rechazo: ' . ($data['mensaje'] ?? substr($body, 0, 500)),
            ])->save();
            return response()->json(['code' => 0, 'mensaje' => 'SAE rechazo: ' . ($data['mensaje'] ?? 'desconocido')]);
        }

        // Exito
        $folio = $data['pedido'];
        $pendiente->fill([
            'estado'       => PedidoSaePendiente::ESTADO_COMPLETADO,
            'folio_sae'    => $folio,
            'intentos'     => $pendiente->intentos + 1,
            'completed_at' => now(),
        ])->save();

        // Si esta enlazado a un PedidoWeb, actualizar la columna correspondiente
        // segun la empresa: empresa 1 → pedido_sae (factura, existente),
        // empresa 3 → pedido_sae_remision (nuevo).
        if ($pendiente->id_pedido_web) {
            $espejo = PedidoWeb::find($pendiente->id_pedido_web);
            if ($espejo) {
                $columna = ($pendiente->empresa == 3) ? 'pedido_sae_remision' : 'pedido_sae';
                $valorActual = $espejo->{$columna} ?? null;
                if (empty($valorActual) || $valorActual === '0' || $valorActual === 0) {
                    $espejo->{$columna} = $folio;
                    $espejo->save();
                }
            }
        }

        return response()->json(['code' => 1, 'mensaje' => 'SAE acepto el pedido', 'folio' => $folio]);
    }

    public function saePendienteCancelar(Request $r, $id)
    {
        $pendiente = PedidoSaePendiente::find($id);
        if (!$pendiente) {
            return response()->json(['code' => 0, 'mensaje' => 'No encontrado'], 404);
        }

        $pendiente->fill([
            'estado'       => PedidoSaePendiente::ESTADO_FALLIDO,
            'ultimo_error' => 'Marcado como fallido manualmente por ' . (\Auth::user()->name ?? 'sistema'),
        ])->save();

        return response()->json(['code' => 1, 'mensaje' => 'Pendiente marcado como fallido']);
    }

}
