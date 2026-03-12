<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
\Auth::routes(['verify' => true]);

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/ver_pdf/{uuid}', [App\Http\Controllers\TiendaOnlineController::class, 'verPdf'])->name('ver_pdf');
Route::get('/cfdi/{uuid}.zip', [App\Http\Controllers\TiendaOnlineController::class, 'downloadZip'])->name('cfdi.zip');


Route::get('/permisos', [App\Http\Controllers\PermisosController::class, 'index'])->name('permisos.crear'); //solo se usa para crear los permisos en las tablas


Route::get('/pl', [App\Http\Controllers\TiendaOnlineController::class, 'pantallaLiquidaciones']);
Route::get('/demo/factura', [App\Http\Controllers\HomeController::class, 'demoFactura']);

Route::get('/clientes/login', [App\Http\Controllers\TiendaOnlineController::class, 'login'])->name('tienda_online.login');
Route::post('/clientes/login', [App\Http\Controllers\TiendaOnlineController::class, 'iniciarSesion'])->name('tienda_online.iniciar_sesion');
Route::get('/clientes/registro', [App\Http\Controllers\TiendaOnlineController::class, 'registro'])->name('tienda_online.registro');
Route::get('/clientes/registro_nuevo', [App\Http\Controllers\TiendaOnlineController::class, 'registro_nuevo'])->name('tienda_online.registro_nuevo');
Route::post('/clientes/registrar', [App\Http\Controllers\TiendaOnlineController::class, 'registrar'])->name('tienda_online.registrar');
Route::post('/clientes/registrar_nuevo', [App\Http\Controllers\TiendaOnlineController::class, 'registrar_nuevo'])->name('tienda_online.registrar_nuevo');
Route::get('/cerrar_sesion', [App\Http\Controllers\TiendaOnlineController::class, 'logout'])->name('tienda_online.logout');

Route::get('/aux', [App\Http\Controllers\TiendaOnlineController::class, 'aux'])->name('tienda_online.aux');

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/productos/reporte-inventario', [App\Http\Controllers\HomeController::class, 'reporteInventario'])->name('productos.reporte_inventario');
Route::get('/productos/reporte-negados', [App\Http\Controllers\HomeController::class, 'reporteNegados'])->name('productos.reporte_negados');
Route::get('/productos/larga-venta', [App\Http\Controllers\HomeController::class, 'reporteLargaVenta'])->name('productos.larga_venta');


Route::get('/pedidos/crear', [App\Http\Controllers\PedidosController::class, 'crear'])->name('pedidos.crear');
Route::get('/pedidos/demo', [App\Http\Controllers\PedidosController::class, 'demo'])->name('pedidos.demo');
Route::post('/pedidos/guardar', [App\Http\Controllers\PedidosController::class, 'guardar'])->name('pedidos.guardar');
Route::post('pedidos/guardar_especial', [App\Http\Controllers\PedidosController::class, 'guardarPedidoEspecial'])->name('pedidos.guardar_especial');

Route::post('pedidos/guardar_pedido_pendiente_web', [App\Http\Controllers\PedidosController::class, 'guardarPedidoPendienteWeb'])->name('pedidos.guardar_pedido_pendiente_web');


Route::get('/etiquetas/crear', [App\Http\Controllers\EtiquetasController::class, 'crear'])->name('etiquetas.crear');
Route::get('/etiquetas/pdf', [App\Http\Controllers\EtiquetasController::class, 'pdf'])->name('etiquetas.pdf');
Route::get('/etiquetas/ver_pdf', [App\Http\Controllers\EtiquetasController::class, 'verPdf'])->name('etiquetas.ver_pdf');
Route::get('/etiquetas/crear_paquetes', [App\Http\Controllers\EtiquetasController::class, 'crearPaquetes'])->name('etiquetas.crear_paquetes');
Route::get('/etiquetas/pdf_paquetes', [App\Http\Controllers\EtiquetasController::class, 'pdfPaquetes'])->name('etiquetas.pdf_paquetes');
Route::get('/etiquetas/etiquetas_compra', [App\Http\Controllers\EtiquetasController::class, 'crearCompra'])->name('etiquetas.etiquetas_compra');
Route::get('/etiquetas/pdf_compra', [App\Http\Controllers\EtiquetasController::class, 'pdfCompra'])->name('etiquetas.pdf_compra');


