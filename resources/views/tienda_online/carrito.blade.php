@extends('tienda_online.base.base')
@section('contenido')
    <!-- Start Cart Area -->
    @if (count($productos) <= 0 && count($productos_especiales) <= 0)
        <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class=" mb-30">
                            <h2>No hay productos en tu carrito</h2>
                            <a href="{{ route('tienda_online.productos') }}?q=&p=1"class="default-btn"
                                style="margin-top: 20px;">
                                Ver y buscar productos
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class=" mb-30">
                                <br><br>
                                <h4>o sube tu excel <a href="{{ asset('/tienda_online/PlantillaOwari.xlsx') }}"
                                        target=_blank>(descarga la plantilla aqui)</a></h4>
                                <div class="input-group mb-2">
                                    <input type="file" class="form-control" id="inputGroupFile04"
                                        aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="inputGroupFileAddon04">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                    </div>
        </section>
        <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel2">Estamos analizando tu archivo...</h5>
                    </div>
                    <div class="modal-body modal-excel">
                        <h5>Espera un momento, estamos analizando tu archivo...</h5>
                    </div>
                    <div class="modal-footer botones-excel" style="display: none;">
                        <a class="default-btn" href="{{ route('tienda_online.carrito') }}">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <?php
        $i = 0;
        ?>
        <script type="text/javascript">
            var gran_total = 0;
            var gran_total_especial = 0;
            var partidas_finales = [];
            var partidas_especiales_finales = [];
            var partidas = [];
            var partidas_especiales = [];

            // Catalogo de proveedores especiales (cargado al iniciar la pagina desde SOMA).
            // Mapa { 'S227': {clave, nombre, tipo_separacion, stock_ficticio}, ... }
            // Se declara en el primer <script> para que los <script> inline de cada
            // producto (mas abajo en el foreach) puedan llamar a `obtenerStockFicticio`.
            // Toda funcion async que dependa del mapa debe await `proveedoresEspecialesListos`.
            var PROVEEDORES_ESPECIALES = {};

            var proveedoresEspecialesListos = fetch('https://owari.appsoma.online/somma/v2.0/api/proveedores-especiales')
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    PROVEEDORES_ESPECIALES = Object.fromEntries(
                        (data.proveedores || []).map(function(p) { return [p.clave, p]; })
                    );
                    return true;
                })
                .catch(function(e) {
                    console.warn('No se pudo cargar proveedores_especiales:', e);
                    PROVEEDORES_ESPECIALES = {};
                    return false;
                });

            // Helpers data-driven — NUNCA hardcodear S227 ni ninguna otra clave.
            function configProveedor(claveProveedor) {
                if (!claveProveedor) return null;
                return PROVEEDORES_ESPECIALES[claveProveedor] || null;
            }
            function obtenerStockFicticio(claveProveedor) {
                var cfg = configProveedor(claveProveedor);
                if (!cfg || cfg.tipo_separacion !== 'split_por_stock') return 0;
                return parseInt(cfg.stock_ficticio) || 0;
            }
        </script>
        <section class="cart-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 col-md-7">
                        <div class=" mb-30">
                            <br><br>
                            <h4>Tambien puedes subir tu excel <a href="{{ asset('/tienda_online/PlantillaOwari.xlsx') }}"
                                    target=_blank>(descarga la plantilla aqui)</a></h4>
                            <div class="input-group mb-2">
                                <input type="file" class="form-control" id="inputGroupFile04"
                                    aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                <button class="btn btn-outline-secondary" type="button" id="inputGroupFileAddon04">Agregar
                                    al carrito</button>
                            </div>
                        </div>
                    </div>
                    @if (count($productos) > 0)
                        <div class="col-lg-8 col-md-12">
                            <h4>Carrito (se surte de bodega)</h4>
                            <form>
                                <div class="cart-table table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Clave</th>
                                                <th>Descripción</th>
                                                <th>Precio</th>
                                                <th>Cantidad</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($productos as $key => $producto)
                                                <?php
                                                $i++;
                                                $producto = (object) $producto;
                                                if (str_contains($producto->codigo_nikko, '/')) {
                                                    $codigo_nikko = str_replace('/', ':', $producto->codigo_nikko);
                                                } else {
                                                    $codigo_nikko = $producto->codigo_nikko;
                                                }
                                                
                                                $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/' . $codigo_nikko;
                                                if (file_exists($directory)) {
                                                    $files = \Storage::disk('cms')->allFiles('productos/' . $codigo_nikko . '/');
                                                } else {
                                                    $files = [];
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="remove quitar_producto"
                                                            data-numero="{{ $producto->codigo_nikko }}"><i
                                                                class='bi bi-x-circle'></i></a>
                                                    </td>
                                                    <td class="product-thumbnail">
                                                        <a href="#">
                                                            @if (count($files) > 0)
                                                                <img src="{{ 'https://owari.com.mx/storage/productos/' . $codigo_nikko . '/' . basename($files[0], PHP_EOL) }}"
                                                                    alt="Product Image">
                                                            @else
                                                                <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}"
                                                                    alt="Product Image">
                                                            @endif
                                                        </a>
                                                    </td>

                                                    <td class="product-name">
                                                        <span class="numero_parte">
                                                            <a
                                                                href="{{ route('tienda_online.detalles_producto', $producto->codigo_nikko) }}">
                                                                <b>{{ $producto->codigo_nikko }}</b>
                                                            </a>
                                                        </span>
                                                        <span class="posicion">
                                                            <b>Marca: </b>{{ $producto->marca_comercial }}
                                                        </span>
                                                        <span class="posicion">
                                                            {{ $producto->descripcion_1 }}
                                                        </span>
                                                        @if (isset($producto->sustituto))
                                                            @if ($producto->sustituto == 'true')
                                                                <span class="posicion" style="color:red">
                                                                    Producto equivalent de Excel
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </td>

                                                    <td class="product-price">
                                                        <span class="precio-unitario-{{ $i }}">$0.00</span>
                                                    </td>

                                                    <td class="product-quantity">
                                                        <small
                                                            style="color:red; display:block;">{!! $producto->mensaje_existencia !!}</small>
                                                        @if ($producto->solicitado > 0)
                                                            <div class="input-counter">
                                                                <span class="minus-btn"><i class='bx bx-minus'></i></span>
                                                                <input type="text" value="{{ $producto->solicitado }}"
                                                                    min="1" max="1"
                                                                    class="cantidad_{{ $i }}">
                                                                <span class="plus-btn"><i class='bx bx-plus'></i></span>
                                                            </div>
                                                            @if ($producto->codigo_nikko != 'MOCHILA')
                                                                <button
                                                                    class="actualizar_producto update_{{ $i }}"
                                                                    data-i="{{ $i }}"
                                                                    data-numero="{{ $producto->codigo_nikko }}"
                                                                    data-maximo="1"><i class="bx bx-refresh"></i> Actualizar
                                                                    cantidad</button>
                                                            @endif
                                                        @else
                                                            <button data-clave="{{ $producto->codigo_nikko }}"
                                                                data-cantidad="{{ $producto->solicitado_original }}"
                                                                data-partida="{{ json_encode($producto->partida) }}"
                                                                class="pasar_especial_producto boton_especial_{{ $i }}"
                                                                type="button"><i class="bx bx-refresh"></i> Pasar a pedido
                                                                especial</button>
                                                            <span style="color:red; display:none;"
                                                                class="es_liquidacion_{{ $i }}">Son existencias
                                                                unicas, apresurate a cerrar tu pedido.</span>
                                                        @endif

                                                    </td>

                                                    <td class="product-subtotal">
                                                        <span class="total_partida_{{ $i }}">$0.00</span>
                                                    </td>
                                                    <script>
                                                        var obj = $.parseJSON('{!! html_entity_decode(str_replace(['\'', '\"'], '', json_encode($producto->partida))) !!}');
                                                        var obj_{{ $i }} = $.parseJSON('{!! html_entity_decode(str_replace(['\'', '\"'], '', json_encode($producto->partida))) !!}');
                                                        var producto_partida = obj;

                                                        //analisis para saber que politiva le toca
                                                        var notas = "";
                                                        var precio = obj.precio_publico;
                                                        var precio_iva = obj.precio_iva;
                                                        var cantidad = {{ $producto->solicitado }};
                                                        //console.log("TAMANO:" + obj.descuentos.length);
                                                        var porcentaje = 1;
                                                        @if (isset(\Auth::user()->clienteData))
                                                            @if (\Auth::user()->clienteData->tiendita)

                                                                porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }} / 100);
                                                            @endif
                                                        @endif
                                                        var notas = "";

                                                        if (obj.en_liquidacion) {
                                                            $(".boton_especial_{{ $i }}").hide();
                                                            $(".es_liquidacion_{{ $i }}").text("Este producto ya no esta disponible.");
                                                        }


                                                        console.log(obj);
                                                        if (obj.descuentos.length > 1) {
                                                            for (i = obj.descuentos.length - 1; i >= 1; i--) {
                                                                //console.log("comenzamos = " + i);
                                                                if (obj.descuentos[i].tipo == "S") {
                                                                    //console.log(obj.descuentos[i].tipo + " " + i);
                                                                    if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                                                        //console.log("cero unidades");
                                                                        precio = obj.descuentos[i].precio_lista;
                                                                        precio_iva = obj.descuentos[i].precio_iva;
                                                                        break;
                                                                    } else {
                                                                        if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                                                            //console.log("hay unidades y cubrimos");
                                                                            precio = obj.descuentos[i].precio_lista;
                                                                            precio_iva = obj.descuentos[i].precio_iva;
                                                                            break;
                                                                        } else {
                                                                            notas +=
                                                                                "Si compras <b>" +
                                                                                parseFloat(obj.descuentos[i].unidades_minimas).toFixed(0) +
                                                                                "</b>&nbsp;el precio es de <b>$ " +
                                                                                parseFloat(obj.descuentos[i].precio_lista * porcentaje).toFixed(2) + "</b><br>";
                                                                            continue;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            precio = obj.descuentos[0].precio_lista;
                                                            precio_iva = obj.descuentos[0].precio_iva;
                                                        }

                                                        var existenciaSae = {{ (int) data_get($producto, 'existencia_real_sae', 0) }};
                                                        // Data-driven desde SOMA — NUNCA hardcodear claves de proveedor.
                                                        // Si SOMA caido / no registrado, stockFicticio=0 (sin efecto).
                                                        obj.existencia = existenciaSae + obtenerStockFicticio('{{ data_get($producto, 'clave_proveedor', '') }}');
                                                        $('.precio-unitario-{{ $i }}').html("$ " + parseFloat(precio * porcentaje).toFixed(2));
                                                        $('.total_partida_{{ $i }}').html("$ " + parseFloat(precio * cantidad * porcentaje).toFixed(2));
                                                        $('.cantidad_{{ $i }}').attr('max', obj.existencia);
                                                        $('.update_{{ $i }}').attr('data-maximo', obj.existencia);
                                                        @if ($producto->solicitado > 0)
                                                            partidas_finales.push({
                                                                "codigo": '{{ $producto->codigo_nikko }}',
                                                                "descripcion": '{{ $producto->descripcion_1 }}',
                                                                "cantidad": cantidad,
                                                                "precio": parseFloat(precio).toFixed(2),
                                                                "precio_iva": parseFloat(precio_iva).toFixed(2),
                                                                "total": (cantidad * precio).toFixed(2),
                                                                "existencia_sae": existenciaSae,
                                                                "existencia_factura": parseInt(obj.existencia_factura),
                                                                "existencia_remision": parseInt(obj.existencia_remision),
                                                                "clave_proveedor": '{{ data_get($producto, 'clave_proveedor', '') }}'
                                                            })
                                                            partidas.push(producto_partida);

                                                            gran_total += precio * cantidad;
                                                        @endif
                                                    </script>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    @endif
                    @if (count($productos_especiales) > 0)
                        <div class="col-lg-8 col-md-12" style="margin-top:20px">
                            <h4>Carrito especial (se solicita a proveedor)</h4>
                            <form>
                                <div class="cart-table table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Clave</th>
                                                <th>Descripción</th>
                                                <th>Precio</th>
                                                <th>Cantidad</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @foreach ($productos_especiales as $key => $producto)
                                                <?php
                                                $i++;
                                                $producto = (object) $producto;
                                                if (str_contains($producto->codigo_nikko, '/')) {
                                                    $codigo_nikko = str_replace('/', ':', $producto->codigo_nikko);
                                                } else {
                                                    $codigo_nikko = $producto->codigo_nikko;
                                                }
                                                
                                                $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/' . $codigo_nikko;
                                                if (file_exists($directory)) {
                                                    $files = \Storage::disk('cms')->allFiles('productos/' . $codigo_nikko . '/');
                                                } else {
                                                    $files = [];
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="remove quitar_producto_especial"
                                                            data-numero="{{ $producto->codigo_nikko }}"><i
                                                                class='bi bi-x-circle'></i></a>
                                                    </td>
                                                    <td class="product-thumbnail">
                                                        <a href="#">
                                                            @if (count($files) > 0)
                                                                <img src="{{ 'https://owari.com.mx/storage/productos/' . $codigo_nikko . '/' . basename($files[0], PHP_EOL) }}"
                                                                    alt="Product Image">
                                                            @else
                                                                <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}"
                                                                    alt="Product Image">
                                                            @endif
                                                        </a>
                                                    </td>

                                                    <td class="product-name">
                                                        <span class="numero_parte">
                                                            <a
                                                                href="{{ route('tienda_online.detalles_producto', $producto->codigo_nikko) }}">
                                                                <b>{{ $producto->codigo_nikko }}</b>
                                                            </a>
                                                        </span>
                                                        <span class="posicion">
                                                            <b>Marca: </b>{{ $producto->marca_comercial }}
                                                        </span>
                                                        <span class="posicion">
                                                            {{ $producto->descripcion_1 }}
                                                        </span>
                                                        @if (isset($producto->sustituto))
                                                            @if ($producto->sustituto == 'true')
                                                                <span class="posicion" style="color:red">
                                                                    Producto equivalent de Excel
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </td>

                                                    <td class="product-price">
                                                        <span class="precio-unitario-{{ $i }}">$0.00</span>
                                                    </td>

                                                    <td class="product-quantity">
                                                        <div class="input-counter">
                                                            <span class="minus-btn"><i class='bx bx-minus'></i></span>
                                                            <input type="text" value="{{ $producto->solicitado }}"
                                                                min="1" max="1"
                                                                class="cantidad_{{ $i }}">
                                                            <span class="plus-btn"><i class='bx bx-plus'></i></span>
                                                        </div>
                                                        <button
                                                            class="actualizar_producto_especial update_{{ $i }}"
                                                            data-i="{{ $i }}"
                                                            data-numero="{{ $producto->codigo_nikko }}"
                                                            data-maximo="1"><i class="bx bx-refresh"></i> Actualizar
                                                            cantidad</button>

                                                    </td>

                                                    <td class="product-subtotal">
                                                        <span class="total_partida_{{ $i }}">$0.00</span>
                                                    </td>
                                                    <script>
                                                        var obj = $.parseJSON('{!! html_entity_decode(str_replace(['\'', '\"'], '', json_encode($producto->partida))) !!}');
                                                        var obj_{{ $i }} = $.parseJSON('{!! html_entity_decode(str_replace(['\'', '\"'], '', json_encode($producto->partida))) !!}');
                                                        var producto_partida = obj;

                                                        //analisis para saber que politiva le toca
                                                        var notas = "";
                                                        var precio = obj.precio_publico;
                                                        var precio_iva = obj.precio_iva;
                                                        var cantidad = {{ $producto->solicitado }};
                                                        //console.log("TAMANO:" + obj.descuentos.length);
                                                        var porcentaje = 1;
                                                        @if (isset(\Auth::user()->clienteData))
                                                            @if (\Auth::user()->clienteData->tiendita)

                                                                porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }} / 100);
                                                            @endif
                                                        @endif
                                                        var notas = "";
                                                        if (obj.descuentos.length > 1) {
                                                            for (i = obj.descuentos.length - 1; i >= 1; i--) {
                                                                //console.log("comenzamos = " + i);
                                                                if (obj.descuentos[i].tipo == "S") {
                                                                    //console.log(obj.descuentos[i].tipo + " " + i);
                                                                    if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                                                        //console.log("cero unidades");
                                                                        precio = obj.descuentos[i].precio_lista;
                                                                        precio_iva = obj.descuentos[i].precio_iva;
                                                                        break;
                                                                    } else {
                                                                        if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                                                            //console.log("hay unidades y cubrimos");
                                                                            precio = obj.descuentos[i].precio_lista;
                                                                            precio_iva = obj.descuentos[i].precio_iva;
                                                                            break;
                                                                        } else {
                                                                            notas +=
                                                                                "Si compras <b>" +
                                                                                parseFloat(obj.descuentos[i].unidades_minimas).toFixed(0) +
                                                                                "</b>&nbsp;el precio es de <b>$ " +
                                                                                parseFloat(obj.descuentos[i].precio_lista * porcentaje).toFixed(2) + "</b><br>";
                                                                            continue;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            precio = obj.descuentos[0].precio_lista;
                                                            precio_iva = obj.descuentos[0].precio_iva;
                                                        }


                                                        if (obj.cliente == "N/A") {
                                                            precio = {{ $producto->precio_normal }}
                                                            const first = obj?.clasificacion?.[0]?.toUpperCase?.() ?? '';
                                                            const mapa = {
                                                                A: 0.85,
                                                                B: 0.9,
                                                                C: 0.95
                                                            };
                                                            const descuento = mapa[first] ?? 0.95;
                                                            console.log(descuento);
                                                            precio = precio * descuento * 1.16;
                                                        }

                                                        // Data-driven desde SOMA — NUNCA hardcodear claves de proveedor.
                                                        obj.existencia = parseInt(obj.existencia) + obtenerStockFicticio('{{ data_get($producto, 'clave_proveedor', '') }}');
                                                        $('.precio-unitario-{{ $i }}').html("$ " + parseFloat(precio * porcentaje).toFixed(2));
                                                        $('.total_partida_{{ $i }}').html("$ " + parseFloat(precio * cantidad * porcentaje).toFixed(2));
                                                        $('.cantidad_{{ $i }}').attr('max', obj.existencia);
                                                        $('.update_{{ $i }}').attr('data-maximo', obj.existencia);

                                                        partidas_especiales_finales.push({
                                                            "codigo": '{{ $producto->codigo_nikko }}',
                                                            "descripcion": '{{ $producto->descripcion_1 }}',
                                                            "cantidad": cantidad,
                                                            "precio": parseFloat(precio).toFixed(2),
                                                            "precio_iva": parseFloat(precio_iva).toFixed(2),
                                                            "total": (cantidad * precio).toFixed(2),
                                                            "sae": obj.cliente == "N/A" ? "NO ESTA EN SAE" : 'EN SAE',
                                                            "existencia_factura": parseInt(obj.existencia_factura),
                                                            "existencia_remision": parseInt(obj.existencia_remision),
                                                            "clave_proveedor": '{{ data_get($producto, 'clave_proveedor', '') }}'
                                                        })
                                                        partidas_especiales.push(producto_partida);

                                                        gran_total_especial += precio * cantidad;
                                                    </script>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    <b>Total pedido especial:</b> <span class="gran_total_especial">$0.00</span><br>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="col-lg-4 col-md-12">
                        <div class="cart-totals">

                            <button id="vaciar_carrito_btn" class="btn btn-sm mb-3" style="background-color:#c62828; color:white; width:100%;">
                                <i class="bi bi-trash"></i> Vaciar carrito
                            </button>

                            <h3>Entrega y factura</h3>
                            <div class="mb-3">
                                <label for="recibir" class="form-label">¿Como recibire mi mercancia?:</label>
                                <select class="form-select form-select-sm" aria-label=".form-select-sm example"
                                    id="recibir">
                                    <option value="">Seleccione (obligatorio)</option>
                                    <option value="PU">Recojo en tienda (pick up)</option>
                                    <option value="PA">Paqueteria</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="forma_pago" class="form-label">¿Como voy a pagar?:</label>
                                <select class="form-select form-select-sm" id="forma_pago">
                                    <option value="">Seleccione (obligatorio)</option>
                                    <option value="01">01 Efectivo</option>
                                    <option value="02">02 Cheque nominativo</option>
                                    <option value="03">03 Transferencia electrónica de fondos</option>
                                    <option value="04">04 Tarjeta de crédito</option>
                                    <option value="28">28 Tarjeta de débito</option>
                                    <option value="99">99 Por definir</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="fecha_recoge" class="form-label" id="texto_fecha_recoge">Pasare por mi pedido
                                    a las (solo si recogo en tienda):</label>
                                <input type="datetime-local" id="fecha_recoge" class="form-control"
                                    placeholder="Dia/Hora para recoger el pedido" min="{{ date('Y-m-d\TH:i') }}"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Metodo pago:</label>
                                <select class="form-select form-select-sm" aria-label=".form-select-sm example"
                                    id="metodo_pago">
                                    <option value="PUE">PUE Pago en una sola exhibición</option>
                                    <option value="PPD">PPD Pago en parcialidades o diferido</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="uso_cfdi" class="form-label">Uso del CFDI:</label>
                                <select class="form-select form-select-sm" aria-label=".form-select-sm example"
                                    id="uso_cfdi">
                                    <option value="G03">G03 Gastos en general.</option>
                                    <option value="G01">G01 Adquisición de mercancías.</option>
                                    <option value="G02">G02 Devoluciones, descuentos o bonificaciones.</option>
                                    <option value="I01">I01 Construcciones.</option>
                                    <option value="I02">I02 Mobiliario y equipo de oficina por inversiones.</option>
                                    <option value="I03">I03 Equipo de transporte.</option>
                                    <option value="I04">I04 Equipo de computo y accesorios.</option>
                                    <option value="I05">I05 Dados, troqueles, moldes, matrices y herramental.</option>
                                    <option value="I06">I06 Comunicaciones telefónicas.</option>
                                    <option value="I07">I07 Comunicaciones satelitales.</option>
                                    <option value="I08">I08 Otra maquinaria y equipo.</option>
                                    <option value="D01">D01 Honorarios médicos, dentales y gastos hospitalarios.
                                    </option>
                                    <option value="D02">D02 Gastos médicos por incapacidad o discapacidad.</option>
                                    <option value="D03">D03 Gastos funerales.</option>
                                    <option value="D04">D04 Donativos.</option>
                                    <option value="D05">D05 Intereses reales efectivamente pagados por créditos
                                        hipotecarios (casa habitación).</option>
                                    <option value="D06">D06 Aportaciones voluntarias al SAR.</option>
                                    <option value="D07">D07 Primas por seguros de gastos médicos.</option>
                                    <option value="D08">D08 Gastos de transportación escolar obligatoria.</option>
                                    <option value="D09">D09 Depósitos en cuentas para el ahorro, primas que tengan como
                                        base planes de pensiones.</option>
                                    <option value="D10">D10 Pagos por servicios educativos (colegiaturas).</option>
                                    <option value="S01">S01 Sin efectos fiscales. </option>
                                </select>
                            </div>
                            <h3 class="mt-3">Total</h3>
                            <ul>
                                <li>Total a pagar <span class="gran_total">$0.00</span></li>
                            </ul>

                            <button id="guardar" class="default-btn">
                                Generar pedido
                            </button>
                            <div id="aviso_duplicado" class="mt-2" style="display:none; padding:10px; border:1px solid #f0c36d; background-color:#fff3cd; color:#856404; border-radius:6px; font-size:13px;">
                                <b>&#9888; Atencion:</b>
                                <span id="aviso_duplicado_texto"></span>
                                <ul id="aviso_duplicado_lista" style="margin:6px 0 0 18px; padding:0; font-size:12px;"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Guardando pedido</h5>
                    </div>
                    <div class="modal-body">
                        <h5>Espera un momento, estamos guardando tu pedido...</h5>
                    </div>
                    <div class="modal-footer" style="display: none;">
                        <a class="default-btn" href="{{ route('tienda_online.carrito') }}">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel2">Estamos analizando tu archivo...</h5>
                    </div>
                    <div class="modal-body modal-excel">
                        <h4>Espera un momento, estamos analizando tu archivo...</h4>
                    </div>
                    <div class="modal-footer botones-excel" style="display: none;">
                        <a class="default-btn" href="{{ route('tienda_online.carrito') }}">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Cart Area -->
        <script type="text/javascript">
            var envio_recoge = false;

            $.get('https://sistemasowari.com:8443/catalowari/api/cliente', {
                cliente: '{{ \Auth::user()->clave_cliente }}'
            }, function(data) {
                /*optional stuff to do after success */
                data = jQuery.parseJSON(data);

                if (data.cliente.STATUS != "A") {
                    alert(
                        "Tu cuenta se encuentra suspendida, favor de comunitarte al area de cobranza. Cel. 56-1318-4858");
                    location.href = "{{ route('tienda_online.dashboard') }}";
                }


                if (data.cliente.CAMPLIB1 == "MOSTRADOR" || String(data.cliente.CAMPLIB1).includes('CTE')) {

                } else {
                    //$("#fecha_recoge").attr('type','text').attr('readonly', 'readonly').val(data.cliente.CAMPLIB1);
                    //$("#texto_fecha_recoge").text('Enviado por:');
                }

                //$("#forma_pago").val(data.cliente.FORMADEPAGOSAT);
                $("#uso_cfdi").val(data.cliente.USO_CFDI);
                $("#metodo_pago").val(data.cliente.METODODEPAGO).trigger('change');

            });

            var porcentaje = 1;
            @if (isset(\Auth::user()->clienteData))
                @if (\Auth::user()->clienteData->tiendita)

                    porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }} / 100);
                @endif
            @endif

            $('.gran_total').html("$ " + parseFloat((gran_total + gran_total_especial) * porcentaje).toFixed(2));
            $('.gran_total_especial').html("$ " + parseFloat(gran_total_especial * porcentaje).toFixed(2));


            @if (\Auth::user()->clave_cliente == 'M014M')
                $('.gran_total').html("$ " + parseFloat((gran_total + gran_total_especial) * porcentaje).toFixed(2));
                $('.gran_total_especial').html("$ " + parseFloat(gran_total_especial * porcentaje).toFixed(2));
            @endif


            @if (count($productos_especiales) <= 0 && \Auth::user()->clave_cliente != 'M014M')
                if (parseFloat(gran_total) < 450)
                    $("#guardar").text("Tu pedido debe de ser mayor a $450.00").attr('disabled', 'disabled');
            @endif

            @if (\Auth::user()->clave_cliente == 'M014M')
                if (parseFloat(gran_total + gran_total_especial) < 2500)
                    $("#guardar").text("Tu pedido debe de ser mayor a $2500.00 para poder solicitarlo").attr('disabled',
                        'disabled');
            @endif


            $('.pasar_especial_producto').click(function(e) {
                e.preventDefault();
                var $cantidad = $(this).data('cantidad');
                var $numero_parte = $(this).data('clave');
                var $producto_partida = $(this).data('partida');
                console.log($producto_partida);

                $.post("{{ route('tienda_online.carrito_actualizar') }}", {
                        'funcion': 'quitar',
                        'numero_parte': $numero_parte,
                        'cantidad': 0,
                        '_token': '{{ csrf_token() }}'
                    },
                    function(data, textStatus, jqXHR) {
                        $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", {
                                'funcion': 'agregar',
                                'numero_parte': $numero_parte,
                                'cantidad': $cantidad,
                                'partida': $producto_partida,
                                '_token': '{{ csrf_token() }}',
                                'sustituto': false
                            },
                            function(data, textStatus, jqXHR) {
                                if (data.code) {
                                    window.location = "{{ route('tienda_online.carrito') }}";
                                }
                            },
                            'json'
                        );
                    },
                    'json'
                );
            });



            $('.quitar_producto').click(function(e) {
                e.preventDefault();
                var $cantidad = $(this).closest('.agregar_carrito').find('input').val();
                var $numero_parte = $(this).data('numero');

                $.post("{{ route('tienda_online.carrito_actualizar') }}", {
                        'funcion': 'quitar',
                        'numero_parte': $numero_parte,
                        'cantidad': 0,
                        '_token': '{{ csrf_token() }}'
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.code) {
                            window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });
            $('.quitar_producto_especial').click(function(e) {
                e.preventDefault();
                var $cantidad = $(this).closest('.agregar_carrito').find('input').val();
                var $numero_parte = $(this).data('numero');

                $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", {
                        'funcion': 'quitar',
                        'numero_parte': $numero_parte,
                        'cantidad': 0,
                        '_token': '{{ csrf_token() }}'
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.code) {
                            window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });

            $('.actualizar_producto').click(function(e) {
                e.preventDefault();

                var $cantidad = $('.cantidad_' + $(this).data('i')).val();

                var $numero_parte = $(this).data('numero');
                var $maximo_stock = $(this).data('maximo');

                if (parseInt($cantidad) > parseInt($maximo_stock)) {
                    alert('Solo tenemos en existencia: ' + $maximo_stock + '. Ingresa una cantidad menor.');
                    return false;
                }



                $.post("{{ route('tienda_online.carrito_actualizar') }}", {
                        'funcion': 'actualizar',
                        'numero_parte': $numero_parte,
                        'cantidad': $cantidad,
                        partida: window['obj_' + $(this).data('i')],
                        '_token': '{{ csrf_token() }}',
                        'sustituto': false
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.code) {
                            window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });

            $('.actualizar_producto_especial').click(function(e) {
                e.preventDefault();

                var $cantidad = $('.cantidad_' + $(this).data('i')).val();

                var $numero_parte = $(this).data('numero');
                var $maximo_stock = 1000000;

                if (parseInt($cantidad) > parseInt($maximo_stock)) {
                    alert('Solo tenemos en existencia: ' + $maximo_stock + '. Ingresa una cantidad menor.');
                    return false;
                }



                $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", {
                        'funcion': 'actualizar',
                        'numero_parte': $numero_parte,
                        'cantidad': $cantidad,
                        partida: window['obj_' + $(this).data('i')],
                        '_token': '{{ csrf_token() }}',
                        'sustituto': false
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.code) {
                            window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });


            var pedidoEnviado = false;

            $("#guardar").click(function(event) {
                // Fase 12: el click ahora invoca el flujo v2 (orquestador
                // async/await con cola de retry, regalos via SOMA,
                // abstraccion de proveedores especiales y guard CLIE03).
                // El viejo guardar_pedido() fue eliminado.
                //
                // Validamos ANTES de levantar pedidoEnviado: si la forma esta
                // incompleta, validarFormulario() lanza alert y devuelve false,
                // pero la flag no queda levantada — el usuario puede corregir
                // y volver a presionar Generar pedido sin recargar la pagina.
                if (pedidoEnviado) return false;
                if (!validarFormulario()) return false;
                pedidoEnviado = true;
                guardar_pedido_v2();
            });

            (function verificarDuplicado() {
                var claves = [];
                for (var i = 0; i < partidas_finales.length; i++) claves.push(partidas_finales[i].codigo);
                for (var i = 0; i < partidas_especiales_finales.length; i++) claves.push(partidas_especiales_finales[i].codigo);
                if (claves.length === 0) return;

                $.post("{{ route('tienda_online.verificar_duplicado') }}",
                    { claves: claves, '_token': "{{ csrf_token() }}" },
                    function(data) {
                        if (data && data.similar) {
                            var refPedido = data.pedido_sae_anterior && data.pedido_sae_anterior != "0"
                                ? "#" + data.pedido_sae_anterior + " (" + data.fecha_anterior + ")"
                                : "del " + data.fecha_anterior;
                            $('#aviso_duplicado_texto').html(
                                ' Este pedido contiene <b>' + data.total_coincidencias +
                                '</b> productos iguales a los de tu pedido anterior ' + refPedido +
                                '. Verifica que no lo estes duplicando antes de generarlo.'
                            );
                            $('#aviso_duplicado_lista').html(
                                data.coincidencias.map(function(c){ return '<li>' + c + '</li>'; }).join('')
                            );
                            $('#aviso_duplicado').show();
                        }
                    },
                    "json"
                );
            })();

            $('#vaciar_carrito_btn').click(function(e) {
                e.preventDefault();
                if (!confirm('Se eliminaran todas las partidas del carrito (normales y especiales). Deseas continuar?')) return;
                $.get("{{ route('tienda_online.vaciar_carrito') }}", function() {
                    window.location.reload();
                }).fail(function() {
                    window.location.reload();
                });
            });


            // ════════════════════════════════════════════════════════════════════
            // FLUJO DE GUARDADO (v2)
            // ════════════════════════════════════════════════════════════════════
            // El click en #guardar invoca guardar_pedido_v2(). Es el unico flujo
            // activo; el viejo guardar_pedido() se elimino en Fase 12.
            //
            //   guardar_pedido_v2()              ← orquestador (async/await)
            //     ├── validarFormulario()
            //     ├── obtenerClienteSae()         ← trae CLASIFIC, CAMPLIB3, EXISTE_E3
            //     ├── capturarEnSoma()            (no bloqueante, fire-and-forget)
            //     ├── separarPartidas()           ← usa PROVEEDORES_ESPECIALES
            //     ├── consultarRegalo()           ← origen='tienda'
            //     ├── guardarEspecialesGenerales()
            //     ├── clasificarPorEmpresa()      ← W en pos.4 + guard EXISTE_E3
            //     ├── insertarEnSaeConRetry()     (E01 + E03; cola si fallan 5 retries)
            //     ├── guardarPedidoWebLocal()
            //     └── redirigirExito() / mostrarPendiente()
            //
            // Helpers UI:    mostrarCargando, mostrarError, mostrarPendiente, redirigirExito
            // Helpers datos: todasLasPartidas, cargarProveedoresEspeciales
            // ════════════════════════════════════════════════════════════════════

            // PROVEEDORES_ESPECIALES, proveedoresEspecialesListos, configProveedor
            // y obtenerStockFicticio estan declarados en el primer <script> del file
            // para que los <script> inline del foreach de productos puedan usarlos.

            var MAX_INTENTOS_SAE = 5;
            var ESPERA_ENTRE_INTENTOS_MS = 2000;

            function dormir(ms) {
                return new Promise(function(resolve) { setTimeout(resolve, ms); });
            }


            // ──────────────────────────────────────────────────────────────────
            // ORQUESTADOR PRINCIPAL
            // ──────────────────────────────────────────────────────────────────
            async function guardar_pedido_v2() {
                if (!validarFormulario()) return;
                mostrarCargando('Procesando tu pedido...');

                // Una vez que guardarEspecialesGenerales (paso 4) regresa, los
                // PedidoEspecial ya estan creados en el server. Si algo truena
                // DESPUES, NO debemos reactivar el boton: un reintento volveria
                // a ejecutar el paso 4 y duplicaria los especiales.
                var especialesYaCreados = false;

                try {
                    // 0. Asegurar que la config de proveedores especiales este
                    //    cargada desde SOMA antes de tocar partidas. Si SOMA esta
                    //    caido, abortamos: prefiero detener el flujo a perder
                    //    partidas silenciosamente (S227 caeria en clasificarPorEmpresa.DESCARTADA).
                    var cfgOk = await proveedoresEspecialesListos;
                    if (!cfgOk) {
                        throw new Error(
                            'No se pudo cargar la configuracion de proveedores especiales desde SOMA. ' +
                            'Recarga la pagina e intenta de nuevo.'
                        );
                    }

                    // 1. Datos del cliente desde SAE (CLASIFIC + CAMPLIB3)
                    var cliente = await obtenerClienteSae();

                    // 2. (El espejo en SOMA se manda AL FINAL, ya con los folios SAE
                    //     reales — ver paso 9b — para poder comparar SOMA vs SAE.)

                    // 3. Separar partidas en {sae, especiales}
                    //    especiales es un mapa {claveProveedor: [...]} con los grupos
                    //    de cada proveedor especial (S227, AAAA, etc.)
                    var separadas = separarPartidas();

                    // 4. Guardar pedidos especiales (uno por proveedor).
                    //    Captura los ids de los PedidoEspecial (y su # de partidas) para mandarlos a SOMA.
                    var especialesCreados  = await guardarEspecialesGenerales(separadas.especiales);
                    var idsEspeciales      = especialesCreados.map(function (e) { return e.id; });
                    var partidasEspeciales = especialesCreados.map(function (e) { return e.num; });
                    especialesYaCreados = true;

                    // 5. Clasificar SAE por empresa segun CLASIFIC del cliente.
                    //    Pasamos cliente.EXISTE_E3 para que si el cliente no
                    //    esta en CLIE03 todo se vaya a factura (E01).
                    var clasificacion = clasificarPorEmpresa(separadas.sae, cliente.CLASIFIC, cliente.EXISTE_E3);

                    // 6. Consultar regalo SOLO si hay partidas en empresa 1
                    //    (factura). Regla: un pedido 100% especial (o solo
                    //    remision) no genera regalo — el regalo acompaña una
                    //    venta real en E01. Si lo aplica, se agrega a factura.
                    var regalo = null;
                    if (clasificacion.factura.length > 0) {
                        regalo = await consultarRegalo(cliente);
                        if (regalo) clasificacion.factura.push(regalo);
                    }

                    // 7. Crear el espejo local en pedidos_web ANTES de tocar SAE,
                    //    con folios en null. Asi el pedido SIEMPRE queda registrado
                    //    localmente: si SAE o la actualizacion de folios fallan
                    //    despues (red, navegador cerrado, etc.), el pedido existe y
                    //    es reconciliable — nunca queda un folio SAE sin espejo
                    //    (el caso B038). Si la creacion del espejo falla, abortamos
                    //    AQUI, antes de insertar en SAE, para no generar un folio
                    //    huerfano. Folios en null; se llenan en el paso 9.
                    var idPedido = await guardarPedidoWebLocal({
                        cliente: cliente.CLAVE,
                        folio_factura:        null,
                        folio_remision:       null,
                        partidas_sae:         clasificacion.factura.concat(clasificacion.remision),
                        especiales:           separadas.especiales,
                        regalo:               regalo,
                    });

                    // 8. Insertar en SAE con retry (1 o 2 pedidos). Pasamos idPedido
                    //    para que, si una empresa agota reintentos y se encola, el
                    //    pendiente quede enlazado al espejo y el cron lo complete.
                    //    Cada llamada devuelve:
                    //      string  → folio SAE
                    //      null    → no hay partidas para esa empresa
                    //      object  → { queued:true, id_pendiente } cuando los 5
                    //                retries fallaron y el pedido quedo encolado
                    var resultadoFactura  = await insertarEnSaeConRetry(clasificacion.factura, 1, idPedido);
                    var resultadoRemision = clasificacion.remision.length
                        ? await insertarEnSaeConRetry(clasificacion.remision, 3, idPedido)
                        : null;

                    var folioFactura  = (typeof resultadoFactura  === 'string') ? resultadoFactura  : null;
                    var folioRemision = (typeof resultadoRemision === 'string') ? resultadoRemision : null;

                    var hayQueued = (resultadoFactura  && resultadoFactura.queued)
                                 || (resultadoRemision && resultadoRemision.queued);

                    // 9. Actualizar el espejo con los folios que SI se lograron. No
                    //    bloqueante: si falla, el espejo ya existe con sus partidas
                    //    y el folio se reconcilia despues — no tiene caso mostrar
                    //    error al cliente por esto. Los encolados ya quedaron
                    //    enlazados en el paso 8; el cron los completara.
                    if (folioFactura || folioRemision) {
                        try {
                            await actualizarFoliosEspejo(idPedido, folioFactura, folioRemision);
                        } catch (e) {
                            console.warn('actualizarFoliosEspejo fallo (no critico):', e);
                        }
                    }

                    // 9b. Espejo en SOMA con los folios SAE reales (fire-and-forget).
                    //     Se manda SIEMPRE con las partidas completas + los folios que
                    //     SÍ se lograron (el que haya quedado en cola va como null).
                    //     Así SOMA tiene el pedido completo aunque un folio se difiera.
                    capturarEnSoma(cliente, todasLasPartidas(), {
                        folio_sae_e01: folioFactura,
                        folio_sae_e03: folioRemision,
                        id_especial:   idsEspeciales,
                        partidas_sae_e01:  clasificacion.factura.length,
                        partidas_sae_e03:  clasificacion.remision.length,
                        partidas_especial: partidasEspeciales,
                    });

                    // 10. Si hay pendientes encolados, mostrar mensaje distinto;
                    //     sino, redirigir a exito normal
                    if (hayQueued) {
                        mostrarPendiente(idPedido);
                    } else {
                        redirigirExito(idPedido);
                    }

                } catch (err) {
                    console.error('guardar_pedido_v2 fallo:', err);
                    if (especialesYaCreados) {
                        // Los especiales ya quedaron guardados. NO reactivamos el
                        // boton (reintentar los duplicaria). Mensaje positivo:
                        // el cliente siente que se guardo y le llega confirmacion.
                        mostrarRegistradoSinReintento();
                    } else {
                        mostrarError(err.message || 'Ocurrio un error inesperado');
                    }
                }
            }


            // ──────────────────────────────────────────────────────────────────
            // HELPERS — UI
            // ──────────────────────────────────────────────────────────────────

            function mostrarCargando(mensaje) {
                // Muestra el modal #staticBackdrop con el mensaje y deshabilita
                // el boton #guardar para evitar dobles clicks.
                $("#staticBackdropLabel").text('Guardando pedido');
                $("#staticBackdrop .modal-body").html(
                    '<h5>' + (mensaje || 'Espera un momento, estamos guardando tu pedido...') + '</h5>'
                );
                $("#staticBackdrop .modal-footer").hide();
                $('#staticBackdrop').modal('show');

                $("#guardar").attr('disabled', 'disabled').text('Enviando pedido...');
            }

            function mostrarError(mensaje) {
                // Cambia el contenido del modal a un mensaje de error, muestra
                // el boton de cerrar y reactiva #guardar para que el cliente
                // pueda intentar de nuevo. Tambien resetea pedidoEnviado para
                // que el click handler no bloquee el reintento.
                $("#staticBackdropLabel").text('Error');
                $("#staticBackdrop .modal-body").html(
                    '<h5>' + (mensaje || 'Ocurrio un error inesperado.') + '</h5>'
                );
                $("#staticBackdrop .modal-footer").show();
                $('#staticBackdrop').modal('show');

                $("#guardar").removeAttr('disabled').text('Generar pedido');
                pedidoEnviado = false;
            }

            function redirigirExito(idPedido) {
                // Redirige a la pantalla de exito con el id del pedido local.
                var url = "{{ route('tienda_online.guardado_exitoso') }}" +
                          '?id_pedido=' + encodeURIComponent(idPedido || '');
                window.location.href = url;
            }

            function mostrarRegistradoSinReintento() {
                // Se usa cuando el flujo fallo DESPUES de crear los pedidos
                // especiales. Mensaje positivo y neutro: el cliente NO debe
                // sentir que algo salio mal ni intentar de nuevo. El boton
                // queda deshabilitado a proposito (NO reseteamos pedidoEnviado)
                // para que un reintento no duplique los especiales. El boton
                // "Ir a mis pedidos" le da una salida clara.
                $("#staticBackdropLabel").text('Pedido registrado');
                $("#staticBackdrop .modal-body").html(
                    '<div style="text-align:left;">' +
                    '<h5 style="color:#43a047;">Tu pedido fue registrado correctamente.</h5>' +
                    '<p>Estamos terminando de procesarlo. Te llegara confirmacion por correo en cuanto este listo.</p>' +
                    '</div>'
                );
                $("#staticBackdrop .modal-footer").html(
                    '<a class="default-btn" href="{{ route('tienda_online.pedidos') }}">Ir a mis pedidos</a>'
                ).show();
                $('#staticBackdrop').modal('show');
                // Intencional: #guardar queda disabled y pedidoEnviado=true.
            }

            function mostrarPendiente(idPedido) {
                // Cuando una o ambas insercciones SAE quedaron encoladas para
                // retry diferido, mostramos un mensaje claro al cliente y NO
                // redirigimos automaticamente — le damos el boton para ir a
                // sus pedidos cuando este listo. El job artisan procesara la
                // cola en background.
                $("#staticBackdropLabel").text('Pedido en proceso');
                $("#staticBackdrop .modal-body").html(
                    '<div style="text-align:left;">' +
                    '<h5 style="color:#ef6c00;">Tu pedido fue registrado correctamente.</h5>' +
                    '<p>SAE no respondio en este momento, asi que estamos terminando de registrarlo en segundo plano. ' +
                    'Te llegara confirmacion por correo en cuanto tengamos el folio definitivo.</p>' +
                    '<p style="font-size:12px;color:#666;">No vuelvas a generar el pedido. Tu numero local es <b>' + (idPedido || '—') + '</b>.</p>' +
                    '</div>'
                );

                // Reemplazo del footer por el boton "Ir a mis pedidos"
                var $footer = $("#staticBackdrop .modal-footer");
                $footer.html(
                    '<a class="default-btn" href="{{ route('tienda_online.pedidos') }}">Ir a mis pedidos</a>'
                ).show();

                $('#staticBackdrop').modal('show');
                $("#guardar").removeAttr('disabled').text('Generar pedido');
            }


            // ──────────────────────────────────────────────────────────────────
            // HELPERS — DATOS DEL FORMULARIO Y CLIENTE
            // ──────────────────────────────────────────────────────────────────

            function validarFormulario() {
                // Valida los campos obligatorios del formulario antes de iniciar
                // el flujo de guardado. El monto minimo NO se valida aqui — el
                // boton #guardar se deshabilita en el render del carrito si no
                // se cumple.
                var recibir   = $("#recibir").val();
                var formaPago = $("#forma_pago").val();

                if (recibir === '' || formaPago === '') {
                    alert('Selecciona la forma de recepcion de tu mercancia y como vas a pagar tus productos');
                    return false;
                }

                // Si recoge en tienda, la fecha y hora son obligatorias
                if (recibir === 'PU' && !$("#fecha_recoge").val()) {
                    alert('Selecciona el dia y hora para recoger tu pedido');
                    $("#fecha_recoge").focus();
                    return false;
                }

                return true;
            }

            function todasLasPartidas() {
                // Junta partidas_finales + partidas_especiales_finales en un
                // solo array. Se manda a SOMA capturar tal cual; SOMA aplica
                // sus reglas internas (politicas, descuentos, etc.).
                return (partidas_finales || []).concat(partidas_especiales_finales || []);
            }

            async function obtenerClienteSae() {
                // GET /catalowari/api/datos_cliente?clave={clave}.
                // Devuelve el row crudo de CLIE01 LEFT JOIN CLIE_CLIB01 (incluye
                // CLASIFIC, CAMPLIB3, CAMPLIB13, METODODEPAGO, FORMADEPAGOSAT,
                // USO_CFDI, RFC, CVE_VEND, etc.).
                var claveCliente = '{{ \Auth::user()->clave_cliente }}';
                var url = 'https://sistemasowari.com:8443/catalowari/api/datos_cliente?clave=' +
                          encodeURIComponent(claveCliente);

                var resp = await fetch(url);
                if (!resp.ok) {
                    throw new Error('No se pudo obtener los datos del cliente desde SAE (HTTP ' + resp.status + ')');
                }

                var data = await resp.json();
                if (!data || !data.CLAVE) {
                    throw new Error('Cliente ' + claveCliente + ' no encontrado en SAE');
                }

                return data;
            }


            // ──────────────────────────────────────────────────────────────────
            // HELPERS — SEPARACION Y CLASIFICACION
            // ──────────────────────────────────────────────────────────────────

            function separarPartidas() {
                // Itera partidas_finales (cart) y partidas_especiales_finales (cartEspecial),
                // aplica la regla del proveedor (PROVEEDORES_ESPECIALES cargado desde SOMA)
                // y devuelve { sae:[], especiales:{<claveProveedor>:[partidas]} }.
                //
                // Reglas por proveedor (desde proveedores_especiales):
                //   sin config (proveedor normal)  → sae
                //   tipo='todo_especial'            → especiales[clave]
                //   tipo='split_por_stock':
                //       cantidad <= existencia_sae  → sae
                //       cantidad >  existencia_sae  → split:
                //                                     existencia_sae a sae,
                //                                     resto a especiales[clave]
                //
                // Las partidas que vienen del cartEspecial (manualmente movidas
                // por el cliente) siempre se mandan a especiales[clave_proveedor],
                // sin importar el tipo de separacion.
                var sae = [];
                var especiales = {};

                function pushEspecial(claveProveedor, partida) {
                    var clave = (claveProveedor || '').trim() || 'SIN_PROVEEDOR';
                    if (!especiales[clave]) especiales[clave] = [];
                    especiales[clave].push(partida);
                }

                function ajustarPartida(partida, nuevaCantidad) {
                    var copia = Object.assign({}, partida);
                    copia.cantidad = nuevaCantidad;
                    copia.total = (nuevaCantidad * parseFloat(copia.precio)).toFixed(2);
                    return copia;
                }

                // 1. Partidas del cart normal — aplican regla del proveedor
                for (var i = 0; i < partidas_finales.length; i++) {
                    var p = Object.assign({}, partidas_finales[i]);
                    var claveProv = (p.clave_proveedor || '').trim();
                    var config = claveProv ? PROVEEDORES_ESPECIALES[claveProv] : null;

                    // Sin config — proveedor normal, va a SAE
                    if (!config) {
                        sae.push(p);
                        continue;
                    }

                    // tipo='todo_especial' — todo va a pedido especial
                    if (config.tipo_separacion === 'todo_especial') {
                        pushEspecial(claveProv, p);
                        continue;
                    }

                    // tipo='split_por_stock'
                    if (config.tipo_separacion === 'split_por_stock') {
                        var cantidad = parseInt(p.cantidad) || 0;
                        var existenciaReal = Math.max(0, parseInt(p.existencia_sae) || 0);

                        if (cantidad <= existenciaReal) {
                            sae.push(p);
                        } else if (existenciaReal === 0) {
                            // Nada en SAE: todo a especial
                            pushEspecial(claveProv, p);
                        } else {
                            // Split: lo que cabe en SAE va a SAE, el resto a especial
                            sae.push(ajustarPartida(p, existenciaReal));
                            pushEspecial(claveProv, ajustarPartida(p, cantidad - existenciaReal));
                        }
                        continue;
                    }

                    // Tipo desconocido — defensivo, va a SAE
                    sae.push(p);
                }

                // 2. Partidas del cartEspecial. La clave_proveedor solo se usa
                //    para separar si ese proveedor esta marcado como ESPECIAL
                //    en SOMA (PROVEEDORES_ESPECIALES). Sino, va a SIN_PROVEEDOR
                //    junto con el resto — no queremos un PedidoEspecial por
                //    cada proveedor regular del catalogo.
                for (var j = 0; j < partidas_especiales_finales.length; j++) {
                    var pe = Object.assign({}, partidas_especiales_finales[j]);
                    var claveProvE = (pe.clave_proveedor || '').trim();
                    var configE = claveProvE ? PROVEEDORES_ESPECIALES[claveProvE] : null;
                    pushEspecial(configE ? claveProvE : '', pe);
                }

                // 3. Consolidar duplicados por codigo dentro de cada bucket especial
                //    (pueden venir partidas iguales del cart y del cartEspecial)
                Object.keys(especiales).forEach(function(clave) {
                    especiales[clave] = consolidarPorCodigo(especiales[clave]);
                });

                return { sae: sae, especiales: especiales };
            }

            // Suma cantidades y totales de partidas con el mismo codigo, preservando
            // los demas campos del primer registro encontrado.
            function consolidarPorCodigo(arr) {
                var mapa = {};
                for (var i = 0; i < arr.length; i++) {
                    var p = arr[i];
                    var cod = p.codigo;
                    if (!mapa[cod]) {
                        mapa[cod] = Object.assign({}, p);
                        mapa[cod].cantidad = parseInt(p.cantidad) || 0;
                        mapa[cod].total = parseFloat(p.total) || 0;
                    } else {
                        mapa[cod].cantidad += parseInt(p.cantidad) || 0;
                        mapa[cod].total += parseFloat(p.total) || 0;
                    }
                }
                return Object.values(mapa).map(function(p) {
                    p.total = parseFloat(p.total).toFixed(2);
                    return p;
                });
            }

            function clasificarPorEmpresa(partidasSae, clasif, existeE3) {
                // Decide si cada partida SAE va a empresa 1 (factura, con IVA)
                // o empresa 3 (remision, sin IVA), segun la CLASIFIC del cliente.
                //
                // GUARD: si el cliente NO existe en CLIE03 (existeE3 === false),
                // todo va a factura. SAE empresa 3 rechazaria el pedido si el
                // cliente no esta registrado alli. Cuando existeE3 viene como
                // undefined (backend viejo sin el campo EXISTE_E3) se mantiene
                // el comportamiento anterior — no bloquea.
                //
                // Reglas (replicadas de externos/guardarPedido original):
                //   - Si CLASIFIC NO tiene W en pos.4 → todo a factura (E01)
                //   - Si CLASIFIC tiene W en pos.4:
                //       existencia_remision >= 0       → remision (E03)
                //       existencia_factura > 0
                //         AND cantidad <= existencia_factura → factura (E01)
                //       resto → DESCARTADA (bug heredado, se preserva)
                var factura  = [];
                var remision = [];

                // Guard: cliente no esta en CLIE03 → no puede ir a E03
                if (existeE3 === false) {
                    return { factura: partidasSae.slice(), remision: remision };
                }

                if (!tieneWEnPos4(clasif)) {
                    return { factura: partidasSae.slice(), remision: remision };
                }

                for (var i = 0; i < partidasSae.length; i++) {
                    var p = partidasSae[i];
                    var existRem = parseInt(p.existencia_remision);
                    var existFac = parseInt(p.existencia_factura);
                    var cantidad = parseInt(p.cantidad) || 0;

                    if (!isNaN(existRem) && existRem >= 0) {
                        remision.push(p);
                    } else if (!isNaN(existFac) && existFac > 0 && cantidad <= existFac) {
                        factura.push(p);
                    } else {
                        console.warn('clasificarPorEmpresa: partida descartada (no entra ni en E01 ni en E03)', p);
                    }
                }

                return { factura: factura, remision: remision };
            }

            // CLASIFIC en SAE es VARCHAR(5). Si viene con menos de 5 chars,
            // SAE la padea con espacios; aqui la padeamos por seguridad. Posicion 4
            // (1-based) = indice 3 (0-based).
            function tieneWEnPos4(clasif) {
                if (!clasif) return false;
                var c = String(clasif).padEnd(5, ' ');
                return c.charAt(3).toUpperCase() === 'W';
            }


            // ──────────────────────────────────────────────────────────────────
            // HELPERS — ENVIO A ENDPOINTS
            // ──────────────────────────────────────────────────────────────────

            function capturarEnSoma(cliente, partidas, foliosSae) {
                // POST al proxy local soma.capturar_proxy, que reenvia a SOMA
                // /api/pedidos/capturar con X-API-Key.
                //
                // Fire-and-forget: no esperamos respuesta. Si SOMA cae, el flujo
                // sigue normal (graceful degradation). Si SOMA acepta, queda
                // espejo del pedido alla.
                //
                // SOMA recibe TODAS las partidas (cart + cartEspecial) sin separar
                // y aplica sus reglas internas (politicas, descuentos, division
                // normal/especial, etc.).
                //
                // foliosSae (opcional): { folio_sae_e01, folio_sae_e03, id_especial[] }
                // con los folios del documento REAL en SAE/origen, para que SOMA
                // los guarde y se pueda comparar SOMA vs SAE en su pantalla.
                try {
                    var idEnvio = (window.crypto && crypto.randomUUID)
                        ? crypto.randomUUID()
                        : ('env-' + Date.now() + '-' + Math.random().toString(36).slice(2));

                    var partidasParaSoma = (partidas || []).map(function(p) {
                        return { clave: p.codigo, cantidad: parseInt(p.cantidad) || 0 };
                    });

                    var payload = {
                        clave_cliente:     cliente.CLAVE,
                        partidas:          partidasParaSoma,
                        origen:            'TIENDA_ONLINE',
                        id_envio_externo:  idEnvio,
                        destino_sucursal:  'E01',
                        gran_total_origen: (gran_total || 0) + (gran_total_especial || 0),
                    };

                    if (foliosSae) {
                        payload.folio_sae_e01 = foliosSae.folio_sae_e01 || null;
                        payload.folio_sae_e03 = foliosSae.folio_sae_e03 || null;
                        payload.id_especial   = foliosSae.id_especial   || [];
                        payload.partidas_sae_e01  = foliosSae.partidas_sae_e01  || null;
                        payload.partidas_sae_e03  = foliosSae.partidas_sae_e03  || null;
                        payload.partidas_especial = foliosSae.partidas_especial || [];
                    }

                    fetch("{{ route('soma.capturar_proxy') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'Accept':           'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(payload),
                    }).catch(function(err) {
                        console.warn('capturarEnSoma fallo:', err);
                    });
                } catch (e) {
                    console.warn('capturarEnSoma excepcion:', e);
                }
            }

            async function consultarRegalo(cliente) {
                // POST /somma/v2.0/api/promociones-regalo/evaluar para saber si
                // este pedido aplica a alguna promo de regalo activa.
                //
                // Manda los datos del cliente que SOMA necesita para matchear
                // audiencia (campolibre y clasificacion vienen de SAE, no se
                // consultan adentro de SOMA).
                //
                // Devuelve la partida_regalo lista para meter al bucket sae si
                // SOMA responde aplica:true. Null en cualquier otro caso —
                // incluyendo timeouts, errores de red o respuestas raras.
                // Graceful degradation: si SOMA truena, el pedido se guarda sin
                // regalo y el cliente no ve un error.
                try {
                    var granTotal = (gran_total || 0) + (gran_total_especial || 0);

                    var payload = {
                        clave_cliente: cliente.CLAVE,
                        gran_total:    granTotal,
                        origen:        'tienda',
                        campolibre:    (cliente.CAMPLIB3 || '').toString().trim(),
                        clasificacion: (cliente.CLASIFIC || '').toString().trim(),
                    };

                    var resp = await fetch(
                        'https://owari.appsoma.online/somma/v2.0/api/promociones-regalo/evaluar',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                            },
                            body: JSON.stringify(payload),
                        }
                    );

                    if (!resp.ok) {
                        console.warn('consultarRegalo: SOMA respondio HTTP', resp.status);
                        return null;
                    }

                    var data = await resp.json();
                    if (!data || !data.aplica || !data.partida_regalo) {
                        return null;
                    }

                    var pr = data.partida_regalo;
                    var claveRegalo = String(pr.clave || '');

                    // Regla "una sola vez por cliente": SAE empresa 1 es la
                    // fuente de verdad — buscamos PAR_FACTP01 JOIN FACTP01 con
                    // la clave del regalo, cliente, STATUS!=C. Si ya tiene,
                    // descartamos. En error devolvemos null (conservador).
                    if (claveRegalo) {
                        try {
                            var url = 'https://sistemasowari.com:8443/catalowari/api/regalo_ya_tiene'
                                + '?cliente=' + encodeURIComponent(cliente.CLAVE)
                                + '&clave_regalo=' + encodeURIComponent(claveRegalo);
                            var verif = await fetch(url, { headers: { 'Accept': 'application/json' }});
                            if (!verif.ok) {
                                console.warn('consultarRegalo: SAE verificacion HTTP', verif.status, '— no se aplica');
                                return null;
                            }
                            var vdata = await verif.json();
                            if (vdata && vdata.ya_tiene === true) {
                                console.log('consultarRegalo: cliente ya tiene', claveRegalo, 'en SAE — no se aplica');
                                return null;
                            }
                        } catch (e2) {
                            console.warn('consultarRegalo: verificacion SAE fallo:', e2);
                            return null;
                        }
                    }

                    return {
                        codigo:              claveRegalo,
                        descripcion:         (data.promocion && data.promocion.nombre) || 'Regalo promocional',
                        cantidad:            parseInt(pr.cantidad) || 1,
                        precio:              parseFloat(pr.precio || 0.01).toFixed(2),
                        precio_iva:          parseFloat(pr.precio || 0.01).toFixed(2),
                        total:               parseFloat(pr.total  || 0.01).toFixed(2),
                        existencia_sae:      999,
                        existencia_factura:  999,
                        existencia_remision: -1,
                        clave_proveedor:     '',
                        es_regalo:           true,
                    };
                } catch (e) {
                    console.warn('consultarRegalo fallo:', e);
                    return null;
                }
            }

            async function guardarEspecialesGenerales(especialesPorProveedor) {
                // Itera el mapa { claveProveedor: [partidas], ... } y por cada
                // grupo hace UN POST a pedidos.guardar_especial pasando
                // clave_proveedor. Cubre S227 (SYD), AAAA y cualquier proveedor
                // futuro. El backend decide el subject del email segun
                // clave_proveedor (Pedido especial SYD vs Pedido especial).
                //
                // Usa jQuery $.post porque el endpoint espera 'partidas' como
                // array anidado de PHP (extract + foreach); jQuery lo serializa
                // como partidas[0][codigo]=..., que PHP recibe como array.
                //
                // Si una llamada truena, loguea warning y continua con las
                // demas — no bloquea el flujo del pedido.
                // Devuelve los ids de los PedidoEspecial creados (uno por proveedor),
                // para mandarlos a SOMA y comparar contra el/los PE que SOMA genera.
                var idsEspeciales = [];
                if (!especialesPorProveedor) return idsEspeciales;

                var claves = Object.keys(especialesPorProveedor);
                for (var i = 0; i < claves.length; i++) {
                    var claveProveedor = claves[i];
                    var partidas = especialesPorProveedor[claveProveedor] || [];
                    if (partidas.length === 0) continue;

                    var data = {
                        '_token':  '{{ csrf_token() }}',
                        cliente:   '{{ \Auth::user()->clave_cliente }}',
                        partidas:  partidas,
                        carrito:   1,
                    };
                    if (claveProveedor && claveProveedor !== 'SIN_PROVEEDOR') {
                        data.clave_proveedor = claveProveedor;
                    }

                    var idEsp = await guardarEspecialUnGrupo(data, claveProveedor);
                    if (idEsp) idsEspeciales.push({ id: idEsp, num: partidas.length });
                }
                return idsEspeciales;
            }

            // Wrapper que envuelve $.post en una promise para usar con await,
            // y no truena el flujo si una falla — solo loguea warning.
            // Resuelve con el id del PedidoEspecial creado (o null si falla).
            function guardarEspecialUnGrupo(data, claveProveedor) {
                return new Promise(function(resolve) {
                    $.post("{{ route('pedidos.guardar_especial') }}", data)
                        .done(function(resp) {
                            try {
                                var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                                resolve(r && r.id_pedido ? r.id_pedido : null);
                            } catch (e) {
                                resolve(null);
                            }
                        })
                        .fail(function(xhr) {
                            console.warn(
                                'guardarEspecialesGenerales fallo proveedor=' + claveProveedor,
                                xhr && xhr.status,
                                xhr && xhr.responseText
                            );
                            resolve(null);   // continuar con el siguiente proveedor
                        });
                });
            }

            async function insertarEnSaeConRetry(partidas, empresa, idPedidoWeb) {
                // Inserta el pedido en SAE empresa 1 (factura) o 3 (remision).
                // Reintenta hasta MAX_INTENTOS_SAE veces con espera de 2s entre
                // cada uno.
                //
                // Resultados posibles:
                //   - Devuelve el folio string ("4CW12345") cuando SAE acepta
                //   - Devuelve null cuando no hay partidas que insertar
                //   - Devuelve { queued:true, id_pendiente, empresa, ultimo_error }
                //     cuando los 5 intentos del frontend fallaron y el pedido
                //     se encolo en backend para retry diferido
                if (!partidas || partidas.length === 0) return null;

                var ultimoError = null;

                for (var intento = 1; intento <= MAX_INTENTOS_SAE; intento++) {
                    try {
                        var folio = await intentarInsercionSae(partidas, empresa);
                        if (intento > 1) {
                            console.log('insertarEnSaeConRetry exito en intento ' + intento + '/' + MAX_INTENTOS_SAE);
                        }
                        return folio;
                    } catch (err) {
                        ultimoError = err;
                        console.warn(
                            'insertarEnSaeConRetry empresa=' + empresa +
                            ' intento=' + intento + '/' + MAX_INTENTOS_SAE +
                            ' fallo:', err.message
                        );

                        if (intento < MAX_INTENTOS_SAE) {
                            await dormir(ESPERA_ENTRE_INTENTOS_MS);
                        }
                    }
                }

                // Tras MAX_INTENTOS, encolar en backend para que un job artisan
                // lo procese cada 5 min hasta lograrlo o marcarlo fallido.
                // Pasamos idPedidoWeb para enlazar el pendiente al espejo ya
                // creado, asi el cron puede escribir el folio cuando lo logre.
                console.warn('insertarEnSaeConRetry agoto reintentos para empresa=' + empresa + ', encolando...');
                var idPendiente = await encolarSaePendiente(partidas, empresa, ultimoError, idPedidoWeb);

                return {
                    queued:        true,
                    id_pendiente:  idPendiente,
                    empresa:       empresa,
                    ultimo_error:  ultimoError ? ultimoError.message : null,
                };
            }

            // Llama al endpoint local que guarda el pedido en pedidos_sae_pendientes
            // para retry diferido. Si esto tambien truena, propagamos error al
            // orquestador (no podemos hacer mas en el frontend).
            async function encolarSaePendiente(partidas, empresa, ultimoError, idPedidoWeb) {
                var partidasParaSae = (partidas || []).map(function(p) {
                    return partidaParaSae(p, empresa);
                });

                var data = {
                    '_token':       '{{ csrf_token() }}',
                    cliente:        '{{ \Auth::user()->clave_cliente }}',
                    empresa:        empresa,
                    usuario:        '{{ \Auth::user()->name }}',
                    su_pedido:      'Pedido Online',
                    // origen 'CW' = carrito. Lo persistimos en pedidos_sae_pendientes
                    // para que el cron lo reenvie con la serie correcta (CAMPLIB13+CW).
                    origen:         'CW',
                    // id_pedido_web enlaza el pendiente al espejo ya creado, para
                    // que el cron escriba el folio en pedidos_web cuando lo logre.
                    id_pedido_web:  idPedidoWeb || null,
                    partidas:       partidasParaSae,
                    ultimo_error:   ultimoError ? ultimoError.message : 'desconocido',
                };

                return new Promise(function(resolve, reject) {
                    $.post("{{ route('pedidos.encolar_sae_pendiente') }}", data)
                        .done(function(resp) {
                            try {
                                var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                                if (r && r.code === 1) {
                                    resolve(r.id_pendiente);
                                } else {
                                    reject(new Error('No se pudo encolar el pedido en backend'));
                                }
                            } catch (e) {
                                reject(new Error('Respuesta invalida al encolar'));
                            }
                        })
                        .fail(function(xhr) {
                            console.error('encolarSaePendiente fallo', xhr && xhr.status, xhr && xhr.responseText);
                            reject(new Error('Error de red al encolar el pedido'));
                        });
                });
            }

            // Un solo intento de POST a guardar_v2. Sin retry. El loop de
            // arriba se encarga de reintentar.
            async function intentarInsercionSae(partidas, empresa) {
                var partidasParaSae = (partidas || []).map(function(p) {
                    return partidaParaSae(p, empresa);
                });

                var payload = {
                    empresa:   empresa,
                    cliente:   '{{ \Auth::user()->clave_cliente }}',
                    usuario:   '{{ \Auth::user()->name }}',
                    su_pedido: 'Pedido Online',
                    // origen 'CW' = carrito / tienda en linea. SAE genera serie
                    // CAMPLIB13+CW (ej. PEDCW). Distingue del telemkt que usa 'W'.
                    origen:    'CW',
                    partidas:  partidasParaSae,
                };

                var resp = await fetch(
                    'https://sistemasowari.com:8443/catalowari/api/guardar_v2',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                        },
                        body: JSON.stringify(payload),
                    }
                );

                if (!resp.ok) {
                    throw new Error('SAE respondio HTTP ' + resp.status);
                }

                var data = await resp.json();
                if (!data || data.code !== 1 || !data.pedido) {
                    throw new Error('SAE rechazo el pedido: ' + ((data && data.mensaje) || 'sin mensaje'));
                }

                return data.pedido;   // ej. "4CW12345"
            }

            // Construye el shape que guardar_v2 espera para una partida.
            // El precio FINAL es el mismo en ambas sucursales (no cambia el total):
            //   - empresa 1 (factura): precio unitario SIN IVA; SAE le suma el
            //     16% (IMPU4) y el total queda con IVA.
            //   - empresa 3 (remision): SAE no maneja impuestos, asi que el
            //     precio unitario va YA CON IVA (sin linea de impuesto) para que
            //     el total quede igual que en factura.
            // p.precio = precio con IVA (mostrado); p.precio_iva = precio sin IVA.
            function partidaParaSae(p, empresa) {
                var cantidad     = parseInt(p.cantidad) || 0;
                var precioSinIva = parseFloat(p.precio_iva) || 0;
                var precioConIva = parseFloat(p.precio) || 0;
                var totalConIva  = parseFloat(p.total) || 0;

                return {
                    clave:    p.codigo,
                    cantidad: cantidad,
                    precio:   empresa === 1 ? precioSinIva : precioConIva,
                    total:    totalConIva,
                };
            }

            async function guardarPedidoWebLocal(payload) {
                // POST a tienda_online.guardar_pedido para crear el espejo del
                // pedido en pedidos_web (Postgres local). En el flujo v2 esto se
                // hace ANTES de insertar en SAE, con los folios en null: el
                // objetivo es que el pedido SIEMPRE quede registrado localmente
                // aunque SAE o la actualizacion de folios fallen despues. Los
                // folios se llenan luego via actualizarFoliosEspejo.
                //
                // Devuelve el id local de PedidoWeb.
                var data = {
                    '_token':            '{{ csrf_token() }}',
                    usuario:             '{{ \Auth::user()->name }}',
                    cliente:             payload.cliente,
                    partidas:            payload.partidas_sae,
                    folio_factura:       payload.folio_factura  || null,
                    folio_remision:      payload.folio_remision || null,
                    // pedido_sae se mantiene por compat: si por alguna razon el
                    // controller lo lee directo, recibe el folio de factura.
                    pedido_sae:          payload.folio_factura  || 0,
                    fecha_recoge:        $("#fecha_recoge").val(),
                    metodo_pago:         $("#metodo_pago").val(),
                    forma_pago:          $("#forma_pago").val(),
                    uso_cfdi:            $("#uso_cfdi").val(),
                    gran_total:          (gran_total || 0) + (gran_total_especial || 0),
                };

                return new Promise(function(resolve, reject) {
                    $.post("{{ route('tienda_online.guardar_pedido') }}", data)
                        .done(function(resp) {
                            try {
                                var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                                if (r && r.code) {
                                    resolve(r.id_pedido);
                                } else {
                                    reject(new Error('No se pudo registrar el pedido en el espejo local'));
                                }
                            } catch (e) {
                                reject(new Error('Respuesta invalida del espejo local'));
                            }
                        })
                        .fail(function(xhr) {
                            console.warn('guardarPedidoWebLocal fallo', xhr && xhr.status);
                            reject(new Error('Error de red al registrar el espejo local'));
                        });
                });
            }

            async function actualizarFoliosEspejo(idPedido, folioFactura, folioRemision) {
                // POST a tienda_online.actualizar_folios para escribir en el
                // PedidoWeb ya creado los folios SAE que se lograron. Se llama
                // DESPUES de insertar en SAE. Solo escribe columnas vacias (el
                // backend no pisa folios ya puestos por el cron).
                var data = {
                    '_token':         '{{ csrf_token() }}',
                    id_pedido:        idPedido,
                    folio_factura:    folioFactura  || null,
                    folio_remision:   folioRemision || null,
                };

                return new Promise(function(resolve, reject) {
                    $.post("{{ route('tienda_online.actualizar_folios') }}", data)
                        .done(function(resp) {
                            var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (r && r.code) resolve(true);
                            else reject(new Error('No se pudieron actualizar los folios del espejo'));
                        })
                        .fail(function(xhr) {
                            console.warn('actualizarFoliosEspejo fallo', xhr && xhr.status);
                            reject(new Error('Error de red al actualizar folios del espejo'));
                        });
                });
            }



            $('#metodo_pago').change(function(event) {
                /* Act on the event */
                if ($(this).val() == "PPD") {
                    $("#forma_pago option:not(:contains('99'))").attr("disabled", "disabled");
                    $("#forma_pago").val('99');
                } else {
                    $("#forma_pago option").removeAttr("disabled");
                }
            });
        </script>
    @endif
    <script>
        $('#inputGroupFileAddon04').click(function(event) {
            /* Act on the event */

            if ($('#inputGroupFile04').get(0).files.length === 0) {
                alert('Selecciona primero tu archivo de plantilla');
            } else {
                var formData = new FormData();
                formData.append('excel', $('#inputGroupFile04')[0].files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                $('#staticBackdrop2').modal('show');

                $.ajax({
                    url: "{{ route('tienda_online.excel_carrito') }}",
                    type: "POST",
                    data: formData,
                    processData: false, // tell jQuery not to process the data
                    contentType: false
                }).done(function(data) {
                    //window.location.href = "{{ route('tienda_online.carrito') }}"
                    var obj = jQuery.parseJSON(data);
                    console.log(obj);
                    if (obj.mensajes != "") {
                        $(".modal-excel").html(obj.mensajes);
                        $("#staticBackdropLabel2").text(
                            'Espera un momento mas, estamos actualizando tu carrito...')
                    }

                    if (obj.productos.length <= 0) {
                        $(".modal-excel").append(
                            "<br>Tu excel no tiene productos que podamos agregar, revisalo");
                        $("#staticBackdropLabel2").text('Revisa tu excel');
                        $(".botones-excel").show();
                    } else {
                        var i = 0;
                        $.ajaxSetup({
                            async: false
                        });
                        $.each(obj.productos, function(index, value) {

                            $.post("{{ route('tienda_online.carrito_actualizar') }}", {
                                    'funcion': 'agregar',
                                    'numero_parte': value.clave,
                                    'cantidad': value.cantidad,
                                    'partida': value.partida,
                                    '_token': '{{ csrf_token() }}',
                                    'sustituto': value.sustituto
                                },
                                function(data, textStatus, jqXHR) {
                                    i++;
                                    if (obj.productos.length == i) {
                                        $("#staticBackdropLabel2").text('Listo!')
                                        $(".botones-excel").show();
                                    }
                                },
                                'json'
                            );
                        });


                    }


                }).fail(function() {
                    alert('Ocurrio un error, valida tu archivo.')
                });
            }
        });
    </script>
@endsection
@section('css')
    <style type="text/css">
        .posicion {
            display: block;
            text-align: left;
            word-wrap: break-word;
            white-space: normal !important;
        }

        .pasar_especial_producto {
            display: block;
            color: white;
            background-color: #d31531;
            border-radius: 50px;
            padding: 3px;
            margin-top: 10px;
            width: 100%;
        }

        .actualizar_producto {
            display: block;
            color: white;
            background-color: rgb(43, 57, 145);
            border-radius: 50px;
            padding: 3px;
            margin-top: 10px;
            width: 100%;
        }

        .actualizar_producto_especial {
            display: block;
            color: white;
            background-color: #d31531;
            border-radius: 50px;
            padding: 5px;
            margin-top: 10px;
            width: 100%;
            border: none;
        }
    </style>
@endsection
@section('js')
@endsection
