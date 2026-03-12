<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CarritoExcelImport;

use App\Models\DatosGenerales;
use App\Models\User;
use App\Models\Registrado;
use App\Models\ProductoBusqueda;
use App\Models\Favorito;
use App\Models\PedidoWeb;
use App\Models\PedidoPartida;
use App\Models\Cliente;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialSae;
use App\Models\PedidoEspecialPartida;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleXMLElement;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TiendaOnlineController extends Controller
{

    public function __construct()
    {
        $general = DatosGenerales::find(1);
        \View::share('general', $general);
    }


    public function aux()
    {
        $carrito = \Session::get('cart');
        array_push($carrito, ['numero_parte' => '04-E43023AN0C-KB', 'cantidad' => 10, 'partida' => [], 'sustituto' => false]);
        \Session::put('cart', $carrito);
    }

    public function login()
    {
        if (\Auth::check())
            if (\Auth::user()->cliente)
                return redirect()->route('tienda_online.dashboard');
            else {
                $titulo = "Cerrar sesión";
                return view('tienda_online.cerrar_sesion', compact('titulo'));
            } else {
            $titulo = "Iniciar sesión";
            return view('tienda_online.login', compact('titulo'));
        }

    }

    public function registro()
    {
        $titulo = "Registrarse";
        return view('tienda_online.registro', compact('titulo'));
    }

    public function registro_nuevo()
    {
        $titulo = "Registrarse";
        return view('tienda_online.registro_nuevo', compact('titulo'));
    }

    public function registrar(Request $r)
    {
        extract($r->all());

        $registrado = Registrado::where('telefono', $telefono)->first();
        if ($registrado) {
            \Session::flash('message', 'El numero telefonico ya se encuentra registrado, valida tu numero o ingresa uno nuevo.');
            return redirect()->route('tienda_online.registro');
        }

        $registrado = Registrado::where('email', $email)->first();
        if ($registrado) {
            \Session::flash('message', 'El correo electronico ya se encuentra registrado, valida tu email o ingresa uno nuevo.');
            return redirect()->route('tienda_online.registro');
        }

        $registrado = Registrado::create([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $email,
            'cliente' => $cliente
        ]);

        \Mail::send('emails.registro', compact('registrado'), function ($message) {
            $message->from('tiendaonline@owari.com.mx', 'OWARI Tienda Online');
            $message->subject("Registro de cliente");
            //$message->to(['john@kobusoft.com']);
            $message->to(['direccion@owari.com.mx', 'sistemas@owari.com.mx']);
        });


        \Session::flash('message', 'Tu registro esta completo. Nos pondremos en contacto contigo para brindarte la información y las condiciones de compra');
        return redirect()->route('tienda_online.registro');

    }


    public function registrar_nuevo(Request $r)
    {
        extract($r->all());

        $registrado = Registrado::where('telefono', $telefono)->first();
        if ($registrado) {
            \Session::flash('message', 'El numero telefonico ya se encuentra registrado, valida tu numero o ingresa uno nuevo.');
            return redirect()->route('tienda_online.registro_nuevo');
        }

        $registrado = Registrado::where('email', $email)->first();
        if ($registrado) {
            \Session::flash('message', 'El correo electronico ya se encuentra registrado, valida tu email o ingresa uno nuevo.');
            return redirect()->route('tienda_online.registro_nuevo');
        }

        $registrado = Registrado::create([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $email,
            'cliente' => 'M014M'
        ]);

        \Mail::send('emails.registro', compact('registrado'), function ($message) {
            $message->from('tiendaonline@owari.com.mx', 'OWARI Tienda Online');
            $message->subject("Registro de cliente");
            //$message->to(['john@kobusoft.com']);
            $message->to(['direccion@owari.com.mx', 'sistemas@owari.com.mx']);
        });

        //nuevo codigo

        $cliente = User::create([
            'name' => $nombre,
            'email' => $email,
            'password' => \Hash::make($password),
            'cliente' => true,
            'clave_cliente' => 'M014M'
        ]);

        $registrado->fill(['id_usuario' => $cliente->id])->save();

        $cliente->givePermissionTo([]);
        $cliente->sendEmailVerificationNotification();


        \Session::flash('message', 'Tu registro esta completo. Revisa tu correo electronico para poder validar tu cuenta y poder tener acceso');
        return redirect()->route('tienda_online.registro');

    }


    public function iniciarSesion(Request $request)
    {
        extract($request->all());

        if (\Auth::attempt(['email' => $email, 'password' => $password, 'cliente' => true], isset($recuerdame))) {
            return redirect()->route('tienda_online.dashboard');
        } else {
            \Session::flash('message', 'Los datos de usuario no coinciden.');
            return redirect()->route('tienda_online.login');
        }
    }


    public function dashboard()
    {
        $titulo = "Dashboard";
        return view('tienda_online.dashboard', compact('titulo'));
    }

    public function productos(Request $request)
    {

        /*
                    q = query
                    c = subgrupo de busqueda
                    p = pagina
                    a = autocompletar
                    f = filtro

        */

        extract($request->all());
        $q = trim($q);

        /*tipos de busqueda*/
        $busqueda = "";
        if (isset($c)) {
            $query = $this->query('categoria', $q);
            $busqueda = "Resultados por CATEGORIA: " . $q;
            $peticion = "?q=" . $q . "&c=" . $c . "&p=";

        } else if (isset($a)) {
            $query = $this->query('autocompletar', $q);
            $busqueda = "Resultados para: " . $q;
            $peticion = "?q=" . $q . "&a=" . $a . "&p=";
        } else if (isset($f)) {
            $busqueda = "Resultados para busqueda por filtrado";
            $q = [
                "ano" => $ano,
                "marca" => $marca,
                "modelo" => $modelo,
                "motor" => $motor,
                "grupo" => $grupo,
                "familia" => $familia,
            ];
            $query = $this->query('filtro', $q);
            $peticion = "?q=" . $q . "&c=" . $c . "&p=";
        } else if ($q == "") {
            $query = $this->query('todos', $q);
            $busqueda = "Todos los productos";
            $peticion = "?q=" . $q . "&p=";
        } else {
            if ($q == "lo_mas_nuevo") {
                $query = $this->query('nuevo', $q);
            } elseif (stripos($q, ' ') !== false) {
                $query = $this->query('palabras', $q);
            } else {
                // Normalizar: quitar guiones y caracteres especiales para buscar en buscador/codigosinguiones
                $q_normalizado = preg_replace('/[^A-Za-z0-9]/', '', $q);
                $query = $this->query('palabra', $q_normalizado);
            }

            $busqueda = "Resultados para: " . $q;
            $peticion = "?q=" . $q . "&p=";
        }

        $resultados = \DB::connection('mysql')->select($query);
        $resultados = array_intersect_key($resultados, array_unique(array_column($resultados, 'codigo_nikko')));
        $total_resultados = count($resultados);
        $mostrar_productos = 15;
        $offset = ($p - 1) * $mostrar_productos;
        $resultados = array_slice($resultados, $offset, $mostrar_productos);

        $productos = [];
        foreach ($resultados as $resultado) {
            array_push($productos, urlencode($resultado->codigo_nikko));
        }

        $url = 'https://sistemasowari.com:8443/catalowari/api/productos-existencias?' . http_build_query(["productos" => $productos]);
        //dd($url);
        /*$ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $existencias = json_encode($data,true);
        */
        $existencias = [];
        $botones = [];

        if ($total_resultados / $mostrar_productos > 10) {
            if ($p >= 1 && $p <= 7) {
                $botones = ["1", "2", "3", "4", "5", "6", "7", '...', ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            } else if (ceil($total_resultados / $mostrar_productos) - 2 <= $p) {
                $botones = ["1", "2", "3", '...', ceil($total_resultados / $mostrar_productos) - 6, ceil($total_resultados / $mostrar_productos) - 5, ceil($total_resultados / $mostrar_productos) - 4, ceil($total_resultados / $mostrar_productos) - 3, ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            } else {
                $botones = ["1", "2", "3", '...', $p - 1, $p, $p + 1, '...', ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            }
        } else {
            for ($i = 1; $i <= ceil($total_resultados / $mostrar_productos); $i++) {
                $botones[] = $i;
            }
        }

        $pagina = $p;
        $titulo = "Busqueda: " . ($q == "" ? "Todos" : $q) . " Pagina: " . $p;
        return view('tienda_online.productos', compact('resultados', 'total_resultados', 'botones', 'busqueda', 'pagina', 'peticion', 'titulo', 'existencias', 'q'));
    }


    public function detalleProducto($clave)
    {
        $clave = str_replace('_', '/', $clave);
        $clave = str_replace('+', '#', $clave);
        $titulo = "Producto: " . $clave;
        $producto = ProductoBusqueda::where('codigo_nikko', $clave)->first();
        if (!$producto) {
            return "El producto no existe";
        }
        $especificaciones = ProductoBusqueda::where('codigo_nikko', $clave)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
        $relacionados = ProductoBusqueda::where('modelo', $producto->modelo)->get()->toArray();
        $relacionados = array_intersect_key($relacionados, array_unique(array_column($relacionados, 'codigo_nikko')));
        $relacionados = array_slice($relacionados, 0, 8);

        $especificaciones_extra = [];
        if ($producto->extra_clave_1 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_1)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            $especificaciones_extra = $aux;
        }
        if ($producto->extra_clave_2 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_2)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            if ($especificaciones_extra) {
                $especificaciones_extra = $aux->merge($especificaciones_extra);
            } else {
                $especificaciones_extra = $aux;
            }

        }
        if ($producto->extra_clave_3 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_3)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            if ($especificaciones_extra) {
                $especificaciones_extra = $aux->merge($especificaciones_extra);
            } else {
                $especificaciones_extra = $aux;
            }

        }

        return view('tienda_online.ver_producto', compact('producto', 'especificaciones', 'relacionados', 'titulo', 'especificaciones_extra'));
    }

    public function detalleProductoDemo($clave)
    {
        $clave = str_replace('_', '/', $clave);
        $titulo = "Producto: " . $clave;
        $producto = ProductoBusqueda::where('codigo_nikko', $clave)->first();
        if (!$producto) {
            return "El producto no existe";
        }
        $especificaciones = ProductoBusqueda::where('codigo_nikko', $clave)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
        $relacionados = ProductoBusqueda::where('modelo', $producto->modelo)->get()->toArray();
        $relacionados = array_intersect_key($relacionados, array_unique(array_column($relacionados, 'codigo_nikko')));
        $relacionados = array_slice($relacionados, 0, 8);

        $especificaciones_extra = [];
        if ($producto->extra_clave_1 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_1)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            $especificaciones_extra = $aux;
        }
        if ($producto->extra_clave_2 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_2)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            if ($especificaciones_extra) {
                $especificaciones_extra = $aux->merge($especificaciones_extra);
            } else {
                $especificaciones_extra = $aux;
            }

        }
        if ($producto->extra_clave_3 != "") {
            $aux = ProductoBusqueda::where('codigo_nikko', $producto->extra_clave_3)->orderBy('armadora', 'asc')->orderBy('modelo', 'ASC')->get();
            if ($especificaciones_extra) {
                $especificaciones_extra = $aux->merge($especificaciones_extra);
            } else {
                $especificaciones_extra = $aux;
            }

        }

        return view('tienda_online.ver_producto_demo', compact('producto', 'especificaciones', 'relacionados', 'titulo', 'especificaciones_extra'));
    }


    private function query($tipo_query, $string)
    {

        switch ($tipo_query) {
            case 'categoria':
                $query = "SELECT
                            marca_comercial,
                            codigo_nikko,
                            grupo,
                            subgrupo,
                            descripcion_1,
                            descripcion_2,
                            descripcion_3,
                            caracteristicas_1,
                            caracteristicas_2,
                            caracteristicas_3,
                            caracteristicas_4,
                            equivalencia_1,
                            equivalencia_2,
                            equivalencia_3,
                            equivalencia_4,
                            equivalencia_5,
                            buscador,
                            precio_normal,
                            precio_final,
                            existencias,
                            minimo_compra_oferta,
                            especial,
                            disponibilidad,
                            ventas
                        FROM
                            productos_busqueda
                        WHERE
                            subgrupo = '" . str_replace("_", " ", $string) . "'
                            AND deleted_at is null
                            order by ventas desc
                            LIMIT 0,1000000000";
                break;

            case 'autocompletar':
                $query = "SELECT
                            marca_comercial,
                            codigo_nikko,
                            grupo,
                            subgrupo,
                            descripcion_1,
                            descripcion_2,
                            descripcion_3,
                            caracteristicas_1,
                            caracteristicas_2,
                            caracteristicas_3,
                            caracteristicas_4,
                            equivalencia_1,
                            equivalencia_2,
                            equivalencia_3,
                            equivalencia_4,
                            equivalencia_5,
                            buscador,
                            precio_normal,
                            precio_final,
                            existencias,
                            minimo_compra_oferta,
                            especial,
                            disponibilidad,
                            ventas
                        FROM
                            productos_busqueda
                        WHERE
                            invocacion LIKE '%" . $string . "%'
                            AND deleted_at is null 
                            order by ventas desc 
                            LIMIT 0,1000000000";
                break;

            case 'filtro':
                $extra = "";
                if ($q['ano'] != "0" && $q['ano'] != "todos") {
                    $extra .= " AND anos LIKE '%" . $q['ano'] . "%' ";
                }

                if ($q['marca'] != "0" && $q['marca'] != "todos") {
                    $extra .= " AND armadora = '" . $q['marca'] . "' ";
                }

                if ($q['modelo'] != "0" && $q['modelo'] != "todos") {
                    $extra .= " AND modelo = '" . $q['modelo'] . "' ";
                }

                if ($q['modelo'] != "0" && $q['motor'] != "todos") {
                    $extra .= " AND motor = '" . $q['motor'] . "' ";
                }

                if ($q['grupo'] != "0" && $q['grupo'] != "todos") {
                    $extra .= " AND grupo = '" . $q['grupo'] . "' ";
                }

                if ($q['familia'] != "0" && $q['familia'] != "todos") {
                    $extra .= " AND subgrupo = '" . $q['familia'] . "' ";
                }

                $query = "SELECT
                        DISTINCT (codigo_nikko),
                        marca_comercial,
                        grupo,
                        subgrupo,
                        descripcion_1,
                        descripcion_2,
                        descripcion_3,
                        caracteristicas_1,
                        caracteristicas_2,
                        caracteristicas_3,
                        caracteristicas_4,
                        equivalencia_1,
                        equivalencia_2,
                        equivalencia_3,
                        equivalencia_4,
                        equivalencia_5,
                        pagina_principal,
                        precio_normal,
                        precio_final,
                        existencias,
                        minimo_compra_oferta,
                        especial,
                        disponibilidad,
                        ventas
                    FROM
                        productos_busqueda
                    WHERE deleted_at is null
                    " . $extra . "
                    ORDER BY ventas DESC, descripcion_1 ASC";

            case 'palabra':
                $query = "SELECT
                            marca_comercial,
                            codigo_nikko,
                            grupo,
                            subgrupo,
                            descripcion_1,
                            descripcion_2,
                            descripcion_3,
                            caracteristicas_1,
                            caracteristicas_2,
                            caracteristicas_3,
                            caracteristicas_4,
                            equivalencia_1,
                            equivalencia_2,
                            equivalencia_3,
                            equivalencia_4,
                            equivalencia_5,
                            buscador,
                            precio_normal,
                            precio_final,
                            existencias,
                            minimo_compra_oferta,
                            extra_clave_1,
                            extra_clave_2,
                            extra_clave_3,
                            especial,
                            disponibilidad,
                            ventas
                        FROM
                            productos_busqueda
                        WHERE
                            buscador LIKE '%" . $string . "%'
                            AND deleted_at is null order by ventas desc";
                break;
            case 'nuevo':
                $query = "SELECT
                            marca_comercial,
                            codigo_nikko,
                            grupo,
                            subgrupo,
                            descripcion_1,
                            descripcion_2,
                            descripcion_3,
                            caracteristicas_1,
                            caracteristicas_2,
                            caracteristicas_3,
                            caracteristicas_4,
                            equivalencia_1,
                            equivalencia_2,
                            equivalencia_3,
                            equivalencia_4,
                            equivalencia_5,
                            buscador,
                            precio_normal,
                            precio_final,
                            existencias,
                            minimo_compra_oferta,
                            extra_clave_1,
                            extra_clave_2,
                            extra_clave_3,
                            especial,
                            disponibilidad,
                            ventas
                        FROM
                            productos_busqueda
                        WHERE
                            lo_mas_nuevo != ''
                            AND deleted_at is null order by ventas desc";
                break;
            case 'todos':
                $query = "SELECT
                                marca_comercial,
                                codigo_nikko,
                                grupo,
                                subgrupo,
                                descripcion_1,
                                descripcion_2,
                                descripcion_3,
                                caracteristicas_1,
                                caracteristicas_2,
                                caracteristicas_3,
                                caracteristicas_4,
                                equivalencia_1,
                                equivalencia_2,
                                equivalencia_3,
                                equivalencia_4,
                                equivalencia_5,
                                buscador,
                                precio_normal,
                                precio_final,
                                existencias,
                                minimo_compra_oferta,
                                especial,
                                disponibilidad,
                                ventas
                            FROM
                                productos_busqueda
                            WHERE deleted_at is null
                            ORDER BY descripcion_1 asc, ventas desc";
                break;
            case 'palabras':
                $query = "SELECT
                    marca_comercial,
                    codigo_nikko,
                    grupo,
                    subgrupo,
                    descripcion_1,
                    descripcion_2,
                    descripcion_3,
                    caracteristicas_1,
                    caracteristicas_2,
                    caracteristicas_3,
                    caracteristicas_4,
                    equivalencia_1,
                    equivalencia_2,
                    equivalencia_3,
                    equivalencia_4,
                    equivalencia_5,
                    precio_normal,
                    precio_final,
                    existencias,
                    minimo_compra_oferta,
                    buscador,
                    extra_clave_1,
                    extra_clave_2,
                    extra_clave_3,
                    especial,
                    disponibilidad,
                    ventas,
                    MATCH (buscador) AGAINST ('+" . trim($string) . "' IN BOOLEAN MODE) AS score
                FROM
                    productos_busqueda
                WHERE
                    MATCH (buscador) AGAINST ('+" . trim($string) . "' IN BOOLEAN MODE)
                    AND deleted_at is null
                ORDER BY score desc, ventas desc";
                break;
        }
        return $query;
    }

    public function logout()
    {
        \Auth::logout(); // logs out the user 
        return redirect('https://owari.com.mx');
    }

    public function actualizarFavoritos(Request $request)
    {
        extract($request->all());
        $favorito = Favorito::where('numero_parte', $numero_parte)->where('id_usuario', \Auth::user()->id)->first();
        if ($favorito) {
            if ($funcion == 'quitar') {
                $favorito->delete();
            }
        } elseif (!$favorito && $funcion == "agregar") {
            Favorito::create(['numero_parte' => $numero_parte, "id_usuario" => \Auth::user()->id]);
        }

        return json_encode([
            'code' => 1,
        ]);
    }

    public function favoritos(Request $request)
    {
        $titulo = "Favoritos";

        $favoritos = Favorito::where('id_usuario', \Auth::user()->id)->get()->pluck('numero_parte')->all();
        $resultados = ProductoBusqueda::whereIn('codigo_nikko', $favoritos)->get()->toArray();
        $resultados = array_intersect_key($resultados, array_unique(array_column($resultados, 'codigo_nikko')));
        return view('tienda_online.favoritos', compact('resultados', 'titulo'));
    }

    public function actualizarCarrito(Request $request)
    {
        extract($request->all());


        if (!\Session::has('cart')) {

            if ($cantidad > $partida['existencia'])
                $cantidad = $partida['existencia'];

            if ($cantidad > 0) {
                $carrito = [['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]];
            }

        } else {

            $carrito = \Session::get('cart');
            $solicitado = 0;
            foreach ($carrito as $key => $value) {
                if ($value['numero_parte'] == $numero_parte) {
                    $solicitado = $value['cantidad'];
                    unset($carrito[$key]);
                    break;
                }
            }
            if ($cantidad > 0) {


                if ($funcion == "actualizar") {
                    if ($cantidad > $partida['existencia']) {
                        $cantidad = $partida['existencia'];
                    }
                } else {
                    if ($cantidad + $solicitado > $partida['existencia']) {
                        $cantidad = $partida['existencia'];
                        $solicitado = 0;
                    }
                }

                if ($funcion == "agregar") {
                    array_push($carrito, ['numero_parte' => $numero_parte, 'cantidad' => $cantidad + $solicitado, 'partida' => $partida, 'sustituto' => $sustituto]);
                } else {
                    array_push($carrito, ['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]);
                }
            }

        }

        \Session::put('cart', $carrito);
        return json_encode([
            'code' => 1,
            'carrito' => $carrito,
        ]);
    }


    public function actualizarCarritoEspecial(Request $request)
    {
        extract($request->all());

        if (!\Session::has('cartEspecial')) {

            if ($cantidad > 0) {
                $carrito = [['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]];
            }

        } else {

            $carrito = \Session::get('cartEspecial');
            $solicitado = 0;
            foreach ($carrito as $key => $value) {
                if ($value['numero_parte'] == $numero_parte) {
                    $solicitado = $value['cantidad'];
                    unset($carrito[$key]);
                    break;
                }
            }
            if ($cantidad > 0) {


                if ($funcion == "agregar") {
                    array_push($carrito, ['numero_parte' => $numero_parte, 'cantidad' => $cantidad + $solicitado, 'partida' => $partida, 'sustituto' => $sustituto]);
                } else {
                    array_push($carrito, ['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]);
                }
            }

        }

        \Session::put('cartEspecial', $carrito);
        return json_encode([
            'code' => 1,
            'carrito' => $carrito,
        ]);
    }

    public function carrito()
    {
        $titulo = "Carrito";
        $premio = "PROMOCIONAL";
        $productos = [];
        if (\Session::has('cart')) {
            $carrito = \Session::get('cart');

            $existe_premio_carrito = false;
            foreach ($carrito as $key => $value) {
                // code...
                if ($value['numero_parte'] == $premio)
                    $existe_premio_carrito = true;
            }

            if ($existe_premio_carrito && count($carrito) == 1) {
                \Session::put('cart', []);
                $carrito = [];
            }



            if (count($carrito) > 0 && !$existe_premio_carrito) {

                $premio_partida = PedidoPartida::join('pedidos_web', 'pedidos_partidas.id_pedido', '=', 'pedidos_web.id')->where('pedidos_partidas.clave', $premio)->where('pedidos_web.cliente', \Auth::user()->clave_cliente)->where('pedidos_web.deleted_at', null)->first();

                if (!$premio_partida) {
                    $url = 'https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto?' . http_build_query(["clave" => $premio, "cliente" => \Auth::user()->clave_cliente, 'tipo' => 'factura']);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $producto = json_decode($data, true);

                    if ($producto['existencia'] > 0)
                        array_push($carrito, ['numero_parte' => $premio, 'cantidad' => 1, 'partida' => $producto, 'sustituto' => false]);
                }

            }


            $productos = ProductoBusqueda::whereIn('codigo_nikko', array_column($carrito, 'numero_parte'))->get()->toArray();
            $productos = array_intersect_key($productos, array_unique(array_column($productos, 'codigo_nikko')));
            foreach ($productos as $key => $value) {

                $url = 'https://sistemasowari.com:8443/catalowari/api/producto-existencia?' . http_build_query(["clave" => $value['codigo_nikko']]);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                $existencias_reales = json_decode($data, true);








                $partes = ProductoBusqueda::where('codigo_nikko', $value['codigo_nikko'])->get();
                $motores = "";
                foreach ($partes as $parte) {
                    # code...
                    $motores .= $parte->armadora . " " . $parte->modelo . " " . $parte->ano_inicio . "-" . $parte->ano_final . " " . $parte->cilindros . "CIL " . $parte->motor . "L<br>";
                }
                $productos[$key]['motores'] = $motores;
                foreach ($carrito as $llave => $valor) {
                    if ($valor['numero_parte'] == $value['codigo_nikko']) {

                        $productos[$key]['mensaje_existencia'] = '';
                        $productos[$key]['solicitado'] = $valor['cantidad'];
                        $productos[$key]['solicitado_original'] = $valor['cantidad'];

                        if ($existencias_reales['existencia'] <= 0) {
                            $productos[$key]['existencia_real'] = 0;
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia de este producto. <br> Solicitaste ' . $valor['cantidad'];
                            $productos[$key]['solicitado'] = 0;
                        }


                        if ($existencias_reales['existencia'] < $valor['cantidad']) {
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia completa de este producto. De ' . $valor['cantidad'] . ' paso a ' . $existencias_reales['existencia'];
                            $productos[$key]['solicitado'] = $existencias_reales['existencia'];
                        }




                        $productos[$key]['partida'] = $valor['partida'];
                        if (isset($valor['sustituto']))
                            $productos[$key]['sustituto'] = $valor['sustituto'];
                        else
                            $productos[$key]['sustituto'] = "false";


                        if (isset($valor['negociado']))
                            $productos[$key]['negociado'] = $valor['negociado'];
                        break;
                    }
                }
            }

            $llave_final = count($productos);
            foreach ($carrito as $llave => $valor) {
                if ($valor['numero_parte'] == $premio) {

                    $productos[$llave_final]['codigo_nikko'] = $premio;
                    $productos[$llave_final]['descripcion_1'] = $premio;
                    $productos[$llave_final]['marca_comercial'] = $premio;
                    $productos[$llave_final]['solicitado'] = $valor['cantidad'];
                    $productos[$llave_final]['partida'] = $valor['partida'];
                    $productos[$llave_final]['sustituto'] = "false";
                    $productos[$llave_final]['codigo_nikko'] = $premio;
                    break;
                }
            }


        }

        $productos_especiales = [];
        if (\Session::has('cartEspecial')) {
            $carrito = \Session::get('cartEspecial');
            $productos_especiales = ProductoBusqueda::whereIn('codigo_nikko', array_column($carrito, 'numero_parte'))->get()->toArray();
            $productos_especiales = array_intersect_key($productos_especiales, array_unique(array_column($productos_especiales, 'codigo_nikko')));
            foreach ($productos_especiales as $key => $value) {
                $partes = ProductoBusqueda::where('codigo_nikko', $value['codigo_nikko'])->get();
                $motores = "";
                foreach ($partes as $parte) {
                    # code...
                    $motores .= $parte->armadora . " " . $parte->modelo . " " . $parte->ano_inicio . "-" . $parte->ano_final . " " . $parte->cilindros . "CIL " . $parte->motor . "L<br>";
                }
                $productos_especiales[$key]['motores'] = $motores;
                foreach ($carrito as $llave => $valor) {
                    if ($valor['numero_parte'] == $value['codigo_nikko']) {
                        $productos_especiales[$key]['solicitado'] = $valor['cantidad'];
                        $productos_especiales[$key]['partida'] = $valor['partida'];
                        if (isset($valor['sustituto']))
                            $productos_especiales[$key]['sustituto'] = $valor['sustituto'];
                        else
                            $productos_especiales[$key]['sustituto'] = "false";


                        if (isset($valor['negociado']))
                            $productos_especiales[$key]['negociado'] = $valor['negociado'];
                        break;
                    }
                }
            }
        }

        $estampa = date("YmdHis");

        return view('tienda_online.carrito', compact('productos', 'estampa', 'titulo', 'productos_especiales'));
    }


    public function guardarPedido(Request $request)
    {
        extract($request->all());

        $data = [
            'cliente' => $cliente,
            'subtotal',
            'iva',
            'gran_total',
            'cadena_original' => strval(json_encode($request->all())),
            'estado' => "CAPTURADO",
            'capturo' => \Auth::user()->id,
            'pedido_sae' => $pedido_sae
        ];

        if (isset(\Auth::user()->clienteData))
            if (\Auth::user()->clienteData->tiendita) {
                $data['tiendita'] = true;
                $data['porcentaje'] = \Auth::user()->clienteData->porcentaje;
            }


        $pedido = PedidoWeb::create($data);

        $subtotal = 0;
        $iva = 0;
        $gran_total = 0;

        foreach ($partidas as $key => $value) {
            // code...
            $data = [
                'id_pedido' => $pedido->id,
                'clave' => $value['codigo'],
                'descripcion' => $value['descripcion'],
                'precio_unitario' => $value['precio'],
                'iva' => $value['precio_iva'],
                'cantidad' => $value['cantidad'],
                'gran_total' => $value['total']
            ];

            PedidoPartida::create($data);



            $subtotal += ($value['cantidad'] * $value['precio_iva']);
            $iva += $value['cantidad'] * ($value['precio'] - $value['precio_iva']);
            $gran_total += ($value['cantidad'] * $value['precio']);

        }

        $pedido->fill([
            'subtotal' => $subtotal,
            'iva' => $iva,
            'gran_total' => $gran_total,
        ])->save();


        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id
        ]);

    }


    public function guardadoExitoso(Request $r)
    {
        extract($r->all());
        $pedido = PedidoWeb::find($id_pedido);
        if ($pedido->cliente != \Auth::user()->clave_cliente)
            return redirect()->route('tienda_online.dashboard');


        session()->forget(['cart', 'cartEspecial']); // o ['cart', 'cart.items', 'cart_count']
        session()->save();

        $titulo = "Carrito exitoso";
        return view('tienda_online.exito', compact('titulo', 'id_pedido'));

    }

    public function misPedidos()
    {
        $titulo = "Pedidos";
        $pedidos = PedidoWeb::where('cliente', \Auth::user()->clave_cliente)->where('created_at', '>=', '2025-01-01')->orderBy('created_at', 'desc')->limit(30)->get();
        $pedidos_especiales = PedidoEspecial::where('cliente', \Auth::user()->clave_cliente)->where('created_at', '>=', '2025-01-01')->limit(30)->orderBy('created_at', 'desc')->get();

        $pedidos_sae = $pedidos->pluck('pedido_sae')->toArray();
        $pedidos_especiales_sae = PedidoEspecialSae::where('cliente', \Auth::user()->clave_cliente)->orderBy('created_at', 'desc')->limit(30)->get()->pluck('pedido_sae')->toArray();
        $no_pedidos = array_merge($pedidos_sae, $pedidos_especiales_sae);

        $url = 'https://sistemasowari.com:8443/catalowari/api/pedidos_cliente';
        //dd($url);  
        $payload = ["clave" => \Auth::user()->clave_cliente, "pedidos" => $no_pedidos];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // NO validar SSL
            CURLOPT_SSL_VERIFYHOST => 0,     // NO validar host
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 45,
            // CURLOPT_FOLLOWLOCATION => true, // descomenta si hay redirecciones
        ]);

        $raw = curl_exec($ch);
        //dd($raw);
        if ($raw === false) {
            die('cURL error: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        // Decodificar JSON
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            die("HTTP $code. Respuesta no JSON: " . $raw);
        }


        $pedidos_mostrador = $data;



        return view('tienda_online.pedidos', compact('pedidos', 'titulo', 'pedidos_especiales', 'pedidos_mostrador'));
    }

    public function detallePedido(Request $request)
    {
        extract($request->all());
        $titulo = "Pedidos";
        $pedido = PedidoWeb::find($q);

        if ($pedido->cliente != \Auth::user()->clave_cliente)
            return redirect()->route('tienda_online.dashboard');


        return view('tienda_online.detalle_pedido', compact('pedido', 'titulo'));

    }


    public function detallePedidoEspecial(Request $request)
    {
        extract($request->all());
        $titulo = "Pedidos especiales";
        $pedido = PedidoEspecial::find($q);
        $pedidos_sae = PedidoEspecialSae::where('id_pedido_especial', $pedido->id)->get();

        if ($pedido->cliente != \Auth::user()->clave_cliente)
            return redirect()->route('tienda_online.dashboard');


        return view('tienda_online.detalle_pedido_especial', compact('pedido', 'titulo', 'pedidos_sae'));

    }

    public function excelCarrito(Request $r)
    {

        extract($r->all());
        $archivoName = 'excel.' . $r->file('excel')->getClientOriginalExtension();
        $r->file('excel')->move(base_path() . '/public/uploads/', $archivoName);
        $collection = (new CarritoExcelImport)->toArray(base_path() . '/public/uploads/' . $archivoName);

        $productos_finales = [];
        $mensajes = '<ul>';

        foreach ($collection[0] as $key => $value) {


            // code...
            if ($key != 0) {

                //buscamos la clave del producto, si no mandamos el sustitutoi
                $normal = \DB::connection('mysql')->table('productos_busqueda')->select('codigo_nikko')->orWhere('codigo_nikko', $value[0])->orWhere('codigosinguiones', $value[0])->first();
                $sustituto = false;
                if ($normal)
                    $clave = $normal->codigo_nikko;
                else {
                    $similar = \DB::connection('mysql')->table('productos_busqueda')->select('codigo_nikko')->orWhere('equivalencia_1', $value[0])->orWhere('equivalencia_2', $value[0])->orWhere('equivalencia_3', $value[0])->orWhere('equivalencia_4', $value[0])->orWhere('equivalencia_5', $value[0])->first();
                    if ($similar) {
                        $clave = $similar->codigo_nikko;
                        $sustituto = true;
                    } else
                        $clave = $value[0];
                }



                $url = 'https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto?' . http_build_query(["clave" => $clave, "cliente" => \Auth::user()->clave_cliente, 'tipo' => 'factura']);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                $producto = json_decode($data, true);

                if ($producto['code']) {
                    if (intval($value[1]) <= intval($producto['existencia'])) {
                        array_push($productos_finales, ['clave' => $clave, 'cantidad' => $value[1], 'partida' => $producto, 'sustituto' => $sustituto]);
                    } elseif (intval($producto['existencia']) > 0 && intval($value[1]) > intval($producto['existencia'])) {
                        array_push($productos_finales, ['clave' => $clave, 'cantidad' => $producto['existencia'], 'partida' => $producto, 'sustituto' => $sustituto]);
                        $mensajes .= "<li>Fila " . ($key + 1) . ": No hay stock completo para el producto: " . $clave . " faltarian " . (floatval(($value[1])) - floatval($producto['existencia'])) . " unidades</li>";
                        if ($sustituto) {
                            $mensajes .= "<li>Fila " . ($key + 1) . ": Estamos agregando el producto equivalente " . $value[0] . " == " . $clave . "</li>";
                        }
                    } else
                        $mensajes .= "<li>Fila " . ($key + 1) . ": No hay stock del producto " . $clave . "</li>";
                } else {
                    $mensajes .= "<li>Fila " . ($key + 1) . ": El producto " . $clave . " no existe en el sistema</li>";
                }

            }

        }

        return json_encode([
            'productos' => $productos_finales,
            'mensajes' => $mensajes . "</ul>"
        ]);

    }
    public function descuentos(Request $request)
    {
        $titulo = "Descuentos";
        $url = 'https://sistemasowari.com:8443/catalowari/api/productos-descuentos?' . http_build_query(["cliente" => \Auth::user()->clave_cliente]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data, true);


        $resultados = \DB::connection('mysql')->table('productos_busqueda')->select('*')->whereIn('codigo_nikko', $data['productos'])->get()->toArray();
        $resultados = array_intersect_key($resultados, array_unique(array_column($resultados, 'codigo_nikko')));
        $total_resultados = count($resultados);

        $categorias = [];
        foreach ($resultados as $key => $val) {
            if (!isset($categorias[$val->grupo]))
                $categorias[$val->grupo] = [];

            if (!isset($categorias[$val->grupo][$val->subgrupo]))
                $categorias[$val->grupo][$val->subgrupo] = true;
        }

        return view('tienda_online.descuentos', compact('resultados', 'total_resultados', 'titulo', 'categorias'));
    }

    public function vaciarCarrito()
    {

        //dd(\Session::get('cart'),\Session::get('cartEspecial'));
        \Session::put('cart', []);
        \Session::put('cartEspecial', []);
        return false;
    }

    public function editarCliente(Request $r)
    {
        $titulo = "Editar cliente";
        return view('tienda_online.cliente', compact('titulo'));
    }

    public function actualizarPassword(Request $r)
    {

        extract($r->all());
        $usuario = User::find(\Auth::user()->id);
        $usuario->fill(['password' => \Hash::make($password)])->save();
        \Session::flash('message', 'Tu password se actualizo correctamente.');
        $titulo = "Editar cliente";
        return view('tienda_online.cliente', compact('titulo'));
    }


    public function actualizarTiendita(Request $r)
    {
        extract($r->all());
        $cliente = Cliente::where('id_usuario', \Auth::user()->id)->first();
        if (!$cliente) {
            $cliente = Cliente::create([
                'id_usuario' => \Auth::user()->id
            ]);
        }
        $data = ['tiendita' => false];

        if (isset($activar_tiendita))
            $data['tiendita'] = true;

        if (isset($logotipo)) {
            $filename = uniqid() . '.' . \File::extension($logotipo->getClientOriginalName());
            $logotipo->move(base_path() . '/public/logos/', $filename);
            $data['logotipo'] = $filename;
        }

        if ($porcentaje > 0)
            $data['porcentaje'] = $porcentaje;

        $cliente->fill($data)->save();
        \Session::flash('message', 'Tu información se actualizo correctamente.');
        $titulo = "Editar cliente";
        return view('tienda_online.cliente', compact('titulo'));

    }

    public function generarPDF(Request $r)
    {
        extract($r->all());
        $pedido = PedidoWeb::find($id_pedido);
        $pdf = PDF::loadView('pdf.pedido', compact("pedido", 'nombre'));
        $archivo = date('YmdHis') . ".pdf";
        $pdf->save(base_path() . "/public/pdfs/pedidos/" . $archivo);

        if (isset($email)) {
            \Mail::send('emails.pedido', compact('pedido'), function ($message) use ($pedido, $archivo, $email) {
                $message->from('tiendaonline@tiendaonline.com', 'Tienda Online');
                $message->subject("Gracias por su compra! Pedido " . $pedido->pedido_sae);
                $message->attach(base_path() . "/public/pdfs/pedidos/" . $archivo);
                $message->to(['john@kobusoft.com', $email]);
                //$message->to(['direccion@owari.com.mx','sistemas@owari.com.mx']);
            });
            return json_encode([
                'code' => 1
            ]);

        } else {
            return json_encode([
                'code' => 1,
                'archivo' => $archivo
            ]);
        }
    }

    public function liquidacion(Request $request)
    {

        extract($request->all());
        $titulo = "Liquidaciones";
        $url = 'https://sistemasowari.com:8443/catalowari/api/liquidacion';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data, true);

        $resultados = \DB::connection('mysql')->table('productos_busqueda')->select('*')->whereIn('codigo_nikko', $data)->get()->toArray();
        $resultados = array_intersect_key($resultados, array_unique(array_column($resultados, 'codigo_nikko')));
        $total_resultados = count($resultados);
        $mostrar_productos = 15;
        $offset = ($p - 1) * $mostrar_productos;
        $resultados = array_slice($resultados, $offset, $mostrar_productos);

        $productos = [];
        foreach ($resultados as $resultado) {
            array_push($productos, urlencode($resultado->codigo_nikko));
        }


        $existencias = [];
        $botones = [];

        if ($total_resultados / $mostrar_productos > 10) {
            if ($p >= 1 && $p <= 7) {
                $botones = ["1", "2", "3", "4", "5", "6", "7", '...', ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            } else if (ceil($total_resultados / $mostrar_productos) - 2 <= $p) {
                $botones = ["1", "2", "3", '...', ceil($total_resultados / $mostrar_productos) - 6, ceil($total_resultados / $mostrar_productos) - 5, ceil($total_resultados / $mostrar_productos) - 4, ceil($total_resultados / $mostrar_productos) - 3, ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            } else {
                $botones = ["1", "2", "3", '...', $p - 1, $p, $p + 1, '...', ceil($total_resultados / $mostrar_productos) - 2, ceil($total_resultados / $mostrar_productos) - 1, ceil($total_resultados / $mostrar_productos)];
            }
        } else {
            for ($i = 1; $i <= ceil($total_resultados / $mostrar_productos); $i++) {
                $botones[] = $i;
            }
        }

        $pagina = $p;
        $titulo = "Liquidación Pagina: " . $p;
        return view('tienda_online.productos_liquidacion', compact('resultados', 'total_resultados', 'botones', 'pagina', 'titulo'));


    }


    public function pantallaLiquidaciones()
    {
        $data = ["*BERG-0281", "0-123-320-007", "02-K030293SF", "02-K050290", "02-K070690", "02-K070905", "02-K080585", "02-K080590", "02-K080680", "02-T071", "021-905-106", "03-102421", "03-103540C", "03-107051", "03-2477", "03-2735", "03-2776", "03-651C", "03-681", "03-693", "03-71621", "03-76008", "03-85438", "03-904086", "03-97613", "032128M3", "034-109309AD", "03C-903-024EVM", "04-352041-KB", "04-52088632RL-KB", "04-90441VW470-KB", "04-KITDIST1MATIZ", "058-0453-966", "058-905-105", "06-AP3923", "06-APP3924", "06-APP64", "06-XP5684", "06A-905-115", "071400-4881", "08-604402", "10-13511536Z", "10-13593730", "10-13597416G", "10-20939745Z", "10-21421-03000", "10-21443-03010K", "10-22891508Z", "10-23458677Z", "10-24101887Z", "10-24465791", "10-24586005Z", "10-42342981", "10-42342981G", "10-42495490G", "10-52102799G", "10-52102799Z", "10-55354071", "10-55496663Z", "10-55568041Z", "10-9048411Z", "10-9066062Z", "10-9066063Z", "10-95390887G", "10-96190259", "10-96318238", "10-96339739G", "10-96397517", "10-96416331K", "10-96456493G", "10-96456713", "10-96495288", "10-96495288Z", "10-96536670G", "10-96676690G", "10-96958201G", "10-96958210G", "100-027", "100-028", "100-112", "10242405SFT", "11-C902", "11-C903", "11-C904", "11-C906", "11-C907", "11-C910", "11-C914", "11-C920", "11-C922", "11-C923", "11-C924", "11-C925", "11-C929", "11-S124", "1103028", "1108012SFT", "12-LA5240-G", "12-LB51830", "120-007", "120-025", "120-034", "120-039", "120-042", "120-043", "120-044", "120-063", "120-067", "120-072", "120-073", "120-098", "120-099", "120-100", "120-101", "120-102", "120-103", "120-118", "120-119", "120-123", "120-628", "120488191VM", "130-006", "130-013", "14-CB139", "14-CB225", "14-CB236", "14-CB260", "14-CH02", "14-L3301", "14-L4225", "14-L4316", "14-L4340", "14-L5000", "14-L6150", "14-L6185", "14-L6218", "14-L6851", "14-L9029", "1403093SFT", "142-938", "1426004", "15-PULCL041", "1505003SFT", "1505004SFT", "16-510059", "16-510074", "16-9036938011", "16-HUF1219", "161-4142143030", "17-282", "17-284", "18-FA11062", "18-FA11580", "18-FA4830", "18-FA7617", "18-FA899AD", "18874-11080VM", "19-93227142", "19010-5R1-003BC", "1J1-614-105FP", "1K0511327AQ", "2100290050-BFP", "220-030", "220-031", "220-033", "220-036", "220-037", "220-039", "220-042", "220-051", "220-055", "220-060", "220-066", "221620M3\/4B000-K", "23100-00QAA", "23100-EB31AVM", "30000-B13G0CK", "304-031", "32-510074", "32-510091", "32-510110", "32-ARER102RS", "32-BSK510006", "32-BSK513057", "32-NSB580541", "32-SC754", "32-UC3C33047B", "330-002", "341117SFT", "38342-D2100J", "40-03C-121-118D", "40-1336W3", "40-1600B02E10AC", "40-1J0422371C", "40-1J0955453P", "40-21510-2B040", "40-22448-2Y001J", "40-226A0-WL000", "40-24578498", "40-25182733", "40-25260-ZH30B", "40-25411-0X000K", "40-25412-0X000K", "40-2554040U60", "40-25560-VW085", "40-255601Z200", "40-26611-26000", "40-30520-59B013", "40-5S6G6M293AK", "40-67136", "40-90180529", "40-90916-03129", "40-93235615", "40-93360-02000", "40-95218007", "40-9647265980", "40-98320-02000", "40-AL23039", "40-BAF1301806", "40-BAF1301FA185", "40-BAF1301FA375", "40-BAF1301FA93", "40-BHFD13", "40-BOB32330002", "40-BP5HS-ECO", "40-CAR21130001", "40-ENCAR21100001", "40-ENCDI3101004N", "40-ENFIL26050101", "40-ENFOC3215007", "40-ENMAJ1111001D", "40-ENMAN11100004", "40-ENREG31020006", "40-FH3-100W-X", "40-FOLM3CR", "40-FOLM3CW", "40-IC107", "40-IC63VM", "40-L92Y-ECO", "40-LAFRH5B", "40-LAFRH5G", "40-LAFRH5PK", "40-LAFRH5Y", "40-MK1090", "40-MK1115", "40-MK2045", "40-MK2095", "40-MK3120", "40-MK7030", "40-MKN035", "40-MKN060", "40014-01G50SFT", "40015-01G50SFT", "41-C013", "41-C015", "41-C016", "41-C020", "41-C021", "41-C023", "41-C026", "41-C033", "41-C459", "41-C462", "41-C466", "41-C470", "41-C473", "41-C476", "41-C482", "41-C486", "41-C496", "41-C498", "41-C505", "41-C512", "41-C514", "41-C515", "41-C517", "41-C523", "41-C526", "41-C527", "41-C528", "41-C543", "41-C546", "41-C554", "41-C56", "41-C57", "41-C60", "41-D024", "41-Z075", "42431-33130", "45-CFI10559", "4677459ADBC", "47-645", "47-SCN1P", "47-SPA5", "5072022SFT", "52088710ADSFT", "547959481-A", "554293SFT", "56-FR8M", "56-HR9BP", "56-YR7DC", "58-BR60013RP", "6Q0-823-359", "718-3700925", "718-46010-3SG1B", "721-5662", "721-74-45", "721-T149107", "721-T25505", "721-T25568", "721-T25618A", "721-T8121", "721-T8505", "721-TM101", "721-TM58", "7272-D383", "728-ECO127", "728-ECO129", "733-032121121A", "738-40227-50Y01J", "738-90311-25021J", "738-90311-30014J", "738-90311-35040J", "738-90311-48020J", "738-90311-75016J", "738-J2213-225705", "738-J4023-250Y00", "738-J9091-302112", "738-LF0110602J", "740-13286445", "740-391000", "740-391005", "740-391006", "740-391363", "740-4625A437", "740-47550-0K010", "740-6R1611019A", "740-BMCCR03", "740-BMCFD07", "740-D7210-5RL1A", "740-W54105", "740-WCCR12", "742-OS53", "742-OSH3100", "748-VAINILLA", "750-510018010", "758-D1408005", "758-D1408017", "758-D2106068", "758-D357413175A", "758-D5511064C00", "758-D7000407", "758-D7701047415", "758-DES3423", "758-ES140L", "760-138127", "760-141439", "760-143204", "760-143403", "760-143420", "760-143986", "760-BRK214", "760-ST405", "761-IC1101", "761-IC1128", "761-IC123", "761-IC206", "761-IC301", "761-IC31", "761-IC360", "761-IC402", "761-IC44", "761-IC654", "761-IC71", "761-IM325", "761-RA22", "761-RH115", "761-RH116", "761-RHA118", "761-TB54", "761-TB60", "761-TH77", "761-THA117", "761-TT96", "769-46210-ET82A", "769-BHCR380309", "769-BHCR380329", "769-BHCR380342", "769-BHCR380343", "769-BHCR381160", "769-BHCR38636", "769-BHFD05", "769-BHFD11", "769-BHFD16", "769-BHFD17", "769-BHFD380299", "769-BHFD380310", "769-BHFD380323", "769-BHFD380325", "769-BHFD380355", "769-BHFD380356", "769-BHFD380357", "769-BHFD380358", "769-BHFD380530", "769-BHFD381163", "769-BHFD381164", "769-BHFD381165", "769-BHFD381170", "769-BHFD381188", "769-BHFD381263", "769-BHFD38337", "769-BHFD38904", "769-BHGM381624", "769-BHGM383347", "769-BHGM4336", "769-BHGM4367", "769-BHHO383187", "769-BHHO383189", "769-BHHO383190", "769-BHNS380761", "769-BHNS381121", "769-BHPG8013", "769-BHRN7072", "769-BHTY383161", "769-BHTY383162", "769-CI3062", "769-CI3063", "769-NF380318", "769-NF38897", "769-NF4050", "769-NF4068", "769-NF4366", "769-NF6019", "7875SFT", "7D0-698-151BFP", "8444019SFT", "892-TEC70A", "900-HS10", "900-HS36", "900-HS5", "900-HS6", "900-HS8", "90091989-G", "901-026C", "901-408", "903-7223", "905-9079-ECO", "905-99209", "905-AF8243-ECON", "906-AAC408", "906-BC88536", "906-BCMAB1600R", "906-BCMMAXR", "906-FC322", "906-FC6L80", "906-FC6T70", "906-FCA404M", "906-FCA4LDE", "906-FCA4LDE4", "906-FCATX", "906-FCAW81", "906-FCAXOD", "906-FCDPO", "906-FCE40D4W", "906-FCFIOD", "906-FCSENTRA", "906-FCTH440N", "906-GP508", "906-HC72TY", "906-HCDINA", "906-HCE0TZB", "906-HCGM27", "906-HCGM6", "906-JFF171", "906-JFPIYII", "906-JLC350BC", "906-JLD360", "906-JLF110", "906-JLGM22", "906-JLPIV", "906-JLRAMBLER", "906-JLVW20D", "906-PP11", "906-SB3", "907-TH11CR", "907-TH11SR", "907-TH12SR", "907-TH13CP", "907-TH13SP", "907-TH14", "907-TH4", "907-TH9", "908-C403", "908-C426", "908-C431", "908-G750", "908-G754", "908-H2800", "908-N2200", "908-N228", "908-N3201", "908-S903", "911-BP21", "911-PER", "913-02182", "913-02193", "913-03160", "913-03190", "913-03197", "913-08033", "913-10145", "913-20215", "913-2720902", "913-29545", "913-29550", "919-SK4", "919-SK8", "920-1051", "920-1079", "920-1082", "920-1085", "920-1088", "920-1091", "920-1100", "920-1124", "920-1127", "920-1130", "920-1171", "920-1228", "920-1233", "920-1234", "920-2434", "922-AIA9002", "924-RFD030", "924-RFD036", "924-RN044", "924-RT003", "925-EU40247", "926-648", "928-1383314", "928-19101P2A000", "928-23386455", "928-2E0121407", "928-9114661", "928-L32115350", "928-RDFD01", "931-CCE3551", "939-30000D22VKD", "939-CHRSET11K", "939-FDRSET04K", "939-GMRSET19K", "939-VWRSET01K", "941-10228100", "941-10228200", "941-40160-52Y10S", "941-40160-W5000", "941-5425", "941-54500-EB30A", "941-54501-4B000", "941-55120-150A10", "941-5Q0505323C", "941-5Q0505323D", "941-5QF505223C", "941-5QF505224C", "941-5T0501529F", "941-96535274", "942-1005005", "942-1006016", "942-1006033", "942-1008012", "942-1008042", "942-1009011", "942-1106044", "942-1106048", "942-1124005", "942-1124006", "942-1124007", "942-1126010S", "942-1306030", "942-1307001", "942-1308014", "942-1308025", "942-1309005", "942-1309016", "942-1313007", "942-1403014", "942-1403040", "942-1403100", "942-1403109", "942-1403122", "942-1406142", "942-1406143", "942-1408001", "942-1408065", "942-1408144", "942-1416016", "942-1503032", "942-1506046", "942-1506047", "942-1524001", "942-1524002", "942-1524011", "942-1524012", "942-191422804A", "942-1H0419821", "942-2105001", "942-2105007", "942-2106065", "942-2106066", "942-2108039", "942-2108040", "942-2509021", "942-26262064", "942-26262065", "942-2772019", "942-2772031", "942-2784049", "942-2784050", "942-3082061", "942-3082062", "942-3448", "942-4084034", "942-4084083", "942-42420-74P10", "942-43330-09A90", "942-45201-62R00", "942-45530-81P00", "942-48521-EA000", "942-48810-62R01", "942-48820-62R01", "942-5072934", "942-5384008", "942-5384009", "942-54410-4B000", "942-54500-8B525", "942-54668-85000", "942-55120-6LB0BB", "942-561407151A", "942-5640", "942-8425028", "942-ES2054RL", "942-K3134", "942-K6325", "942-K6600", "942-K7084", "942-K80068", "943-45517-26060", "943-ES2262RL", "943-ES3051L", "944-90311-80001", "944-TKTY104A", "944-TKTY104B", "946-58305-4BA20", "946-7430-D551", "946-7694-D859", "946-7849-D950", "946-7973-D1067", "946-8336-D1216C", "946-8410-D1169", "946-8420-D1304", "946-8652-D1334", "946-9201-D1974", "946-9322-D2087", "946-9424-D2179", "946-9509-D2269", "948-13077-5V1NK", "948-TKFD301A", "948-TKFDT206A", "949-13289621", "949-1355A278", "949-17120-69L00", "949-21481-ET000", "949-214814297R", "949-2538005500", "949-25386-0X150B", "949-3861555AZ01", "949-42426778", "949-5U0121205C", "949-68057238AA", "949-95352379A", "949-96526666", "949-96536520", "949-96553242", "949-96629064", "952-39720-EW627", "952-49110-4KV0A", "952-49110-VZ10B", "952-49500-0X110", "952-49500-0X110C", "952-96425091", "952-CH515", "952-E4B13-200AC", "952-RC7055", "952-RC7056", "952-RC7057", "952-RC7059", "952-RC7509", "952-RC7747", "952-RR9227", "952-RR9267", "957-1019", "957-1037", "957-1042", "957-1106R", "957-11220-6LB0A", "957-11220-ET01A", "957-11220-ET10A", "957-11320-01G0A", "957-11320-7Z010", "957-11320-9CA0A", "957-11350-JA00A", "957-11360-6LA0A", "957-1159", "957-1534", "957-1672H", "957-22826284", "957-23954395", "957-2503036", "957-2506030", "957-2525015", "957-3003H", "957-3040", "957-3122H", "957-3151H", "957-3158H", "957-3328", "957-3444", "957-3467", "957-3473", "957-3643", "957-3661", "957-3894", "957-3952", "957-3969", "957-41710-62R50", "957-4234H", "957-4251", "957-4262H", "957-4351", "957-4369", "957-4418H", "957-4425", "957-4457", "957-4522", "957-4609", "957-4611", "957-4629H", "957-4630H", "957-4635", "957-4720", "957-4771", "957-4831H", "957-4917", "957-50890T0AA81", "957-5290", "957-5523", "957-5524", "957-5613", "957-5Q0199262BM", "957-5Q0199855N", "957-7096", "957-7136", "957-7137", "957-7138", "957-7140H", "957-7192", "957-7199", "957-7322R", "957-7439", "957-7786", "957-7787", "957-7851", "957-7989H", "957-9380022", "959-15010-1W900", "959-15010-F450A", "959-15010-VM00C", "961-F0243", "961-FFVW159", "962-1300A045", "962-16100-39466", "962-55599494", "962-PE0115010", "967-43502-AA021", "967-43502-BZ020", "967-512371", "967-512452", "967-512551", "967-512655", "967-513324", "967-513326", "967-513374", "967-515170", "967-51750-H9000", "967-52750-1G001Z", "967-6C111A049BA", "967-90767719", "967-90767720", "967-9645242", "967-WHFD16", "967-WHGM512317", "967-WHMZ512347", "967-WHMZ513212", "967-WHTY512018", "967-WHTY512206", "967-WHTY512207", "967-WHTY512208", "967-WHTY512210", "967-WHTY512213", "967-WHTY512215", "967-WHTY512216", "967-WHTY512280", "967-WHTY513257", "967-WHTY515040", "967-WHTY518509", "968-10722871", "968-1K0615601M", "968-23742602", "968-3501050P3010", "968-40206-6LE0A", "968-42431-0K120", "968-43206-6LA0B", "968-43206-F4601", "968-43511-62R00", "968-43512-0K060", "968-55311-52R50", "968-55611-52R00", "968-580769R", "968-580770R", "968-580875R", "968-581025R", "968-5QN615601A", "968-680027R", "968-780733R", "968-95245601", "968-97841R", "968-97873R", "968-980987R", "968-982048R", "968-982494R", "968-982611R", "968-BDGM31391", "968-BRFD141265", "968-BRGM141488", "968-BRGM141829", "968-BRGM145265", "968-BRGM145282", "968-BRGM145317", "968-BRGM145624", "968-BRNS31058", "968-XM341126BC", "969-1502002", "969-2344251", "969-2344809", "969-2346003L", "969-IK16", "971-10400855", "971-19315698", "971-19315699", "971-2QB513049F", "971-3009051", "971-3430045", "971-3430082", "971-349087", "971-5C5827550", "971-65470-1AA0A", "971-81171B2000", "971-835179", "971-90450-1VK1A", "971-904520004R", "971-EG2163620D", "971-KD7763620A", "971-MP8046", "972-DZ3009", "972-DZ620", "972-DZJ102HA", "972-DZJ102HB", "972-DZKC1003", "972-DZKC108", "972-DZKC109A", "972-DZKC1100", "972-DZKC1103", "972-DZKC202", "972-DZKC202A", "972-DZKC210", "972-DZKC213", "972-DZKC214", "972-DZKC214A", "972-DZKC215", "972-DZKC219", "972-DZKC220", "972-DZKC224", "972-DZKC225", "972-DZKC226", "972-DZKC227", "972-DZKC229", "972-DZKC230", "972-DZKC231", "972-DZKC242", "972-DZKC244", "972-DZKC245", "972-DZKC246", "972-DZKC251", "972-DZKC300", "972-DZKC302", "972-DZKC305", "972-DZKC308", "972-DZKC310", "972-DZKC318", "972-DZKC324", "972-DZKC336", "972-DZKC338", "972-DZKC348", "972-DZKC349", "972-DZKC350", "972-DZKC354", "972-DZKC360", "972-DZKC361", "972-DZKC362", "972-DZKC363", "972-DZKC366", "972-DZKC372", "972-DZKC413", "972-DZKC416", "972-DZKC418", "972-DZKC421", "972-DZKC422", "972-DZKC423", "972-DZKC424", "972-DZKC427", "972-DZKC428", "972-DZKC430", "972-DZKC435", "972-DZKC439", "972-DZKC441", "972-DZKC442", "972-DZKC443", "972-DZKC444", "972-DZKC445", "972-DZKC452", "972-DZKC453", "972-DZKC455", "972-DZKC499", "972-DZKC514", "972-DZKC516", "972-DZKC519", "972-DZKC520", "972-DZKC522", "972-DZKC525", "972-DZKC526", "972-DZKC533", "972-DZKC537", "972-DZKC603A", "972-DZKC604A", "972-DZKC605", "972-DZKC608", "972-DZKC610", "972-DZKC613A", "972-DZKC613B", "972-DZKC613C", "972-DZKC615", "972-DZKC615A", "972-DZKC625", "972-DZKC630", "972-DZKC631", "972-DZKC637", "972-DZKC641", "972-DZKC644", "972-DZKC652", "972-DZKC656", "972-DZKC657", "972-DZKC658", "972-DZKC702", "972-DZKC703", "972-DZKC704", "972-DZKC705", "972-DZKC708", "972-DZKC709", "972-DZKC711", "972-DZKC715", "972-DZKC717", "972-DZKC721", "972-DZKC724", "972-DZKC725", "972-DZKC904", "972-DZKC906", "972-DZKC908A", "972-DZKT101L", "972-DZKT101R", "972-DZKT118R", "972-DZKT126L", "972-DZKT225L", "972-DZKT225R", "972-DZKT301R", "972-DZKT306R", "972-DZKT311L", "972-DZKT311R", "972-DZKT500L", "972-DZKT505L", "972-DZKT505R", "972-DZKT602L", "972-DZKT700L", "972-DZKT700R", "972-DZKT701R", "972-DZKT702L", "972-DZKT702R", "972-RWCR401", "972-RWGM265", "974-623360000", "974-CHRSET06CE", "974-CHRSET14CE", "974-FDRSET22CE", "974-GMRSET25CE", "974-PGRSET03CE", "974-VWRSET17CE", "976-5M2747A050", "978-975130", "980601RFP", "AA037", "AA053-2", "AA053-6", "AA060-3", "AA060-4", "AA061-1", "AA061-3", "AA064", "AA070", "AA071", "AA084", "AA090", "AA090-3", "AA091", "AA092", "AA093", "AA102", "AA103", "AA107", "AA108", "AA531", "AA536", "AA551", "AA552", "AA556", "AA563", "AA601", "AA602", "AA604", "AA608", "AA609", "AA612", "AA616", "AA660", "AA675", "AA726", "AA739", "AA742", "AA750", "AL-11008", "AL-11343", "BB022", "BB040", "BB046-3", "BB046-7", "BB085", "BB089", "BB206", "CB-200", "CC065-1", "CC176", "CC218-100NEGRO", "CC218-100ROJO", "CC321", "CC324", "CC367", "CC370", "CC414", "CC421", "CC913", "CC927", "CC932", "CFI-7850", "DBNC1700", "E11030043", "EE200", "EE201", "EMP-6505-105", "EU-42702", "FFNN0151", "FMC-1931", "GG034", "L-3140", "LL847R", "LL850R", "LL857", "LL865", "LL868", "MM003", "MM271", "N510001410", "NKLZTR4AIX-11", "PLZKBR7B8G", "PP009", "PP015", "PP018-1", "PP072", "PP100", "PP215", "PP227", "PP327", "PP328", "PP724", "PP7980", "PP7987", "PP7988", "PP843R", "PP857", "PP904AL", "PP904AR", "PP906", "RC-63217", "RD-CR06", "RES-1319-0010", "RR024-3", "RR049", "RR200", "SILZKBR8D8S", "SS008", "SS030", "SS032", "SS033", "SS036", "SS037", "SS037-1", "SS038", "SS040", "SS040-3", "SS042", "SS049", "SS050", "SS054-2", "SS054-3", "SS055-8", "SS057", "SS068", "SS092", "SS094", "SS1261", "SS1511", "SS16100", "SS16109A", "SS16131", "SS16139", "SS16140", "SS16142", "SS16143", "SS16145", "SS16147", "SS16148", "SS16176", "SS16227", "SS16242", "SS16243", "SS16250", "SS16252", "SS16254", "SS16271", "SS1658A", "SS1659", "SS1664", "SS1665", "SS1666", "SS1669", "SS1674", "SS1676", "SS1677", "SS1680", "SS1681", "SS1682", "SS1683", "SS1688", "SS1691", "SS1692", "SS1694", "SS1697", "SS1699", "SS2704", "SS290", "SS304", "SS3106", "SS340", "SS405", "SS503", "SS709", "TM-546", "TT012-1", "TT024-1", "TT054", "TT1189", "TT139", "TT151-5", "TT151-9", "TT182", "TT187", "TT187-1", "TT199", "TT218-9", "TT220", "TT285", "TT286", "TT3031A", "TT404", "TT409", "TT410", "TT417", "TT420", "TT421", "TT431", "TT432", "TT536", "TT558", "TT563", "TT566", "TT574", "TT605", "TT612", "TT701", "TT990", "VA-CR01", "VE-CR01", "VV007", "VWA030", "VWB013", "VWB024-1", "VWB042", "VWB108", "VWB240", "VWC045", "VWE014D", "VWG005", "VWJ012", "VWM001", "VWM002", "VWP045C", "VWS106", "VWT023", "VWT064", "VWT065", "VWT066", "VWT069-2", "VWT095", "VWT117", "VWT136", "VWT137", "VWT214", "VWZ005", "WP-FD11P", "01-MU41404", "02-PQ17520", "07-544307D-LS", "07-7926-D1021-LS", "120-053", "120-077", "15-PULF028", "15-PULVW063", "17-055", "17-286", "40-80671-3XA0BC", "40-ARE237", "40-ARE425TA", "40-F9004X 100W", "40-L381116", "40-MK204043", "40-MK24VT24587", "40-MK24VT25682", "41-C014", "41-C453", "41-C552", "41-J1815", "718-24104708", "728-ECO107D", "740-1J2721388A", "740-WCGM09", "746-54618-01G10", "746-FG260", "758-D2108030", "769-NF4040", "769-NF4062", "890-811407181A", "905-3660-ECON", "905-6600", "905-7774-ECON", "905-8602-ECON", "905-FA3384-ECON", "905-VW2-ECON", "906-JFGM173", "907-TH15", "908-C435", "908-F602", "908-N227", "913-03150", "913-26180", "913-26550", "920-1002", "924-RN059", "939-3000040P00K", "939-30000T1000K", "941-1403059", "942-1006002", "942-1103021", "942-1108020", "942-1109005", "942-1109006", "942-1109018", "942-1109019", "942-1119001", "942-1119002", "942-1408007", "942-2612475", "942-4626766", "942-ES3254RL", "942-ES3579", "942-K3128", "942-K3147A", "942-K7206", "942-K8734", "943-K3196", "943-K9210", "946-41060-AX61F", "946-7Y35-F278", "947-OF5919", "948-TKNS104S", "957-1943", "957-2925051", "957-3019", "957-7012", "957-7020", "957-7113", "957-7114", "957-7878", "971-68950-26065", "972-DZKC626", "972-DZKC710", "972-DZKC714", "972-DZKC800", "972-DZKC801", "972-DZKT320R", "AA090-1", "AA110", "AA112", "AA541", "AA548", "AA558", "AA947", "CC042", "CC074", "CC137", "CC965", "LL847", "MM008", "PP018-4", "PP018-5", "PP224", "PP5524", "PP7982", "RR004", "RR005", "RR251", "SS055-9", "SS1693", "SS1695", "SS286", "SS406", "SS7808", "TT565", "TT613", "TT868B", "VV035A", "VWB017", "VWT502", "VWZ003"];

        $no_existe = true;

        while ($no_existe) {
            # code...
            $clave = $data[array_rand($data)];
            $producto = \DB::connection('mysql')->table('productos_busqueda')->select('*')->where('codigo_nikko', $clave)->first();
            if ($producto)
                $no_existe = false;
        }


        return view('pantallas.liquidacion', compact('producto'));
    }


    public function facturaSAE(Request $request)
    {
        extract($request->all());

        $regimen = [
            '601' => 'General de Ley Personas Morales',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => 'Arrendamiento',
            '607' => 'Régimen de Enajenación o Adquisición de Bienes',
            '608' => 'Demás ingresos',
            '610' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
            '611' => 'Ingresos por Dividendos (socios y accionistas)',
            '612' => 'Personas Físicas con Actividades Empresariales y Profesionales',
            '614' => 'Ingresos por intereses',
            '615' => 'Régimen de los ingresos por obtención de premios',
            '616' => 'Sin obligaciones fiscales',
            '620' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            '621' => 'Incorporación Fiscal',
            '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => 'Opcional para Grupos de Sociedades',
            '624' => 'Coordinados',
            '625' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => 'Régimen Simplificado de Confianza'
        ];
        $forma_pago = [
            '01' => 'Efectivo',
            '02' => 'Cheque nominativo',
            '03' => 'Transferencia electrónica de fondos',
            '04' => 'Tarjeta de crédito',
            '05' => 'Monedero electrónico',
            '06' => 'Dinero electrónico',
            '08' => 'Vales de despensa',
            '12' => 'Dación en pago',
            '13' => 'Pago por subrogación',
            '14' => 'Pago por consignación',
            '15' => 'Condonación',
            '17' => 'Compensación',
            '23' => 'Novación',
            '24' => 'Confusión',
            '25' => 'Remisión de deuda',
            '26' => 'Prescripción o caducidad',
            '27' => 'A satisfacción del acreedor',
            '28' => 'Tarjeta de débito',
            '29' => 'Tarjeta de servicios',
            '30' => 'Aplicación de anticipos',
            '31' => 'Intermediario pagos',
            '99' => 'Por definir'
        ];

        $metodo_pago = [
            'PUE' => 'Pago en una sola exhibición',
            'PPD' => 'Pago en parcialidades o diferido',
        ];

        $uso_cfdi = [
            'G01' => 'Adquisición de mercancías.',
            'G02' => 'Devoluciones, descuentos o bonificaciones.',
            'G03' => 'Gastos en general.',
            'I01' => 'Construcciones.',
            'I02' => 'Mobiliario y equipo de oficina por inversiones.',
            'I03' => 'Equipo de transporte.',
            'I04' => 'Equipo de computo y accesorios.',
            'I05' => 'Dados, troqueles, moldes, matrices y herramental.',
            'I06' => 'Comunicaciones telefónicas.',
            'I07' => 'Comunicaciones satelitales.',
            'I08' => 'Otra maquinaria y equipo.',
            'D01' => 'Honorarios médicos, dentales y gastos hospitalarios.',
            'D02' => 'Gastos médicos por incapacidad o discapacidad.',
            'D03' => 'Gastos funerales.',
            'D04' => 'Donativos.',
            'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).',
            'D06' => 'Aportaciones voluntarias al SAR.',
            'D07' => 'Primas por seguros de gastos médicos.',
            'D08' => 'Gastos de transportación escolar obligatoria.',
            'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.',
            'D10' => 'Pagos por servicios educativos (colegiaturas).',
            'S01' => 'Sin efectos fiscales.',
            'CP01' => 'Pagos',
            'CN01' => 'Nómina',
        ];


        $titulo = "Pedidos";
        //$pedido = PedidoWeb::find($id_pedido);
        $titulo = "Ver factura";

        $cliente['clave'] = \Auth::user()->clave_cliente;
        $cliente['nombre'] = \Auth::user()->name;

        /*if($pedido->cliente != \Auth::user()->clave_cliente)
            return redirect()->route('tienda_online.dashboard');*/



        $url = 'https://sistemasowari.com:8443/catalowari/api/facturas?' . http_build_query(["pedido" => $id_pedido]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $xmls = json_decode($data, true);




        $mensaje = "Aun no hay facturas generadas, revisalo mas tarde.";
        $facturas = [];
        if ($xmls['code'] == 1) {
            foreach ($xmls['archivos'] as $key => $value) {

                if ($value['cliente'] != \Auth::user()->clave_cliente)
                    return redirect()->route('tienda_online.dashboard');

                $url = 'https://sistemasowari.com:8443/catalowari/api/partidas_factura?' . http_build_query(["factura" => $value['factura']]);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                $partidas_sae = json_decode($data, true);

                $xmlStr = $value['xml'];

                libxml_use_internal_errors(true);
                $sxe = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_COMPACT);
                if ($sxe === false) {
                    die("XML inválido: " . implode("; ", array_map(fn($e) => $e->message, libxml_get_errors())));
                }
                $cfdi = $this->sxeToArray($sxe, true);
                $path = storage_path('app/facturas/' . $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] . '.pdf');   // dentro de storage/app
                if (!\File::exists($path)) {
                    $data = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=' . $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] . '&re=LOCI780206BK1&rr=' . $cfdi['Receptor']['@attributes']['Rfc'];
                    $qr = base64_encode(\QrCode::format('png')->generate($data));
                    $pdf = PDF::loadView('pdf.factura', compact("cfdi", "cliente", "regimen", "forma_pago", "metodo_pago", "uso_cfdi", "qr", "partidas_sae"));
                    $pdf->save(storage_path('app/facturas/' . $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] . '.pdf'));

                    $sxe->asXML(storage_path('app/facturas/' . $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] . '.xml'));

                }
                array_push($facturas, ['uuid' => $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'], 'factura' => $value['factura']]);
            }
            $mensaje = "Factura encontrada para el pedido " . $id_pedido . ": ";
            if (count($xmls['archivos']) > 1)
                $mensaje = "Facturas encontradas para el pedido :" . $id_pedido;

        }

        //dd($facturas);

        return view('tienda_online.factura_sae', compact('id_pedido', 'titulo', 'facturas', 'mensaje'));
    }

    public function verPdf($uuid)
    {
        $path = storage_path('app/facturas/' . $uuid . '.pdf');
        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $uuid . '"',
        ]);
    }

    public function downloadZip(string $uuid)
    {
        $pdf = storage_path('app/facturas/' . $uuid . '.pdf');
        $xml = storage_path('app/facturas/' . $uuid . '.xml');
        abort_unless(is_file($pdf) && is_file($xml), 404);

        $zipPath = tempnam(sys_get_temp_dir(), 'cfdi_') . ".zip";
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'No se pudo crear el ZIP');
        }
        $zip->addFile($pdf, "CFDI-$uuid.pdf");
        $zip->addFile($xml, "CFDI-$uuid.xml");
        $zip->close();

        return response()->download($zipPath, "CFDI-$uuid.zip")
            ->deleteFileAfterSend(true);
    }


    private function sxeToArray(SimpleXMLElement $sxe, bool $stripNS = true)
    {
        $result = [];

        // 1) Atributos sin y con namespace
        $attrs = $sxe->attributes();
        if ($attrs && count($attrs)) {
            foreach ($attrs as $k => $v) {
                $result['@attributes'][$k] = (string) $v;
            }
        }
        foreach ($sxe->getNamespaces(true) as $pfx => $uri) {
            foreach ($sxe->attributes($pfx, true) as $k => $v) {
                $key = $stripNS ? $k : ($pfx ? "$pfx:$k" : $k);
                $result['@attributes'][$key] = (string) $v;
            }
        }

        // 2) Hijos (todas las namespaces + sin namespace)
        $namespaces = $sxe->getNamespaces(true);
        $prefixes = array_merge(['' => null], $namespaces); // '' = sin NS

        $hasChildren = false;
        foreach (array_keys($prefixes) as $pfx) {
            $children = $pfx === '' ? $sxe->children() : $sxe->children($pfx, true);
            foreach ($children as $name => $child) {
                $hasChildren = true;
                $key = $stripNS || $pfx === '' ? $name : "$pfx:$name";
                $value = $this->sxeToArray($child, $stripNS);

                // Si la clave ya existe, convierte a arreglo y agrega
                if (array_key_exists($key, $result)) {
                    if (!is_array($result[$key]) || !array_is_list($result[$key])) {
                        $result[$key] = [$result[$key]];
                    }
                    $result[$key][] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        // 3) Si no hay hijos ni atributos, devuelve el texto
        if (!$hasChildren && empty($result['@attributes'])) {
            $text = trim((string) $sxe);
            return $text === '' ? null : $text;
        }

        // 4) Si hay texto además de hijos/atributos, guárdalo
        $text = trim((string) $sxe);
        if ($text !== '' && $hasChildren) {
            $result['@value'] = $text;
        }

        return $result;
    }


    public function carritoAux()
    {
        $titulo = "Carrito";
        $premio = "PROMOCIONAL";
        $productos = [];
        if (\Session::has('cart')) {
            $carrito = \Session::get('cart');

            $existe_premio_carrito = false;
            foreach ($carrito as $key => $value) {
                // code...
                if ($value['numero_parte'] == $premio)
                    $existe_premio_carrito = true;
            }

            if ($existe_premio_carrito && count($carrito) == 1) {
                \Session::put('cart', []);
                $carrito = [];
            }



            if (count($carrito) > 0 && !$existe_premio_carrito) {

                $premio_partida = PedidoPartida::join('pedidos_web', 'pedidos_partidas.id_pedido', '=', 'pedidos_web.id')->where('pedidos_partidas.clave', $premio)->where('pedidos_web.cliente', \Auth::user()->clave_cliente)->where('pedidos_web.deleted_at', null)->first();

                if (!$premio_partida) {
                    $url = 'https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto?' . http_build_query(["clave" => $premio, "cliente" => \Auth::user()->clave_cliente, 'tipo' => 'factura']);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $producto = json_decode($data, true);

                    if ($producto['existencia'] > 0)
                        array_push($carrito, ['numero_parte' => $premio, 'cantidad' => 1, 'partida' => $producto, 'sustituto' => false]);
                }

            }


            $productos = ProductoBusqueda::whereIn('codigo_nikko', array_column($carrito, 'numero_parte'))->get()->toArray();
            $productos = array_intersect_key($productos, array_unique(array_column($productos, 'codigo_nikko')));
            foreach ($productos as $key => $value) {

                $url = 'https://sistemasowari.com:8443/catalowari/api/producto-existencia?' . http_build_query(["clave" => $value['codigo_nikko']]);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                $existencias_reales = json_decode($data, true);








                $partes = ProductoBusqueda::where('codigo_nikko', $value['codigo_nikko'])->get();
                $motores = "";
                foreach ($partes as $parte) {
                    # code...
                    $motores .= $parte->armadora . " " . $parte->modelo . " " . $parte->ano_inicio . "-" . $parte->ano_final . " " . $parte->cilindros . "CIL " . $parte->motor . "L<br>";
                }
                $productos[$key]['motores'] = $motores;
                foreach ($carrito as $llave => $valor) {
                    if ($valor['numero_parte'] == $value['codigo_nikko']) {

                        $productos[$key]['mensaje_existencia'] = '';
                        $productos[$key]['solicitado'] = $valor['cantidad'];
                        $productos[$key]['solicitado_original'] = $valor['cantidad'];

                        if ($existencias_reales['existencia'] <= 0) {
                            $productos[$key]['existencia_real'] = 0;
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia de este producto. <br> Solicitaste ' . $valor['cantidad'];
                            $productos[$key]['solicitado'] = 0;
                        }


                        if ($existencias_reales['existencia'] < $valor['cantidad']) {
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia completa de este producto. De ' . $valor['cantidad'] . ' paso a ' . $existencias_reales['existencia'];
                            $productos[$key]['solicitado'] = $existencias_reales['existencia'];
                        }




                        $productos[$key]['partida'] = $valor['partida'];
                        if (isset($valor['sustituto']))
                            $productos[$key]['sustituto'] = $valor['sustituto'];
                        else
                            $productos[$key]['sustituto'] = "false";


                        if (isset($valor['negociado']))
                            $productos[$key]['negociado'] = $valor['negociado'];
                        break;
                    }
                }
            }

            $llave_final = count($productos);
            foreach ($carrito as $llave => $valor) {
                if ($valor['numero_parte'] == $premio) {

                    $productos[$llave_final]['codigo_nikko'] = $premio;
                    $productos[$llave_final]['descripcion_1'] = $premio;
                    $productos[$llave_final]['marca_comercial'] = $premio;
                    $productos[$llave_final]['solicitado'] = $valor['cantidad'];
                    $productos[$llave_final]['partida'] = $valor['partida'];
                    $productos[$llave_final]['sustituto'] = "false";
                    $productos[$llave_final]['codigo_nikko'] = $premio;
                    break;
                }
            }


        }

        $productos_especiales = [];
        if (\Session::has('cartEspecial')) {
            $carrito = \Session::get('cartEspecial');
            $productos_especiales = ProductoBusqueda::whereIn('codigo_nikko', array_column($carrito, 'numero_parte'))->get()->toArray();
            $productos_especiales = array_intersect_key($productos_especiales, array_unique(array_column($productos_especiales, 'codigo_nikko')));
            foreach ($productos_especiales as $key => $value) {
                $partes = ProductoBusqueda::where('codigo_nikko', $value['codigo_nikko'])->get();
                $motores = "";
                foreach ($partes as $parte) {
                    # code...
                    $motores .= $parte->armadora . " " . $parte->modelo . " " . $parte->ano_inicio . "-" . $parte->ano_final . " " . $parte->cilindros . "CIL " . $parte->motor . "L<br>";
                }
                $productos_especiales[$key]['motores'] = $motores;
                foreach ($carrito as $llave => $valor) {
                    if ($valor['numero_parte'] == $value['codigo_nikko']) {
                        $productos_especiales[$key]['solicitado'] = $valor['cantidad'];
                        $productos_especiales[$key]['partida'] = $valor['partida'];
                        if (isset($valor['sustituto']))
                            $productos_especiales[$key]['sustituto'] = $valor['sustituto'];
                        else
                            $productos_especiales[$key]['sustituto'] = "false";


                        if (isset($valor['negociado']))
                            $productos_especiales[$key]['negociado'] = $valor['negociado'];
                        break;
                    }
                }
            }
        }

        $estampa = date("YmdHis");

        return view('tienda_online.carrito_aux', compact('productos', 'estampa', 'titulo', 'productos_especiales'));
    }

    public function generarCatalogo(Request $request)
    {
        $query = "WITH ranked AS (
        SELECT
            codigo_nikko, descripcion_1, marca_comercial, precio_normal, especial, id,
            ROW_NUMBER() OVER (PARTITION BY codigo_nikko ORDER BY id) AS rn
        FROM productos_busqueda
        )
        SELECT marca_comercial as MARCA, codigo_nikko as CLAVE, descripcion_1 as DESCRIPCION,  ROUND(precio_normal,2) as PRECIO, especial as ESPECIAL
        FROM ranked
        WHERE rn = 1 ORDER BY descripcion_1;";

        $rows = collect(\DB::connection('mysql')->select($query))->map(fn($o) => (array) $o)->all();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fwrite($out, "\xEF\xBB\xBF");
            // encabezados si es asociativo
            if (!empty($rows) && array_keys($rows[0]) !== range(0, count($rows[0]) - 1)) {
                fputcsv($out, array_keys($rows[0]));
            }
            foreach ($rows as $r)
                fputcsv($out, array_values($r));
            fclose($out);
        }, 'catalogo.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);


    }

    public function guardadoPendienteExitoso(Request $r)
    {
        extract($r->all());

        session()->forget(['cart', 'cartEspecial']); // o ['cart', 'cart.items', 'cart_count']
        session()->save();

        $titulo = "<br>
        Nos comunicaremos contigo para darle seguimiento.
        Favor de estar al pendiente del numero de telefono que nos proporcionaste.<br><br>";
        return view('tienda_online.exito_pendiente', compact('titulo', 'id_pedido'));
    }

    public function carritoSesion()
    {
        $carrito = \Session::get('cart');

        echo "<table><tr><th>CLAVE</th><th>CANTIDAD</th></tr>";
        if ($carrito) {
            foreach ($carrito as $key => $value) {
                echo "<tr><td>" . $value['numero_parte'] . "</td><td>" . $value['cantidad'] . "</td></tr>";
            }
        }
        echo "</table>";
    }



}