Route::get('/usuarios', [App\Http\Controllers\UsuariosController::class, 'index'])->name('usuarios.index');
Route::get('/usuarios/agregar', [App\Http\Controllers\UsuariosController::class, 'agregar'])->name('usuarios.agregar');
Route::post('/usuarios/crear', [App\Http\Controllers\UsuariosController::class, 'crear'])->name('usuarios.crear');
Route::get('/usuarios/{usuario}/editar', [App\Http\Controllers\UsuariosController::class, 'editar'])->name('usuarios.editar');
Route::put('/usuarios/{usuario}/actualizar', [App\Http\Controllers\UsuariosController::class, 'actualizar'])->name('usuarios.actualizar');
Route::delete('/usuarios/eliminar', [App\Http\Controllers\UsuariosController::class, 'eliminar'])->name('usuarios.eliminar');

Route::get('/clientes', [App\Http\Controllers\ClientesController::class, 'index'])->name('clientes.index');
Route::get('/clientes/agregar', [App\Http\Controllers\ClientesController::class, 'agregar'])->name('clientes.agregar');
Route::post('/clientes/crear', [App\Http\Controllers\ClientesController::class, 'crear'])->name('clientes.crear');
Route::get('/clientes/{cliente}/editar', [App\Http\Controllers\ClientesController::class, 'editar'])->name('clientes.editar');
Route::put('/clientes/{cliente}/actualizar', [App\Http\Controllers\ClientesController::class, 'actualizar'])->name('clientes.actualizar');
Route::delete('/clientes/eliminar', [App\Http\Controllers\ClientesController::class, 'eliminar'])->name('clientes.eliminar');
Route::get('/clientes/ventas', [App\Http\Controllers\ClientesController::class, 'ventas'])->name('clientes.ventas');


Route::get('/compras/plantilla/productos_nuevos', [App\Http\Controllers\ComprasController::class, 'plantillaProductosNuevos'])->name('compras.plantilla.productos_nuevos');
Route::get('/compras/plantilla/compras', [App\Http\Controllers\ComprasController::class, 'plantillaCapturaCompra'])->name('compras.plantilla.captura_compra');
Route::post('/compras/excel/plantilla/productos/nuevos', [App\Http\Controllers\ComprasController::class, 'excelProductosNuevos'])->name('compras.excel.productos.nuevos');
Route::get('/compras/plantilla/descargar', [App\Http\Controllers\ComprasController::class, 'descargarPlantilla'])->name('compras.plantilla.descargar');
Route::post('/compras/excel/plantilla/requisicion', [App\Http\Controllers\ComprasController::class, 'excelRequisicion'])->name('compras.excel.requisicion');




