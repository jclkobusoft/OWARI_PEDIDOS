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
    // Cache de proveedores_especiales (SOMA) por request — evita N queries
    // cuando se renderizan muchos productos del carrito en un solo controller.
    private $proveedoresEspecialesCache = null;

    public function __construct()
    {
        $general = DatosGenerales::find(1);
        \View::share('general', $general);
    }

    /**
     * Lee la tabla `proveedores_especiales` de SOMA y devuelve un mapa
     * indexado por clave (S227 → {tipo_separacion, stock_ficticio, ...}).
     * Es la fuente UNICA — nunca hardcodear claves de proveedor en este controller.
     * Si SOMA esta caido o la tabla no responde, devuelve [] (degradacion segura:
     * los productos se tratan como normales, sin +stock_ficticio).
     */
    private function obtenerProveedoresEspeciales(): array
    {
        if ($this->proveedoresEspecialesCache !== null) {
            return $this->proveedoresEspecialesCache;
        }
        try {
            $rows = \DB::connection('owari_soma')
                ->table('proveedores_especiales')
                ->where('activo', true)
                ->get(['clave', 'tipo_separacion', 'stock_ficticio']);
            $this->proveedoresEspecialesCache = [];
            foreach ($rows as $r) {
                $this->proveedoresEspecialesCache[$r->clave] = $r;
            }
        } catch (\Throwable $e) {
            \Log::warning('obtenerProveedoresEspeciales fallo: ' . $e->getMessage());
            $this->proveedoresEspecialesCache = [];
        }
        return $this->proveedoresEspecialesCache;
    }

    /**
     * Stock ficticio que se suma al stock real para mostrar al cliente.
     * Solo aplica para proveedores con tipo_separacion='split_por_stock'.
     */
    private function obtenerStockFicticio(?string $claveProveedor): int
    {
        if (empty($claveProveedor)) return 0;
        $cfg = $this->obtenerProveedoresEspeciales()[$claveProveedor] ?? null;
        if (!$cfg || $cfg->tipo_separacion !== 'split_por_stock') return 0;
        return (int) $cfg->stock_ficticio;
    }

    /**
     * Mapa [clave_proveedor => stock_ficticio] de los proveedores
     * 'split_por_stock'. Se pasa a las vistas de listados (productos,
     * favoritos, liquidacion) para sumar el stock ficticio de forma
     * data-driven, sin hardcodear la clave (S227) ni la cantidad (+2).
     */
    private function mapaStockFicticio(): array
    {
        $mapa = [];
        foreach ($this->obtenerProveedoresEspeciales() as $clave => $cfg) {
            if (($cfg->tipo_separacion ?? null) === 'split_por_stock') {
                $mapa[$clave] = (int) $cfg->stock_ficticio;
            }
        }
        return $mapa;
    }


    /**
     * Mapa [clave_producto => existencia] del stock que vive en la plataforma de un
     * proveedor externo (KIMS, etc.), que SOMA mantiene sincronizado en
     * `productos_stock_externo`.
     *
     * OJO — este stock NO es nuestro: la mercancia esta con el proveedor. Por eso:
     *   - NUNCA se suma a `existencia_sae`: esa parte es la que se factura/remisiona
     *     desde nuestro almacen. Sumarlo haria que SAE intente facturar piezas que no
     *     tenemos y rechace la partida.
     *   - Va SIEMPRE a pedido especial, que ya es lo que ocurre con estos productos
     *     porque no existen en SAE (obj.cliente == "N/A").
     * Sirve para dos cosas: mostrarle disponibilidad al cliente y TOPAR cuanto puede
     * pedir (no se puede comprometer mas de lo que el proveedor tiene).
     *
     * Una sola query para todas las claves de la pagina (no una por producto).
     * Si SOMA no responde devuelve [] y la tienda sigue funcionando igual que hoy.
     */
    private function mapaStockExterno(array $claves): array
    {
        $claves = array_values(array_unique(array_filter(array_map('trim', $claves))));
        if (empty($claves)) return [];

        try {
            $rows = \DB::connection('owari_soma')
                ->table('productos_stock_externo as pse')
                ->join('productos as p', 'p.id', '=', 'pse.id_producto')
                ->whereNull('pse.deleted_at')
                ->whereNull('p.deleted_at')
                ->where('pse.estado', 'ok')
                ->where('pse.existencia', '>', 0)
                ->whereIn('p.clave', $claves)
                ->get(['p.clave', 'pse.existencia']);

            $mapa = [];
            foreach ($rows as $r) {
                // Si un producto estuviera en dos plataformas, se acumulan.
                $mapa[$r->clave] = ($mapa[$r->clave] ?? 0) + (int) $r->existencia;
            }
            return $mapa;
        } catch (\Throwable $e) {
            \Log::warning('mapaStockExterno fallo: ' . $e->getMessage());
            return [];
        }
    }

    /** Acceso al carrito persistido en BD (tabla carrito_items, por cliente). */
    private function carritoSvc(): \App\Services\CarritoService
    {
        return new \App\Services\CarritoService();
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
            'cliente' => config('services.tienda.clave_cliente_registro')
        ]);

        \Mail::send('emails.registro', compact('registrado'), function ($message) {
            $message->from('tiendaonline@owari.com.mx', 'OWARI Tienda Online');
            $message->subject("Registro de cliente");
            $message->to(['direccion@owari.com.mx', 'sistemas@owari.com.mx']);
        });

        //nuevo codigo

        $cliente = User::create([
            'name' => $nombre,
            'email' => $email,
            'password' => \Hash::make($password),
            'cliente' => true,
            'clave_cliente' => config('services.tienda.clave_cliente_registro')
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

    // ---------------------------------------------------------------------
    //  Recuperacion de contraseña por correo (solo clientes de la tienda)
    // ---------------------------------------------------------------------

    /** Formulario para solicitar el enlace de recuperacion (pide el correo). */
    public function mostrarSolicitudReset()
    {
        $titulo = "Recuperar contraseña";
        return view('tienda_online.password.solicitar', compact('titulo'));
    }

    /**
     * Envia el correo con el enlace de restablecimiento. Se restringe a
     * cuentas de cliente (cliente = true) para que el flujo de la tienda no
     * afecte a usuarios internos. Siempre respondemos con el mismo mensaje
     * para no revelar si un correo existe o no (evita enumeracion).
     */
    public function enviarLinkReset(Request $request)
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email'    => 'El correo electrónico no es válido.',
        ]);

        \Illuminate\Support\Facades\Password::broker()->sendResetLink([
            'email'   => $request->email,
            'cliente' => true,
        ]);

        \Session::flash('status', 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada y la carpeta de spam.');
        return redirect()->route('tienda_online.password.solicitar');
    }

    /** Formulario para capturar la nueva contraseña (token en la URL). */
    public function mostrarFormReset(Request $request, $token)
    {
        $titulo = "Restablecer contraseña";
        $email  = $request->query('email', '');
        return view('tienda_online.password.restablecer', compact('titulo', 'token', 'email'));
    }

    /** Aplica la nueva contraseña validando el token del correo. */
    public function restablecerPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required'  => 'Escribe tu nueva contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            [
                'email'                 => $request->email,
                'password'              => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token'                 => $request->token,
                'cliente'               => true,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password'            => \Hash::make($password),
                    'password_changed_at' => now(),
                    'remember_token'      => \Illuminate\Support\Str::random(60),
                ])->save();
            }
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            \Session::flash('status', 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.');
            return redirect()->route('tienda_online.login');
        }

        \Session::flash('message', 'El enlace no es válido o ya expiró. Solicita uno nuevo.');
        return redirect()->route('tienda_online.password.solicitar');
    }


    /**
     * Pantalla informativa que ve el cliente cuando su cuenta esta suspendida.
     * El middleware VerificarCuentaSuspendida lo trae aqui desde cualquier
     * intento de acceder al resto de la tienda en linea.
     */
    public function cuentaSuspendida()
    {
        $titulo = "Carrito suspendido";
        return view('tienda_online.cuenta_suspendida', compact('titulo'));
    }

    public function dashboard()
    {
        $titulo = "Dashboard";
        // Flag para mostrar el modal de invitacion a cambiar la contraseña
        // inicial. Si el cliente ya la cambio alguna vez (password_changed_at)
        // no aparece. Usuarios admin (cliente=false) tampoco.
        $debeCambiarPassword = \Auth::check()
            && \Auth::user()->cliente
            && is_null(\Auth::user()->password_changed_at);

        // Flyers vigentes (modal promocional), traidos en vivo desde SOMA.
        $flyers = $this->flyersVigentes();

        return view('tienda_online.dashboard', compact('titulo', 'debeCambiarPassword', 'flyers'));
    }

    /**
     * Trae los flyers vigentes desde SOMA para mostrarlos en el modal del dashboard.
     * SIN cache: se consulta en vivo en cada carga para que un flyer nuevo aparezca
     * de inmediato. Timeout corto para no frenar el dashboard si SOMA tarda. Si SOMA
     * no responde, devuelve [] y el dashboard carga normal.
     */
    private function flyersVigentes()
    {
        $apiUrl = rtrim(config('services.somma.api_url'), '/');    // .../somma/v2.0
        $base   = preg_replace('#/somma/v[0-9.]+$#', '', $apiUrl); // host base para imagenes relativas
        try {
            $ch = curl_init($apiUrl . '/flyers/vigentes');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);

            $data  = json_decode($resp, true);
            $lista = (is_array($data) && isset($data['flyers'])) ? $data['flyers'] : [];

            return array_map(function ($f) use ($base) {
                $url = isset($f['url']) ? (string) $f['url'] : '';
                if ($url !== '' && strpos($url, 'http') !== 0) {
                    $url = $base . '/' . ltrim($url, '/');
                }
                $f['url'] = $url;
                return $f;
            }, array_values(array_filter($lista, function ($f) {
                return !empty($f['url']);
            })));
        } catch (\Throwable $e) {
            return [];
        }
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
        $tipo_busqueda = "";
        $q_busqueda = $q;
        if (isset($c)) {
            $tipo_busqueda = 'categoria';
            $busqueda = "Resultados por CATEGORIA: " . $q;
            $peticion = "?q=" . $q . "&c=" . $c . "&p=";
        } else if (isset($a)) {
            $tipo_busqueda = 'autocompletar';
            $busqueda = "Resultados para: " . $q;
            $peticion = "?q=" . $q . "&a=" . $a . "&p=";
        } else if (isset($f)) {
            $tipo_busqueda = 'filtro';
            $busqueda = "Resultados para busqueda por filtrado";
            $q_busqueda = [
                "ano" => $ano,
                "marca" => $marca,
                "modelo" => $modelo,
                "motor" => $motor,
                "grupo" => $grupo,
                "familia" => $familia,
            ];
            $peticion = "?f=1&ano=" . $ano . "&marca=" . $marca . "&modelo=" . $modelo . "&motor=" . $motor . "&grupo=" . $grupo . "&familia=" . $familia . "&p=";
        } else if ($q == "") {
            $tipo_busqueda = 'todos';
            $busqueda = "Todos los productos";
            $peticion = "?q=" . $q . "&p=";
        } else {
            if ($q == "lo_mas_nuevo") {
                $tipo_busqueda = 'nuevo';
            } elseif (stripos($q, ' ') !== false) {
                $tipo_busqueda = 'palabras';
            } else {
                $tipo_busqueda = 'palabra';
                $q_busqueda = preg_replace('/[^A-Za-z0-9]/', '', $this->quitarAcentos($q));
            }
            $busqueda = "Resultados para: " . $q;
            $peticion = "?q=" . $q . "&p=";
        }

        list($querySql, $queryBindings) = $this->querySoma($tipo_busqueda, $q_busqueda ?? $q);
        $resultados = \DB::connection('owari_soma')->select($querySql, $queryBindings);
        // Deduplicar por codigo_nikko
        $vistos = [];
        $unicos = [];
        foreach ($resultados as $r) {
            if (!isset($vistos[$r->codigo_nikko])) {
                $vistos[$r->codigo_nikko] = true;
                $unicos[] = $r;
            }
        }
        $resultados = $unicos;
        $total_resultados = count($resultados);
        $mostrar_productos = 15;
        $offset = ($p - 1) * $mostrar_productos;
        $resultados = array_slice($resultados, $offset, $mostrar_productos);

        // Cargar equivalencias de los resultados de esta pagina
        $productoIds = array_filter(array_map(function($r) { return $r->producto_id; }, $resultados));
        if (!empty($productoIds)) {
            $placeholders = implode(',', array_fill(0, count($productoIds), '?'));
            $equivRows = $this->somaSelect(
                "SELECT id_producto, clave FROM productos_equivalencias WHERE id_producto IN ({$placeholders}) AND deleted_at IS NULL ORDER BY id",
                array_values($productoIds)
            );
            $equivMap = [];
            foreach ($equivRows as $eq) {
                $equivMap[$eq->id_producto][] = $eq->clave;
            }
            foreach ($resultados as $r) {
                $r->equivalencias = $equivMap[$r->producto_id] ?? [];
            }
        } else {
            foreach ($resultados as $r) {
                $r->equivalencias = [];
            }
        }

        $productos = [];
        foreach ($resultados as $resultado) {
            array_push($productos, urlencode($resultado->codigo_nikko));
        }

        // (el cURL de abajo esta comentado desde antes; URL a SOMA por coherencia)
		$url = 'https://owari.appsoma.online/somma/v2.0/api/existencias?' . http_build_query(["claves" => $productos]);
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
        $stockFicticios = $this->mapaStockFicticio();
        $stockExterno = $this->mapaStockExterno(array_map(fn($r) => $r->codigo_nikko ?? '', $resultados));
        return view('tienda_online.productos', compact('resultados', 'total_resultados', 'botones', 'busqueda', 'pagina', 'peticion', 'titulo', 'existencias', 'q', 'stockFicticios', 'stockExterno'));
    }


    private function somaSelect($sql, $bindings = []) {
        return \DB::connection('owari_soma')->select($sql, $bindings);
    }

    public function detalleProducto($clave)
    {
        $clave = str_replace('_', '/', $clave);
        $clave = str_replace('+', '#', $clave);
        $titulo = "Producto: " . $clave;

        // Producto principal desde tablas normalizadas
        $productoArr = $this->somaSelect("
            SELECT
                p.clave as codigo_nikko,
                m.nombre as marca_comercial,
                pw.grupo,
                pw.subgrupo,
                pw.descripcion_1,
                pw.descripcion_2,
                pw.descripcion_3,
                pw.caracteristicas_1,
                pw.caracteristicas_2,
                pw.caracteristicas_3,
                pw.caracteristicas_4,
                pw.oem,
                COALESCE(ppr.precio, 0) as precio_normal,
                COALESCE(ppr.precio, 0) as precio_final,
                0 as minimo_compra_oferta,
                COALESCE(p.prioridad, 0) as ventas,
                p.id as producto_id,
                COALESCE(prov.clave, '') as clave_proveedor
            FROM productos p
            LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
            LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
            LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
            LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
            WHERE p.clave = ? AND p.deleted_at IS NULL
            LIMIT 1
        ", [$clave]);

        if (empty($productoArr)) {
            return "El producto no existe";
        }
        $producto = $productoArr[0];

        // Especificaciones (aplicaciones vehiculares)
        $especificaciones = $this->somaSelect("
            SELECT
                pa.armadora,
                pa.modelo,
                pa.ano_inicio as ano_inicial,
                pa.ano_fin as ano_final,
                pa.generacion_mexico,
                pa.version,
                pa.motor,
                pa.especificacion
            FROM productos_aplicaciones pa
            INNER JOIN productos p ON pa.id_producto = p.id
            WHERE p.clave = ? AND pa.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY pa.armadora ASC, pa.modelo ASC
        ", [$clave]);

        // Equivalencias con marca
        $equivalencias = $this->somaSelect("
            SELECT pe.clave, COALESCE(pe.id_marca, 0) as id_marca, COALESCE(m.nombre, '') as marca
            FROM productos_equivalencias pe
            INNER JOIN productos p ON pe.id_producto = p.id
            LEFT JOIN marcas m ON pe.id_marca = m.id AND m.deleted_at IS NULL
            WHERE p.clave = ? AND pe.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY pe.id
        ", [$clave]);

        // Aplicaciones de productos equivalentes que existen en el ERP
        $especificaciones_extra = $this->somaSelect("
            SELECT DISTINCT
                pa.armadora,
                pa.modelo,
                pa.ano_inicio as ano_inicial,
                pa.ano_fin as ano_final,
                pa.generacion_mexico,
                pa.version,
                pa.motor,
                pa.especificacion
            FROM productos_equivalencias pe
            INNER JOIN productos p_origen ON pe.id_producto = p_origen.id AND p_origen.deleted_at IS NULL
            INNER JOIN productos p_equiv ON pe.clave = p_equiv.clave AND p_equiv.deleted_at IS NULL
            INNER JOIN productos_aplicaciones pa ON pa.id_producto = p_equiv.id AND pa.deleted_at IS NULL
            WHERE p_origen.clave = ?
            AND pe.deleted_at IS NULL
            ORDER BY pa.armadora ASC, pa.modelo ASC
        ", [$clave]);

        // Productos relacionados
        $relacionados = $this->somaSelect("
            SELECT DISTINCT p2.clave as codigo_nikko, pw2.descripcion_1
            FROM productos p2
            LEFT JOIN productos_web pw2 ON p2.id = pw2.id_producto AND pw2.deleted_at IS NULL
            INNER JOIN productos_aplicaciones pa2 ON p2.id = pa2.id_producto AND pa2.deleted_at IS NULL
            WHERE pa2.modelo IN (
                SELECT pa3.modelo FROM productos_aplicaciones pa3
                INNER JOIN productos p3 ON pa3.id_producto = p3.id
                WHERE p3.clave = ? AND pa3.deleted_at IS NULL AND p3.deleted_at IS NULL
                AND pa3.modelo IS NOT NULL AND pa3.modelo != ''
            )
            AND p2.clave != ? AND p2.deleted_at IS NULL
            LIMIT 8
        ", [$clave, $clave]);

        // Stock del proveedor externo (KIMS) para este producto: se muestra como
        // disponibilidad y TOPA cuanto puede pedirse (la partida va a especial).
        $mapaExt = $this->mapaStockExterno([$producto->codigo_nikko ?? '']);
        $stockExternoProducto = (int) ($mapaExt[$producto->codigo_nikko ?? ''] ?? 0);
        $tieneProveedorExterno = !empty($mapaExt);

        return view('tienda_online.ver_producto', compact('producto', 'especificaciones', 'equivalencias', 'relacionados', 'titulo', 'especificaciones_extra', 'stockExternoProducto', 'tieneProveedorExterno'));
    }



    private function quitarAcentos($texto) {
        $buscar =    ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'];
        $reemplazar = ['a','e','i','o','u','A','E','I','O','U','n','N','u','U'];
        return str_replace($buscar, $reemplazar, $texto);
    }

    private function querySoma($tipo_query, $string)
    {
        if (is_string($string)) {
            $string = $this->quitarAcentos($string);
        }
        $bindings = [];
        $select = "SELECT
                m.nombre as marca_comercial,
                p.clave as codigo_nikko,
                pw.grupo,
                pw.subgrupo,
                pw.descripcion_1,
                pw.descripcion_2,
                pw.descripcion_3,
                pw.caracteristicas_1,
                pw.caracteristicas_2,
                pw.caracteristicas_3,
                pw.caracteristicas_4,
                pw.oem,
                pb.armadora,
                pb.modelo,
                pb.ano_inicial,
                pb.ano_final,
                COALESCE(pw.nuevo, false) as nuevo,
                COALESCE(ppr.precio, 0) as precio_normal,
                COALESCE(ppr.precio, 0) as precio_final,
                0 as minimo_compra_oferta,
                COALESCE(p.prioridad, 0) as ventas,
                0 as existencias,
                '' as especial,
                '' as disponibilidad,
                p.id as producto_id,
                COALESCE(prov.clave, '') as clave_proveedor
            FROM productos_busqueda pb
            INNER JOIN productos p ON pb.producto_id = p.id
            LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
            LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
            LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
            LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL";

        switch ($tipo_query) {

        case 'categoria':
            $sql = $select . " WHERE pb.subgrupo = ? ORDER BY pb.ventas DESC NULLS LAST";
            $bindings = [str_replace("_", " ", $string)];
            break;

        case 'autocompletar':
            $sql = $select . " WHERE pb.buscador ILIKE ? ORDER BY pb.ventas DESC NULLS LAST";
            $bindings = ['%' . $string . '%'];
            break;

        case 'filtro':
            $extra = "";
            $bindings = [];
            if (isset($string['ano']) && $string['ano'] != "0" && $string['ano'] != "todos") {
                $extra .= " AND ?::int BETWEEN pb.ano_inicial AND pb.ano_final";
                $bindings[] = $string['ano'];
            }
            if (isset($string['marca']) && $string['marca'] != "0" && $string['marca'] != "todos") {
                $extra .= " AND pb.armadora = ?";
                $bindings[] = $string['marca'];
            }
            if (isset($string['modelo']) && $string['modelo'] != "0" && $string['modelo'] != "todos") {
                $extra .= " AND pb.modelo = ?";
                $bindings[] = $string['modelo'];
            }
            if (isset($string['motor']) && $string['motor'] != "0" && $string['motor'] != "todos") {
                $extra .= " AND pb.motor = ?";
                $bindings[] = $string['motor'];
            }
            if (isset($string['grupo']) && $string['grupo'] != "0" && $string['grupo'] != "todos") {
                $extra .= " AND pb.grupo = ?";
                $bindings[] = $string['grupo'];
            }
            if (isset($string['familia']) && $string['familia'] != "0" && $string['familia'] != "todos") {
                $extra .= " AND pb.subgrupo = ?";
                $bindings[] = $string['familia'];
            }
            $sql = $select . " WHERE 1=1" . $extra . " ORDER BY pb.ventas DESC NULLS LAST";
            break;

        case 'palabra':
            $sql = $select . " WHERE pb.buscador ILIKE ? ORDER BY pb.ventas DESC NULLS LAST";
            $bindings = ['%' . $string . '%'];
            break;

        case 'nuevo':
            $sql = $select . " WHERE pb.nuevo = true ORDER BY pb.ventas DESC NULLS LAST";
            break;

        case 'todos':
            $sql = $select . " ORDER BY pb.ventas DESC NULLS LAST, pw.descripcion_1 ASC NULLS LAST";
            break;

        case 'palabras':
            $palabras = array_filter(explode(" ", trim($string)));
            $where = [];
            $bindings = [];
            foreach ($palabras as $p) {
                $where[] = "pb.buscador ILIKE ?";
                $bindings[] = '%' . $p . '%';
            }
            $sql = $select . " WHERE " . implode(' AND ', $where) . " ORDER BY pb.ventas DESC NULLS LAST";
            break;

        default:
            $sql = $select . " ORDER BY pb.ventas DESC NULLS LAST";
            break;
        }

        return [$sql, $bindings];
    }

    private function buscarProductosPorClaves(array $claves)
    {
        $claves = array_filter($claves);
        if (empty($claves)) return [];
        $ph = implode(',', array_fill(0, count($claves), '?'));
        $rows = $this->somaSelect("
            SELECT DISTINCT ON (p.clave)
                p.clave as codigo_nikko, m.nombre as marca_comercial,
                pw.grupo, pw.subgrupo, pw.descripcion_1, pw.descripcion_2, pw.descripcion_3,
                pw.caracteristicas_1, pw.caracteristicas_2, pw.caracteristicas_3, pw.caracteristicas_4,
                COALESCE(ppr.precio, 0) as precio_normal, p.id as producto_id,
                COALESCE(prov.clave, '') as clave_proveedor
            FROM productos p
            LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
            LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
            LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
            LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
            WHERE p.clave IN ({$ph}) AND p.deleted_at IS NULL
            ORDER BY p.clave
        ", array_values($claves));
        return array_values(array_map(fn($r) => (array) $r, $rows));
    }

    private function buscarAplicacionesPorClave(string $clave)
    {
        $partes = $this->somaSelect("
            SELECT pa.armadora, pa.modelo, pa.ano_inicio, pa.ano_fin, pa.motor,
                   COALESCE(pa.especificacion, '') as cilindros
            FROM productos_aplicaciones pa
            INNER JOIN productos p ON pa.id_producto = p.id
            WHERE p.clave = ? AND pa.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY pa.armadora, pa.modelo
        ", [$clave]);
        $motores = "";
        foreach ($partes as $parte) {
            $motores .= $parte->armadora . " " . $parte->modelo . " " . $parte->ano_inicio . "-" . $parte->ano_fin . " " . $parte->cilindros . "CIL " . $parte->motor . "L<br>";
        }
        return $motores;
    }

    public function autocompletar(Request $request)
    {
        $query = $this->quitarAcentos($request->input('query', ''));
        $resultados = \DB::connection('owari_soma')->select("
            SELECT
                p.clave || ' - ' || COALESCE(pw.descripcion_1, '') as value,
                REPLACE(REPLACE(p.clave, '/', '_'), '#', '+') as data
            FROM (
                SELECT DISTINCT producto_id
                FROM productos_busqueda
                WHERE buscador ILIKE ?
                LIMIT 15
            ) pb
            INNER JOIN productos p ON pb.producto_id = p.id
            LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
        ", ['%' . $query . '%']);
        return json_encode(["query" => $query, "suggestions" => $resultados]);
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

        if (empty($favoritos)) {
            $resultados = [];
        } else {
            $placeholders = implode(',', array_fill(0, count($favoritos), '?'));
            $resultados = $this->somaSelect("
                SELECT DISTINCT ON (p.clave)
                    p.clave as codigo_nikko,
                    m.nombre as marca_comercial,
                    pw.grupo,
                    pw.subgrupo,
                    pw.descripcion_1,
                    pw.descripcion_2,
                    pw.descripcion_3,
                    pw.caracteristicas_1,
                    pw.caracteristicas_2,
                    pw.caracteristicas_3,
                    pw.caracteristicas_4,
                    COALESCE(ppr.precio, 0) as precio_normal,
                    COALESCE(p.prioridad, 0) as ventas,
                    p.id as producto_id,
                    COALESCE(prov.clave, '') as clave_proveedor
                FROM productos p
                LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
                LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
                LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
                LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
                WHERE p.clave IN ({$placeholders}) AND p.deleted_at IS NULL
                ORDER BY p.clave
            ", $favoritos);

            // Cargar equivalencias
            $productoIds = array_filter(array_map(fn($r) => $r->producto_id, $resultados));
            if (!empty($productoIds)) {
                $ph2 = implode(',', array_fill(0, count($productoIds), '?'));
                $equivRows = $this->somaSelect(
                    "SELECT id_producto, clave FROM productos_equivalencias WHERE id_producto IN ({$ph2}) AND deleted_at IS NULL ORDER BY id",
                    array_values($productoIds)
                );
                $equivMap = [];
                foreach ($equivRows as $eq) {
                    $equivMap[$eq->id_producto][] = $eq->clave;
                }
                foreach ($resultados as $r) {
                    $r->equivalencias = $equivMap[$r->producto_id] ?? [];
                }
            } else {
                foreach ($resultados as $r) {
                    $r->equivalencias = [];
                }
            }
        }

        $stockFicticios = $this->mapaStockFicticio();
        $stockExterno = $this->mapaStockExterno(array_map(fn($r) => $r->codigo_nikko ?? '', $resultados));
        return view('tienda_online.favoritos', compact('resultados', 'titulo', 'stockFicticios', 'stockExterno'));
    }

    public function actualizarCarrito(Request $request)
    {
        extract($request->all());

        $svc = $this->carritoSvc();
        $carrito = [];

        if (!$svc->tiene('normal')) {

            if ($cantidad > $partida['existencia'])
                $cantidad = $partida['existencia'];

            if ($cantidad > 0) {
                $carrito = [['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]];
            }

        } else {

            $carrito = $svc->obtener('normal');
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

        $svc->guardar('normal', $carrito);
        return json_encode([
            'code' => 1,
            'carrito' => $carrito,
        ]);
    }


    public function actualizarCarritoEspecial(Request $request)
    {
        extract($request->all());

        $svc = $this->carritoSvc();
        $carrito = [];

        if (!$svc->tiene('especial')) {

            if ($cantidad > 0) {
                $carrito = [['numero_parte' => $numero_parte, 'cantidad' => $cantidad, 'partida' => $partida, 'sustituto' => $sustituto]];
            }

        } else {

            $carrito = $svc->obtener('especial');
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

        $svc->guardar('especial', $carrito);
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
        $svc = $this->carritoSvc();
        if ($svc->tiene('normal')) {
            $carrito = $svc->obtener('normal');

            $existe_premio_carrito = false;
            foreach ($carrito as $key => $value) {
                // code...
                if ($value['numero_parte'] == $premio)
                    $existe_premio_carrito = true;
            }

            if ($existe_premio_carrito && count($carrito) == 1) {
                $svc->guardar('normal', []);
                $carrito = [];
            }



            if (count($carrito) > 0 && !$existe_premio_carrito) {

                $premio_partida = PedidoPartida::join('pedidos_web', 'pedidos_partidas.id_pedido', '=', 'pedidos_web.id')->where('pedidos_partidas.clave', $premio)->where('pedidos_web.cliente', \Auth::user()->clave_cliente)->where('pedidos_web.deleted_at', null)->first();

                if (!$premio_partida) {
                    $url = 'https://owari.appsoma.online/somma/v2.0/api/cotizar?' . http_build_query(["clave" => $premio, "cliente" => \Auth::user()->clave_cliente, 'tipo' => 'factura']);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $producto = json_decode($data, true);

                    if (is_array($producto) && ($producto['existencia'] ?? 0) > 0)
                        array_push($carrito, ['numero_parte' => $premio, 'cantidad' => 1, 'partida' => $producto, 'sustituto' => false]);
                }

            }


            $productos = $this->buscarProductosPorClaves(array_column($carrito, 'numero_parte'));
            // Existencias desde SOMA en UN SOLO lote (antes: un cURL a SAE por
            // producto — N+1 que hacia lento abrir el carrito). SOMA es la fuente
            // de la verdad del stock: espejo nocturno + decrementos por pedido.
            $existenciasLote = $this->existenciasReales(array_map(fn($v) => $v['codigo_nikko'], $productos));

            foreach ($productos as $key => $value) {
                // Guardar SIEMPRE la existencia real (sin ficticio) para que la
                // logica de division (split) pueda usar el stock real.
                $existenciaRealSae = intval($existenciasLote[$value['codigo_nikko']] ?? 0);
                $existencias_reales = ['existencia' => $existenciaRealSae];
                $productos[$key]['existencia_real_sae'] = $existenciaRealSae;

                // Sumar `stock_ficticio` al stock visible si el proveedor del
                // producto tiene tipo_separacion='split_por_stock' en SOMA.
                // Data-driven — NUNCA hardcodear claves de proveedor (S227, AAAE, etc.).
                $stockFicticio = $this->obtenerStockFicticio($value['clave_proveedor'] ?? null);
                if ($stockFicticio > 0) {
                    $existencias_reales['existencia'] = $existenciaRealSae + $stockFicticio;
                }





                $motores = $this->buscarAplicacionesPorClave($value['codigo_nikko']);
                $productos[$key]['motores'] = $motores;
                foreach ($carrito as $llave => $valor) {
                    if ($valor['numero_parte'] == $value['codigo_nikko']) {

                        $productos[$key]['mensaje_existencia'] = '';
                        $productos[$key]['solicitado'] = $valor['cantidad'];
                        $productos[$key]['solicitado_original'] = $valor['cantidad'];

                        $existenciaDisp = is_array($existencias_reales) ? ($existencias_reales['existencia'] ?? 0) : 0;

                        if ($existenciaDisp <= 0) {
                            $productos[$key]['existencia_real'] = 0;
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia de este producto. <br> Solicitaste ' . $valor['cantidad'];
                            $productos[$key]['solicitado'] = 0;
                        }


                        if ($existenciaDisp < $valor['cantidad']) {
                            $productos[$key]['mensaje_existencia'] = 'Ya no hay existencia completa de este producto. De ' . $valor['cantidad'] . ' paso a ' . $existenciaDisp;
                            $productos[$key]['solicitado'] = $existenciaDisp;
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
        if ($svc->tiene('especial')) {
            $carrito = $svc->obtener('especial');
            $productos_especiales = $this->buscarProductosPorClaves(array_column($carrito, 'numero_parte'));
            foreach ($productos_especiales as $key => $value) {
                $motores = $this->buscarAplicacionesPorClave($value['codigo_nikko']);
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

        // Stock de proveedores externos (KIMS) para topar cantidades en el carrito
        // especial: no se puede comprometer mas de lo que el proveedor tiene.
        $stockExterno = $this->mapaStockExterno(array_merge(
            array_map(fn($x) => $x['codigo_nikko'] ?? '', $productos ?: []),
            array_map(fn($x) => $x['codigo_nikko'] ?? '', $productos_especiales ?: [])
        ));

        return view('tienda_online.carrito', compact('productos', 'estampa', 'titulo', 'productos_especiales', 'stockExterno'));
    }


    public function guardarPedido(Request $request)
    {
        extract($request->all());

        // partidas puede no venir en el request cuando el pedido es 100%
        // especial: jQuery omite los arrays vacios al serializar, asi que
        // `extract` no define $partidas y el foreach de abajo reventaria con
        // "Undefined variable". Lo normalizamos a [] para ese caso.
        $partidas = $request->input('partidas', []);

        // Compatibilidad con dos shapes de payload:
        //   - Shape viejo / unico folio: solo se manda `pedido_sae` (folio principal).
        //   - Shape v2 (carrito refactorizado): se mandan `folio_factura` y
        //     `folio_remision`. El folio de factura va a `pedido_sae` (mismo
        //     campo de produccion, sin breaking change). El de remision va a
        //     `pedido_sae_remision` (campo nuevo).
        $folioFactura  = $request->input('folio_factura',  $request->input('pedido_sae'));
        $folioRemision = $request->input('folio_remision', null);

        // Normalizacion: vacios y "0" se traducen a null para que la columna
        // quede NULL en BD y los reportes/UI los detecten como "no generado".
        if ($folioFactura === '' || $folioFactura === '0' || $folioFactura === 0) {
            $folioFactura = null;
        }
        if ($folioRemision === '' || $folioRemision === '0' || $folioRemision === 0) {
            $folioRemision = null;
        }

        $data = [
            'cliente' => $cliente,
            'subtotal',
            'iva',
            'gran_total',
            'cadena_original'     => strval(json_encode($request->all())),
            'estado'              => "CAPTURADO",
            'capturo'             => \Auth::user()->id,
            'pedido_sae'          => $folioFactura,    // folio empresa 1 (factura)
            'pedido_sae_remision' => $folioRemision,   // folio empresa 3 (remision) — null si no aplica
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

        // Si el carrito encolo pedidos SAE pendientes (porque fallaron los 5
        // retries en frontend), enlazarlos a este PedidoWeb para que el job
        // artisan los actualice cuando logre insertarlos en SAE.
        $idsPendientes = $request->input('ids_pendientes_sae', []);
        if (!empty($idsPendientes) && is_array($idsPendientes)) {
            \App\Models\PedidoSaePendiente::whereIn('id', $idsPendientes)
                ->whereNull('id_pedido_web')
                ->update(['id_pedido_web' => $pedido->id]);
        }

        return json_encode([
            'code' => 1,
            'id_pedido' => $pedido->id
        ]);

    }

    /**
     * Escribe en un PedidoWeb ya creado los folios SAE que se lograron.
     * El flujo v2 del carrito crea el espejo PRIMERO (folios en null) y luego,
     * tras insertar en SAE, llama aqui para llenar los folios. Solo escribe
     * columnas vacias: no pisa folios que el cron de pendientes ya haya puesto.
     *
     * POST /tienda_online/carrito/actualizar_folios
     *   { id_pedido, folio_factura, folio_remision }
     */
    public function actualizarFoliosEspejo(Request $request)
    {
        $idPedido      = $request->input('id_pedido');
        $folioFactura  = $request->input('folio_factura');
        $folioRemision = $request->input('folio_remision');

        // Normalizar vacios/"0" a null
        if ($folioFactura === '' || $folioFactura === '0' || $folioFactura === 0)   $folioFactura = null;
        if ($folioRemision === '' || $folioRemision === '0' || $folioRemision === 0) $folioRemision = null;

        $pedido = PedidoWeb::where('id', $idPedido)
            ->where('cliente', \Auth::user()->clave_cliente)
            ->first();

        if (!$pedido) {
            return response()->json(['code' => 0, 'mensaje' => 'Pedido no encontrado']);
        }

        // Solo llenar columnas vacias (no pisar folios ya puestos por el cron).
        $cambios = false;
        if ($folioFactura && empty($pedido->pedido_sae)) {
            $pedido->pedido_sae = $folioFactura;
            $cambios = true;
        }
        if ($folioRemision && empty($pedido->pedido_sae_remision)) {
            $pedido->pedido_sae_remision = $folioRemision;
            $cambios = true;
        }
        if ($cambios) $pedido->save();

        return response()->json(['code' => 1, 'id_pedido' => $pedido->id]);
    }


    public function verificarPedidoDuplicado(Request $request)
    {
        $claves = $request->input('claves', []);
        $claves = array_values(array_filter(array_map('trim', $claves)));

        if (empty($claves)) {
            return response()->json(['similar' => false]);
        }

        // Ultimo pedido del cliente
        $ultimoPedido = PedidoWeb::where('cliente', \Auth::user()->clave_cliente)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ultimoPedido) {
            return response()->json(['similar' => false]);
        }

        $clavesAnterior = PedidoPartida::where('id_pedido', $ultimoPedido->id)
            ->pluck('clave')
            ->map(fn($c) => trim($c))
            ->toArray();

        $coincidencias = array_values(array_intersect($claves, $clavesAnterior));

        if (count($coincidencias) >= 2) {
            return response()->json([
                'similar' => true,
                'id_pedido_anterior' => $ultimoPedido->id,
                'pedido_sae_anterior' => $ultimoPedido->pedido_sae,
                'fecha_anterior' => $ultimoPedido->created_at ? $ultimoPedido->created_at->format('d/m/Y H:i') : '',
                'coincidencias' => $coincidencias,
                'total_coincidencias' => count($coincidencias),
            ]);
        }

        return response()->json(['similar' => false]);
    }


    public function guardadoExitoso(Request $r)
    {
        extract($r->all());
        $tipo = $r->input('tipo', '');
        $pedido = null;

        if ($tipo === 'especial') {
            $pedido = PedidoEspecial::find($id_pedido);
        } else {
            $pedido = PedidoWeb::find($id_pedido);
        }

        if (!$pedido || $pedido->cliente != \Auth::user()->clave_cliente)
            return redirect()->route('tienda_online.dashboard');


        $this->carritoSvc()->vaciarTodo();

        $titulo = "Carrito exitoso";
        return view('tienda_online.exito', compact('titulo', 'id_pedido'));

    }

    public function misPedidos()
    {
        $titulo = "Pedidos";
        $pedidos = PedidoWeb::where('cliente', \Auth::user()->clave_cliente)->where('created_at', '>=', '2025-01-01')->orderBy('created_at', 'desc')->limit(30)->get();

        // Pedidos especiales normales (sin clave_proveedor o distinto de S227)
        $pedidos_especiales = PedidoEspecial::where('cliente', \Auth::user()->clave_cliente)
            ->where('created_at', '>=', '2025-01-01')
            ->where(function($q) { $q->whereNull('clave_proveedor')->orWhere('clave_proveedor', '!=', 'S227'); })
            ->limit(30)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pedidos especiales SYD (se muestran junto a pedidos normales)
        $pedidos_syd = PedidoEspecial::where('cliente', \Auth::user()->clave_cliente)
            ->where('created_at', '>=', '2025-01-01')
            ->where('clave_proveedor', 'S227')
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        // Adaptar SYD al formato de PedidoWeb y combinar con la lista de pedidos normales
        $pedidos_syd_formateados = $pedidos_syd->map(function($esp) {
            $obj = new \stdClass();
            $obj->id = $esp->id;
            $obj->pedido_sae = 'SYD-' . $esp->id;
            $obj->estado = 'SURTIDO BODEGA2';
            $obj->created_at = $esp->created_at;
            $obj->partidas = $esp->partidas;
            $obj->gran_total = $esp->gran_total;
            $obj->es_syd = true;
            return $obj;
        });

        // Marcar pedidos normales
        $pedidos = $pedidos->map(function($p) {
            $p->es_syd = false;
            return $p;
        });

        $pedidos = $pedidos->concat($pedidos_syd_formateados)->sortByDesc('created_at')->values();

        // Folios SAE conocidos en local (excluir SYD que aun no tienen folio).
        // Incluye tanto pedido_sae (factura) como pedido_sae_remision si existe,
        // para que SAE no devuelva esos folios como "nuevos por descubrir".
        $pedidos_no_syd = $pedidos->where('es_syd', false);
        $pedidos_sae = $pedidos_no_syd->pluck('pedido_sae')
            ->merge($pedidos_no_syd->pluck('pedido_sae_remision'))
            ->filter(function($f) { return !empty($f); })
            ->values()
            ->toArray();
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



                $url = 'https://owari.appsoma.online/somma/v2.0/api/cotizar?' . http_build_query(["clave" => $clave, "cliente" => \Auth::user()->clave_cliente, 'tipo' => 'factura']);
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

        // Filtros de la barra lateral (opcionales). Si vienen, la vista muestra
        // solo ese grupo/subgrupo, siempre paginado.
        $grupoFiltro    = trim((string) $request->query('grupo', ''));
        $subgrupoFiltro = trim((string) $request->query('subgrupo', ''));

        // Buscador propio de descuentos (independiente del de /productos). Se
        // resuelve en memoria sobre el catalogo ya cargado: partimos la
        // consulta en palabras y exigimos que TODAS aparezcan (AND).
        $q         = trim((string) $request->query('q', ''));
        $qPalabras = $q === '' ? [] : preg_split('/\s+/', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY);

        // Categoria y busqueda son EXCLUYENTES: si el cliente entra a un
        // subgrupo, el buscador por palabras no tiene efecto.
        if ($subgrupoFiltro !== '' || $grupoFiltro !== '') {
            $q = '';
            $qPalabras = [];
        }

        // 1+2) Catalogo de descuentos del cliente = lista SAE (POLI01) + datos
        //      SOMA. Es lo caro del request (externo + Postgres) y es identico
        //      para buscar / paginar / filtrar por subgrupo, asi que lo
        //      cacheamos por cliente 15 min (las promos cambian ~diario: el
        //      Excel se regenera a las 02:00). Asi solo el 1er load paga
        //      SAE+SOMA; buscar/paginar/subgrupo son filtros en RAM.
        $claveCliente = \Auth::user()->clave_cliente;
        $catalogo = \Cache::remember('descuentos_catalogo_' . $claveCliente, now()->addMinutes(15), function () use ($claveCliente) {
        // 1) Lista de productos con descuento + vigencia desde el externo (SAE).
        //    Devuelve [{clave, vigencia, inicio}].
        // Descuentos del cliente desde SOMA (politicas propias; mismo shape que SAE)
        $url = 'https://owari.appsoma.online/somma/v2.0/api/productos-descuentos?' . http_build_query(["cliente" => $claveCliente]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data, true);

        $items = (is_array($data) && isset($data['productos']) && is_array($data['productos']))
            ? $data['productos']
            : [];

        // Compat: el externo antes devolvia un arreglo plano de claves. Ahora
        // devuelve objetos {clave, vigencia}. Soportamos ambos por si el externo
        // aun no se despliega.
        $vigencias = [];   // clave => 'Y-m-d'|null (fin, mas proxima a vencer)
        $inicios   = [];   // clave => 'Y-m-d'|null (inicio, promo mas nueva)
        foreach ($items as $it) {
            if (is_array($it)) {
                $clave = $it['clave'] ?? null;
                if ($clave === null) continue;
                $vigencias[$clave] = $it['vigencia'] ?? null;
                $inicios[$clave]   = $it['inicio'] ?? null;
            } else {
                $vigencias[$it] = null;
                $inicios[$it]   = null;
            }
        }
        $claves = array_values(array_unique(array_keys($vigencias)));

        // 2) Catalogo desde SOMA (owari_soma / Postgres), NO desde el CMS viejo.
        //    productos + productos_web (grupo/subgrupo/descripcion/caracteristicas)
        //    + marcas (marca). Igual que el buscador del telemarketing.
        $catalogo = collect();
        if (!empty($claves)) {
            $rows = \DB::connection('owari_soma')->table('productos as p')
                ->leftJoin('productos_web as pw', function ($j) {
                    $j->on('pw.id_producto', '=', 'p.id')->whereNull('pw.deleted_at');
                })
                ->leftJoin('marcas as m', function ($j) {
                    $j->on('m.id', '=', 'p.id_marca')->whereNull('m.deleted_at');
                })
                ->whereIn('p.clave', $claves)
                ->whereNull('p.deleted_at')
                ->get([
                    'p.id',
                    'p.clave as codigo_nikko',
                    'm.nombre as marca_comercial',
                    'pw.grupo', 'pw.subgrupo',
                    'pw.descripcion_1', 'pw.descripcion_2', 'pw.descripcion_3',
                    'pw.caracteristicas_1', 'pw.caracteristicas_2', 'pw.caracteristicas_3',
                ]);

            // Equivalencias (filas en SOMA) -> hasta 5 columnas equivalencia_N
            // para compat con la vista.
            $ids = $rows->pluck('id')->all();
            $equivsPorProducto = collect();
            if (!empty($ids)) {
                $equivsPorProducto = \DB::connection('owari_soma')->table('productos_equivalencias')
                    ->whereIn('id_producto', $ids)
                    ->whereNull('deleted_at')
                    ->orderBy('id')
                    ->get(['id_producto', 'clave'])
                    ->groupBy('id_producto');
            }

            // Dedup por codigo_nikko y armado del objeto que consume la vista.
            $vistos = [];
            foreach ($rows as $r) {
                if (isset($vistos[$r->codigo_nikko])) continue;
                $vistos[$r->codigo_nikko] = true;

                $eqs = $equivsPorProducto->get($r->id, collect())->pluck('clave')->values();
                for ($n = 1; $n <= 5; $n++) {
                    $r->{'equivalencia_' . $n} = $eqs->get($n - 1, '');
                }
                $r->caracteristicas_4 = '';   // SOMA no tiene la 4; se deja vacia
                $r->marca_comercial   = $r->marca_comercial ?? '';
                $r->grupo             = $r->grupo    ?: 'SIN GRUPO';
                $r->subgrupo          = $r->subgrupo ?: 'SIN SUBGRUPO';
                $r->vigencia          = $vigencias[$r->codigo_nikko] ?? null;
                $r->inicio            = $inicios[$r->codigo_nikko] ?? null;

                $catalogo->push($r);
            }

            // Ordenar por promocion MAS NUEVA: fecha de inicio (V_DFECH) de la
            // politica descendente. Los que no tienen fecha de inicio van al
            // final ('' ordena despues por sortByDesc con '' minimo).
            $catalogo = $catalogo->sortByDesc(function ($p) {
                return $p->inicio ?? '';
            })->values();
        }

        return $catalogo;
        });

        // 3) Lista PLANA de subgrupos, unica y ordenada alfabeticamente, tomada
        //    del set completo (para que la barra lateral muestre todos aunque
        //    haya filtro/paginacion). El cliente los ve directo, sin desplegar
        //    categorias.
        $subgrupos = $catalogo
            ->pluck('subgrupo')
            ->filter(function ($s) { return $s !== null && $s !== ''; })
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $total_resultados = $catalogo->count();

        // 4) Aplicar filtro de grupo/subgrupo (si viene de la barra lateral).
        $filtrados = $catalogo->filter(function ($p) use ($grupoFiltro, $subgrupoFiltro, $qPalabras) {
            if ($grupoFiltro !== '' && $p->grupo !== $grupoFiltro) return false;
            if ($subgrupoFiltro !== '' && $p->subgrupo !== $subgrupoFiltro) return false;
            if (!empty($qPalabras)) {
                // Texto contra el que se busca: clave, marca, grupo/subgrupo,
                // descripciones y equivalencias.
                $heno = mb_strtolower(implode(' ', array_filter([
                    $p->codigo_nikko, $p->marca_comercial, $p->grupo, $p->subgrupo,
                    $p->descripcion_1 ?? '', $p->descripcion_2 ?? '', $p->descripcion_3 ?? '',
                    $p->equivalencia_1 ?? '', $p->equivalencia_2 ?? '', $p->equivalencia_3 ?? '',
                    $p->equivalencia_4 ?? '', $p->equivalencia_5 ?? '',
                ])));
                foreach ($qPalabras as $palabra) {
                    if (!str_contains($heno, $palabra)) return false;
                }
            }
            return true;
        })->values();

        // 5) Paginacion con el MISMO estilo que /tienda_online/productos:
        //    botones con ellipsis + base $peticion (que conserva el filtro).
        $mostrar_productos = 30;
        $p = (int) $request->query('p', 1);
        if ($p < 1) $p = 1;

        $total_filtrados = $filtrados->count();
        $total_paginas   = (int) max(1, ceil($total_filtrados / $mostrar_productos));
        if ($p > $total_paginas) $p = $total_paginas;

        $offset     = ($p - 1) * $mostrar_productos;
        $resultados = $filtrados->slice($offset, $mostrar_productos)->values();

        // Base del query string para los links, terminada en "p=" (igual que en
        // productos: <url>?<filtros>&p=<n>). Preserva grupo/subgrupo.
        $filtrosQuery = [];
        if ($q !== '')              $filtrosQuery['q'] = $q;
        if ($grupoFiltro !== '')    $filtrosQuery['grupo'] = $grupoFiltro;
        if ($subgrupoFiltro !== '') $filtrosQuery['subgrupo'] = $subgrupoFiltro;
        $peticion = '?' . http_build_query($filtrosQuery);
        $peticion .= ($peticion === '?' ? '' : '&') . 'p=';

        // Botones (misma logica de ellipsis que productos).
        $botones = [];
        if ($total_paginas > 10) {
            if ($p <= 7) {
                $botones = [1, 2, 3, 4, 5, 6, 7, '...', $total_paginas - 2, $total_paginas - 1, $total_paginas];
            } elseif ($total_paginas - 2 <= $p) {
                $botones = [1, 2, 3, '...', $total_paginas - 6, $total_paginas - 5, $total_paginas - 4, $total_paginas - 3, $total_paginas - 2, $total_paginas - 1, $total_paginas];
            } else {
                $botones = [1, 2, 3, '...', $p - 1, $p, $p + 1, '...', $total_paginas - 2, $total_paginas - 1, $total_paginas];
            }
        } else {
            for ($i = 1; $i <= $total_paginas; $i++) $botones[] = $i;
        }

        $pagina = $p;

        return view('tienda_online.descuentos', compact(
            'resultados', 'total_resultados', 'total_filtrados', 'titulo', 'subgrupos',
            'grupoFiltro', 'subgrupoFiltro', 'botones', 'pagina', 'peticion', 'total_paginas', 'q'
        ));
    }

    public function vaciarCarrito()
    {
        $this->carritoSvc()->vaciarTodo();
        return false;
    }

    /**
     * Entrega el Excel de promociones globales generado por el cron diario
     * (promociones:generar-excel). Sirve el archivo desde storage/app.
     * URL: /tienda_online/promociones.xlsx
     */
    public function promocionesExcel()
    {
        $ruta = storage_path('app/promociones.xlsx');
        if (!file_exists($ruta)) {
            abort(404, 'El listado de promociones aun no esta disponible.');
        }
        return response()->download($ruta, 'promociones-owari.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
        // Tambien marcamos password_changed_at para que el middleware
        // ForzarCambioPassword no vuelva a redirigir al cliente al flujo
        // obligatorio si decide cambiarla desde "Editar cliente".
        $usuario->fill([
            'password'            => \Hash::make($password),
            'password_changed_at' => now(),
        ])->save();
        \Session::flash('message', 'Tu password se actualizo correctamente.');
        $titulo = "Editar cliente";
        return view('tienda_online.cliente', compact('titulo'));
    }



    public function actualizarTiendita(Request $r)
    {
        extract($r->all());

        $activarTiendita = $r->has('activar_tiendita');
        $porcentajeInput = (float) $r->input('porcentaje', 0);

        // Si se esta activando la tiendita, el porcentaje es obligatorio (>0).
        // Sin esa validacion el cliente activaba la tiendita sin margen, veia
        // los precios "raros" y llamaba a soporte pensando que no podia ver
        // sus propios precios.
        if ($activarTiendita && $porcentajeInput <= 0) {
            \Session::flash('error_tiendita', 'Para activar tu tiendita debes ingresar un porcentaje mayor a 0.');
            return redirect()->back();
        }

        $cliente = Cliente::where('id_usuario', \Auth::user()->id)->first();
        if (!$cliente) {
            $cliente = Cliente::create([
                'id_usuario' => \Auth::user()->id
            ]);
        }

        $data = ['tiendita' => $activarTiendita];

        if (isset($logotipo)) {
            $filename = uniqid() . '.' . \File::extension($logotipo->getClientOriginalName());
            $logotipo->move(base_path() . '/public/logos/', $filename);
            $data['logotipo'] = $filename;
        }

        if ($porcentajeInput > 0)
            $data['porcentaje'] = $porcentajeInput;

        $cliente->fill($data)->save();
        \Session::flash('message', 'Tu información se actualizo correctamente.');

        // Si quedo activa la tiendita, mostramos modal explicando que los
        // precios visibles ahora incluyen el margen — para evitar que el
        // cliente piense que esos son sus precios.
        if ($activarTiendita) {
            \Session::flash('mostrar_modal_tiendita', true);
            \Session::flash('porcentaje_tiendita', $porcentajeInput);
        }

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
                $message->from('tiendaonline@owari.com.mx', 'OWARI Tienda Online');
                $message->subject("Gracias por su compra! Pedido " . $pedido->pedido_sae);
                $message->attach(base_path() . "/public/pdfs/pedidos/" . $archivo);
                $message->to([$email]);
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

        // La lista de liquidacion ahora se administra en SOMA (tabla productos_liquidacion),
        // ya NO se consume la API externa catalowari. Traemos las claves activas.
        // Defensivo: si la tabla aun no existe (SQL no aplicado), mostramos vacio en vez de romper.
        try {
            $filasLiquidacion = $this->somaSelect("
                SELECT p.clave
                FROM productos_liquidacion pl
                JOIN productos p ON p.id = pl.id_producto AND p.deleted_at IS NULL
                WHERE pl.deleted_at IS NULL
                ORDER BY pl.created_at DESC
            ");
            $data = array_map(fn($f) => $f->clave, $filasLiquidacion);
        } catch (\Throwable $e) {
            \Log::warning('productos_liquidacion no disponible: ' . $e->getMessage());
            $data = [];
        }

        if (!empty($data)) {
            $placeholders = implode(',', array_fill(0, count($data), '?'));
            $resultados = $this->somaSelect("
                SELECT DISTINCT ON (p.clave)
                    p.clave as codigo_nikko,
                    m.nombre as marca_comercial,
                    pw.grupo, pw.subgrupo,
                    pw.descripcion_1, pw.descripcion_2, pw.descripcion_3,
                    pw.caracteristicas_1, pw.caracteristicas_2, pw.caracteristicas_3, pw.caracteristicas_4,
                    COALESCE(ppr.precio, 0) as precio_normal,
                    COALESCE(p.prioridad, 0) as ventas,
                    p.id as producto_id,
                    COALESCE(prov.clave, '') as clave_proveedor
                FROM productos p
                LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
                LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
                LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
                LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
                WHERE p.clave IN ({$placeholders}) AND p.deleted_at IS NULL
                ORDER BY p.clave
            ", $data);
        } else {
            $resultados = [];
        }

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
        $stockFicticios = $this->mapaStockFicticio();
        $stockExterno = $this->mapaStockExterno(array_map(fn($r) => $r->codigo_nikko ?? '', $resultados));
        return view('tienda_online.productos_liquidacion', compact('resultados', 'total_resultados', 'botones', 'pagina', 'titulo', 'stockFicticios', 'stockExterno'));


    }

    // ---------------------------------------------------------------------
    //  Regalo de LIQUIDACION (independiente del regalo de promo de SOMA).
    //  El cliente elige 1 de hasta 3 productos de liquidacion cuyo precio
    //  normal no exceda el 1% del subtotal (sin IVA) del pedido y que tengan
    //  existencia real >= 1. Se agrega como partida normal de empresa 1 a
    //  $0.01. Todo el filtro por precio es contra SOMA (cacheado); la
    //  existencia usa el externo /productos-existencias-real (consistente con
    //  el checkout).
    // ---------------------------------------------------------------------

    private const REGALO_MUESTRA = 12;   // claves por ronda al checar stock
    private const REGALO_RONDAS  = 3;    // rondas maximas de muestreo

    /**
     * Lista cacheada de productos de liquidacion candidatos a regalo (SOMA):
     * [ ['clave','precio_normal','descripcion','marca','clave_proveedor'], ... ]
     */
    private function listaLiquidacionRegalo(): array
    {
        return \Cache::remember('regalo_liquidacion_lista', now()->addMinutes(20), function () {
            try {
                $rows = $this->somaSelect("
                    SELECT DISTINCT ON (p.clave)
                        p.clave,
                        COALESCE(ppr.precio, 0) as precio_normal,
                        TRIM(CONCAT_WS(' ', pw.descripcion_1, pw.descripcion_2, pw.descripcion_3)) as descripcion,
                        COALESCE(m.nombre, '') as marca,
                        COALESCE(prov.clave, '') as clave_proveedor
                    FROM productos_liquidacion pl
                    JOIN productos p ON p.id = pl.id_producto AND p.deleted_at IS NULL
                    LEFT JOIN productos_web pw ON p.id = pw.id_producto AND pw.deleted_at IS NULL
                    LEFT JOIN marcas m ON p.id_marca = m.id AND m.deleted_at IS NULL
                    LEFT JOIN productos_precios ppr ON p.id = ppr.id_producto AND ppr.id_lista_precios = 1 AND ppr.id_sucursal = 1 AND ppr.deleted_at IS NULL
                    LEFT JOIN proveedores prov ON p.id_proveedor = prov.id AND prov.deleted_at IS NULL
                    WHERE pl.deleted_at IS NULL
                    ORDER BY p.clave
                ");
                return array_map(fn ($r) => (array) $r, $rows);
            } catch (\Throwable $e) {
                \Log::warning('listaLiquidacionRegalo fallo: ' . $e->getMessage());
                return [];
            }
        });
    }

    /** Existencia real por lote (externo, consistente con el checkout). */
    private function existenciasReales(array $claves): array
    {
        $claves = array_values(array_filter($claves));
        if (empty($claves)) return [];
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => 'https://owari.appsoma.online/somma/v2.0/api/existencias?' . http_build_query(['claves' => $claves]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT        => 25,
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($body, true);
            // SOMA responde {code, existencias: {clave: n}}; SAE respondia el mapa
            // directo. Se aceptan las dos formas.
            if (is_array($data) && isset($data['existencias']) && is_array($data['existencias'])) {
                return $data['existencias'];
            }
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            \Log::warning('existenciasReales fallo: ' . $e->getMessage());
            return [];
        }
    }

    /** Candidatos por precio (<= 1% subtotal), excluyendo proveedores especiales. */
    private function poolRegalo(float $umbral): array
    {
        $especiales = array_keys($this->obtenerProveedoresEspeciales());
        $pool = [];
        foreach ($this->listaLiquidacionRegalo() as $p) {
            $pn = (float) ($p['precio_normal'] ?? 0);
            if ($pn <= 0 || $pn > $umbral) continue;
            if (!empty($p['clave_proveedor']) && in_array($p['clave_proveedor'], $especiales, true)) continue;
            $pool[$p['clave']] = $p;
        }
        return $pool;
    }

    /**
     * GET regalo/opciones?subtotal=<sin_iva>
     * Devuelve hasta 3 productos de liquidacion que cumplen la regla y tienen
     * stock real >= 1, o {regalo:false}.
     */
    public function regaloOpciones(Request $request)
    {
        $subtotal = (float) $request->query('subtotal', 0);
        if ($subtotal <= 0) return response()->json(['regalo' => false]);

        $umbral = $subtotal * 0.01;
        $pool   = $this->poolRegalo($umbral);
        if (empty($pool)) return response()->json(['regalo' => false]);

        $keys = array_keys($pool);
        shuffle($keys);

        $conStock = [];
        $offset   = 0;
        $ronda    = 0;
        while (count($conStock) < 3 && $offset < count($keys) && $ronda < self::REGALO_RONDAS) {
            $muestra = array_slice($keys, $offset, self::REGALO_MUESTRA);
            $offset += self::REGALO_MUESTRA;
            $ronda++;
            if (empty($muestra)) break;
            $ex = $this->existenciasReales($muestra);
            foreach ($muestra as $clave) {
                if (count($conStock) >= 3) break;
                if ((int) ($ex[$clave] ?? 0) >= 1) $conStock[$clave] = $pool[$clave];
            }
        }

        if (empty($conStock)) return response()->json(['regalo' => false]);

        $opciones = [];
        foreach (array_slice(array_keys($conStock), 0, 3) as $clave) {
            $p = $conStock[$clave];
            $opciones[] = [
                'clave'         => $p['clave'],
                'descripcion'   => $p['descripcion'] ?? '',
                'marca'         => $p['marca'] ?? '',
                'precio_normal' => round((float) ($p['precio_normal'] ?? 0), 2),
            ];
        }

        return response()->json(['regalo' => true, 'opciones' => $opciones]);
    }

    /**
     * GET regalo/validar?clave=&subtotal=<sin_iva>
     * Revalida el regalo elegido (en liquidacion, <=1%, no especial, stock>=1)
     * y devuelve la partida lista para inyectar al pedido (empresa 1, $0.01).
     */
    public function regaloValidar(Request $request)
    {
        $clave    = trim((string) $request->query('clave', ''));
        $subtotal = (float) $request->query('subtotal', 0);
        if ($clave === '' || $subtotal <= 0) return response()->json(['ok' => false, 'motivo' => 'parametros']);

        $umbral = $subtotal * 0.01;

        $cand = null;
        foreach ($this->listaLiquidacionRegalo() as $p) {
            if ($p['clave'] === $clave) { $cand = $p; break; }
        }
        if (!$cand) return response()->json(['ok' => false, 'motivo' => 'no_liquidacion']);

        $pn = (float) ($cand['precio_normal'] ?? 0);
        if ($pn <= 0 || $pn > $umbral) return response()->json(['ok' => false, 'motivo' => 'precio']);

        $especiales = array_keys($this->obtenerProveedoresEspeciales());
        if (!empty($cand['clave_proveedor']) && in_array($cand['clave_proveedor'], $especiales, true)) {
            return response()->json(['ok' => false, 'motivo' => 'especial']);
        }

        $ex = $this->existenciasReales([$clave]);
        if ((int) ($ex[$clave] ?? 0) < 1) return response()->json(['ok' => false, 'motivo' => 'sin_stock']);

        // Partida con la MISMA forma que consultarRegalo (para que fluya por
        // clasificacion.factura -> SAE como empresa 1 a $0.01).
        return response()->json([
            'ok'      => true,
            'partida' => [
                'codigo'              => $clave,
                'descripcion'         => $cand['descripcion'] ?: 'Regalo liquidacion',
                'cantidad'            => 1,
                'precio'              => '0.01',
                'precio_iva'          => '0.01',
                'total'               => '0.01',
                'existencia_sae'      => 999,
                'existencia_factura'  => 999,
                'existencia_remision' => -1,
                'clave_proveedor'     => '',
                'es_regalo'           => true,
                'es_regalo_liquidacion' => true,
            ],
        ]);
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

        $this->carritoSvc()->vaciarTodo();

        $titulo = "<br>
        Nos comunicaremos contigo para darle seguimiento.
        Favor de estar al pendiente del numero de telefono que nos proporcionaste.<br><br>";
        return view('tienda_online.exito_pendiente', compact('titulo', 'id_pedido'));
    }


    // ---------------------------------------------------------------------
    //  Carrito auxiliar (rapido): solo sesion, sin analisis de precios.
    //  Cuando el carrito normal tarda por el analisis, el cliente ve aqui
    //  su pedido (clave + cantidad), lo descarga en Excel y telemarketing
    //  lo captura. Tambien permite vaciar todo.
    // ---------------------------------------------------------------------

    /**
     * Junta el carrito normal y el especial en filas [clave, cantidad],
     * sumando cantidades si una misma clave aparece repetida. Lee el carrito
     * persistido (tabla), asi que es instantaneo (sin analisis de precios).
     */
    private function itemsCarritoRapido(): array
    {
        $svc = $this->carritoSvc();
        $porClave = [];
        foreach (['normal', 'especial'] as $llave) {
            $carrito = $svc->obtener($llave);
            if (!is_array($carrito)) continue;
            foreach ($carrito as $item) {
                $clave = $item['numero_parte'] ?? null;
                if ($clave === null || $clave === '') continue;
                $porClave[$clave] = ($porClave[$clave] ?? 0) + (int) ($item['cantidad'] ?? 0);
            }
        }

        // Descripcion desde SOMA (una sola consulta por las claves del carrito).
        // Es rapido: whereIn sobre pocas claves; las que no existan en SOMA
        // (PROMOCIONAL, etc.) quedan con descripcion vacia.
        $descripciones = [];
        $claves = array_keys($porClave);
        if (!empty($claves)) {
            $rows = \DB::connection('owari_soma')->table('productos as p')
                ->leftJoin('productos_web as pw', function ($j) {
                    $j->on('pw.id_producto', '=', 'p.id')->whereNull('pw.deleted_at');
                })
                ->whereIn('p.clave', $claves)
                ->whereNull('p.deleted_at')
                ->get(['p.clave', 'pw.descripcion_1', 'pw.descripcion_2', 'pw.descripcion_3']);
            foreach ($rows as $r) {
                if (isset($descripciones[$r->clave])) continue;
                $descripciones[$r->clave] = trim(implode(' ', array_filter([
                    $r->descripcion_1, $r->descripcion_2, $r->descripcion_3,
                ])));
            }
        }

        $items = [];
        foreach ($porClave as $clave => $cant) {
            $items[] = [
                'clave'       => $clave,
                'descripcion' => $descripciones[$clave] ?? '',
                'cantidad'    => $cant,
            ];
        }
        return $items;
    }

    /** Vista del carrito auxiliar (clave + cantidad, sin analisis). */
    public function carritoRapido()
    {
        $titulo       = "Carrito rápido";
        $items        = $this->itemsCarritoRapido();
        $total_piezas = array_sum(array_column($items, 'cantidad'));
        return view('tienda_online.carrito_rapido', compact('titulo', 'items', 'total_piezas'));
    }

    /** Descarga el carrito auxiliar como Excel (CLAVE, CANTIDAD). */
    public function carritoRapidoExcel()
    {
        $items = $this->itemsCarritoRapido();

        $filas = [['CLAVE', 'DESCRIPCION', 'CANTIDAD']];
        foreach ($items as $it) {
            $filas[] = [$it['clave'], $it['descripcion'] ?? '', $it['cantidad']];
        }

        $nombre = 'carrito-' . \Auth::user()->clave_cliente . '-' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CarritoRapidoExport($filas), $nombre);
    }

    /** Vacia por completo el carrito (normal y especial) y regresa al aux. */
    public function vaciarCarritoRapido()
    {
        $this->carritoSvc()->vaciarTodo();
        \Session::flash('status', 'Tu carrito se vació correctamente.');
        return redirect()->route('tienda_online.carrito_rapido');
    }

    // ---------------------------------------------------------------------
    //  Migracion del carrito viejo (sesion) al carrito nuevo (BD).
    //  Al pasar el carrito a base de datos, los productos que el cliente
    //  tenia guardados en su sesion anterior siguen fisicamente ahi (las
    //  llaves 'cart'/'cartEspecial') hasta que expire la sesion. Estas
    //  funciones se los muestran y le permiten importarlos al carrito de BD
    //  para que no pierda lo que habia acumulado.
    // ---------------------------------------------------------------------

    /** Lee el carrito viejo que quedo en la sesion (antes del cambio a BD). */
    private function itemsCarritoAnterior(): array
    {
        $out = ['normal' => [], 'especial' => []];
        foreach (['normal' => 'cart', 'especial' => 'cartEspecial'] as $tipo => $sesKey) {
            $viejo = \Session::get($sesKey, []);
            if (is_array($viejo)) {
                foreach ($viejo as $it) {
                    if (is_array($it) && !empty($it['numero_parte'])) {
                        $out[$tipo][] = $it;
                    }
                }
            }
        }
        return $out;
    }

    /** Pantalla que muestra las partidas del carrito viejo (sesion). */
    public function carritoAnterior()
    {
        $titulo   = "Recuperar carrito anterior";
        $anterior = $this->itemsCarritoAnterior();

        // Aplanado clave+cantidad para mostrar (normal + especial juntos).
        $items = [];
        foreach (['normal', 'especial'] as $tipo) {
            foreach ($anterior[$tipo] as $it) {
                $items[] = [
                    'clave'    => $it['numero_parte'],
                    'cantidad' => (int) ($it['cantidad'] ?? 0),
                    'tipo'     => $tipo,
                ];
            }
        }

        $total_piezas = array_sum(array_column($items, 'cantidad'));
        return view('tienda_online.carrito_anterior', compact('titulo', 'items', 'total_piezas'));
    }

    /**
     * Importa las partidas del carrito viejo (sesion) al carrito nuevo (BD),
     * fusionandolas con lo que ya tenga en BD (suma cantidades por clave), y
     * limpia la sesion vieja para que ya no vuelva a aparecer.
     */
    public function importarCarritoAnterior()
    {
        $svc      = $this->carritoSvc();
        $anterior = $this->itemsCarritoAnterior();

        foreach (['normal', 'especial'] as $tipo) {
            $viejos = $anterior[$tipo];
            if (empty($viejos)) continue;

            // Indexar lo que ya hay en BD por clave.
            $porClave = [];
            foreach ($svc->obtener($tipo) as $it) {
                if (!empty($it['numero_parte'])) $porClave[$it['numero_parte']] = $it;
            }

            // Fusionar los viejos: si ya existe la clave, suma cantidad; si no,
            // se agrega tal cual (conserva su 'partida').
            foreach ($viejos as $it) {
                $clave = $it['numero_parte'];
                if (isset($porClave[$clave])) {
                    $porClave[$clave]['cantidad'] = (int) ($porClave[$clave]['cantidad'] ?? 0) + (int) ($it['cantidad'] ?? 0);
                } else {
                    $porClave[$clave] = $it;
                }
            }

            $svc->guardar($tipo, array_values($porClave));
        }

        // La sesion vieja ya no se necesita.
        \Session::forget(['cart', 'cartEspecial']);

        \Session::flash('status', 'Tu carrito anterior se agregó correctamente a tu carrito.');
        return redirect()->route('tienda_online.carrito');
    }

}