Route::middleware(['auth', 'verified'])->prefix('tienda_online')->group(function () {
    //
    Route::get('dashboard', [App\Http\Controllers\TiendaOnlineController::class, 'dashboard'])->name('tienda_online.dashboard');
    Route::get('productos', [App\Http\Controllers\TiendaOnlineController::class, 'productos'])->name('tienda_online.productos');
    Route::get('producto/{clave}', [App\Http\Controllers\TiendaOnlineController::class, 'detalleProducto'])->name('tienda_online.detalles_producto');
    Route::get('producto_demo/{clave}', [App\Http\Controllers\TiendaOnlineController::class, 'detalleProductoDemo'])->name('tienda_online.detalles_producto_demo');
    Route::post('productos/actualizar-favoritos', [App\Http\Controllers\TiendaOnlineController::class, 'actualizarFavoritos'])->name('tienda_online.actualizar_favoritos');
    Route::get('productos/favoritos', [App\Http\Controllers\TiendaOnlineController::class, 'favoritos'])->name('tienda_online.favoritos');
    Route::post('carrito/actualizar', [App\Http\Controllers\TiendaOnlineController::class, 'actualizarCarrito'])->name('tienda_online.carrito_actualizar');
    Route::post('carrito/actualizar_especial', [App\Http\Controllers\TiendaOnlineController::class, 'actualizarCarritoEspecial'])->name('tienda_online.carrito_actualizar_especial');
    Route::get('carrito', [App\Http\Controllers\TiendaOnlineController::class, 'carrito'])->name('tienda_online.carrito');
    Route::get('carrito_aux', [App\Http\Controllers\TiendaOnlineController::class, 'carritoAux'])->name('tienda_online.carrito_aux');
    Route::post('carrito/guardar_pedido', [App\Http\Controllers\TiendaOnlineController::class, 'guardarPedido'])->name('tienda_online.guardar_pedido');
    Route::get('pedido/guardado_exitoso', [App\Http\Controllers\TiendaOnlineController::class, 'guardadoExitoso'])->name('tienda_online.guardado_exitoso');
    Route::get('pedido/guardado_pendiente_exitoso', [App\Http\Controllers\TiendaOnlineController::class, 'guardadoPendienteExitoso'])->name('tienda_online.guardado_pendiente_exitoso');
    Route::get('pedidos', [App\Http\Controllers\TiendaOnlineController::class, 'misPedidos'])->name('tienda_online.pedidos');
    Route::get('pedidos/detalle', [App\Http\Controllers\TiendaOnlineController::class, 'detallePedido'])->name('tienda_online.detalle_pedido');
    Route::get('pedidos/detalle_especial', [App\Http\Controllers\TiendaOnlineController::class, 'detallePedidoEspecial'])->name('tienda_online.detalle_pedido_especial');
    Route::post('carrito/excel', [App\Http\Controllers\TiendaOnlineController::class, 'excelCarrito'])->name('tienda_online.excel_carrito');
    Route::get('descuentos', [App\Http\Controllers\TiendaOnlineController::class, 'descuentos'])->name('tienda_online.descuentos');
    Route::get('liquidacion', [App\Http\Controllers\TiendaOnlineController::class, 'liquidacion'])->name('tienda_online.liquidacion');
    Route::get('vaciar_carrito', [App\Http\Controllers\TiendaOnlineController::class, 'vaciarCarrito'])->name('tienda_online.vaciar_carrito');
    Route::get('editar_cliente', [App\Http\Controllers\TiendaOnlineController::class, 'editarCliente'])->name('tienda_online.editar_cliente');
    Route::post('actualizar_password', [App\Http\Controllers\TiendaOnlineController::class, 'actualizarPassword'])->name('tienda_online.actualizar_password');
    Route::post('actualizar_tiendita', [App\Http\Controllers\TiendaOnlineController::class, 'actualizarTiendita'])->name('tienda_online.actualizar_tiendita');
    Route::get('generar_pdf', [App\Http\Controllers\TiendaOnlineController::class, 'generarPDF'])->name('tienda_online.generar_pdf');

    Route::get('/pedidos_especiales', [App\Http\Controllers\PedidosEspecialesController::class, 'index'])->name('pedidos_especiales.index');
    Route::delete('/pedidos_especiales/eliminar', [App\Http\Controllers\PedidosEspecialesController::class, 'eliminar'])->name('pedidos_especiales.eliminar');
    Route::get('/pedidos_especiales/{pedido}/ver', [App\Http\Controllers\PedidosEspecialesController::class, 'ver'])->name('pedidos_especiales.ver');

    Route::get('/pedidos_pendientes', [App\Http\Controllers\PedidosPendientesController::class, 'index'])->name('pedidos_pendientes.index');
    Route::delete('/pedidos_pendientes/eliminar', [App\Http\Controllers\PedidosPendientesController::class, 'eliminar'])->name('pedidos_pendientes.eliminar');
    Route::get('/pedidos_pendientes/{pedido}/ver', [App\Http\Controllers\PedidosPendientesController::class, 'ver'])->name('pedidos_pendientes.ver');


    Route::post('/pedidos_especiales_sae/guardar', [App\Http\Controllers\PedidosEspecialesSaeController::class, 'guardarPedidoEspecialSae'])->name('pedidos_especiales_sae.guardar');

    Route::get('/pedidos_especiales_sae/test', [App\Http\Controllers\PedidosEspecialesSaeController::class, 'test'])->name('pedidos_especiales.test');

    Route::get('/pedidos/ver_factura_sae', [App\Http\Controllers\TiendaOnlineController::class, 'facturaSAE'])->name('tienda_online.factura_sae');

    Route::get('/catalogo/generar', [App\Http\Controllers\TiendaOnlineController::class, 'generarCatalogo'])->name('tienda_online.generar_catalogo');



    Route::get('/reporte-conteo', [App\Http\Controllers\ConteoExportController::class, 'showForm'])->name('conteo.form'); // Le damos un nombre para llamarla fácilmente

    // 2. (MODIFICADA) Esta ruta recibirá la solicitud del formulario
    // Nota: Quitamos el /{conteo} de la URL.
    Route::get('/exportar-conteo', [App\Http\Controllers\ConteoExportController::class, 'exportar'])->name('conteo.exportar');
    Route::get('/carrito-sesion', [App\Http\Controllers\TiendaOnlineController::class, 'carritoSesion'])->name('carrito.sesion');



});
