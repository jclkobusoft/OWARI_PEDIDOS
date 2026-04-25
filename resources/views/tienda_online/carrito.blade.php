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
                                                        if ('{{ data_get($producto, 'clave_proveedor', '') }}' === 'S227') obj.existencia = existenciaSae + 2;
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

                                                        if ('{{ data_get($producto, 'clave_proveedor', '') }}' === 'S227') obj.existencia = parseInt(obj.existencia) + 2;
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
                if (pedidoEnviado) return false;

                if ($("#recibir").val() == "" || $("#forma_pago").val() == "") {
                    alert("Selecciona la forma de recepcion de tu mercancia y como vas a pagar tus productos");
                    return false;
                }

                pedidoEnviado = true;
                $(this).attr('disabled', 'disabled').text('Enviando pedido...');
                guardar_pedido();
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
            // NUEVO FLUJO DE GUARDADO (refactor en construccion)
            // ════════════════════════════════════════════════════════════════════
            // El click en #guardar SIGUE invocando guardar_pedido() (viejo).
            // Cuando el v2 este completo y validado, se cambia el handler y se
            // borra el viejo. Mientras tanto, ambos coexisten.
            //
            // Para probar el v2 desde la consola del navegador:
            //   guardar_pedido_v2()
            //
            // Estructura del v2:
            //   guardar_pedido_v2()              ← orquestador (async/await)
            //     ├── validarFormulario()
            //     ├── obtenerClienteSae()
            //     ├── capturarEnSoma()           (no bloqueante, fire-and-forget)
            //     ├── separarPartidas()          ← usa PROVEEDORES_ESPECIALES
            //     ├── consultarRegalo()
            //     ├── guardarEspecialesGenerales()
            //     ├── guardarEspecialesSyd()
            //     ├── clasificarPorEmpresa()     ← regla del W en pos.4 de CLASIFIC
            //     ├── insertarEnSaeConRetry()    (x2: factura y/o remision)
            //     ├── guardarPedidoWebLocal()
            //     └── redirigirExito()
            //
            // Helpers UI:    mostrarCargando, mostrarError, redirigirExito
            // Helpers datos: todasLasPartidas, cargarProveedoresEspeciales
            // ════════════════════════════════════════════════════════════════════

            // Catalogo de proveedores especiales (cargado al iniciar la pagina).
            // Mapa { 'S227': {clave, nombre, tipo_separacion, stock_ficticio}, ... }
            // separarPartidas() lo usa para decidir el destino de cada partida.
            var PROVEEDORES_ESPECIALES = {};

            async function cargarProveedoresEspeciales() {
                try {
                    var r = await fetch('https://owari.appsoma.online/somma/v2.0/api/proveedores-especiales');
                    var data = await r.json();
                    PROVEEDORES_ESPECIALES = Object.fromEntries(
                        (data.proveedores || []).map(function(p) { return [p.clave, p]; })
                    );
                } catch (e) {
                    console.warn('No se pudo cargar proveedores_especiales:', e);
                    PROVEEDORES_ESPECIALES = {};
                }
            }
            cargarProveedoresEspeciales();

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

                try {
                    // 1. Datos del cliente desde SAE (CLASIFIC + CAMPLIB3)
                    var cliente = await obtenerClienteSae();

                    // 2. Espejo en SOMA (todo el pedido tal cual; no bloqueante)
                    capturarEnSoma(cliente, todasLasPartidas());

                    // 3. Separar partidas en {sae, especiales}
                    //    especiales es un mapa {claveProveedor: [...]} con los grupos
                    //    de cada proveedor especial (S227, AAAA, etc.)
                    var separadas = separarPartidas();

                    // 4. Consultar regalo (si aplica, se agrega al bucket sae)
                    var regalo = await consultarRegalo(cliente);
                    if (regalo) separadas.sae.push(regalo);

                    // 5. Guardar pedidos especiales (uno por proveedor)
                    await guardarEspecialesGenerales(separadas.especiales);

                    // 6. Clasificar SAE por empresa segun CLASIFIC del cliente
                    var clasificacion = clasificarPorEmpresa(separadas.sae, cliente.CLASIFIC);

                    // 7. Insertar en SAE con retry (1 o 2 pedidos)
                    //    Cada llamada devuelve:
                    //      string  → folio SAE
                    //      null    → no hay partidas para esa empresa
                    //      object  → { queued:true, id_pendiente } cuando los
                    //                5 retries fallaron y el pedido quedo
                    //                encolado en backend para retry diferido
                    var resultadoFactura  = await insertarEnSaeConRetry(clasificacion.factura, 1);
                    var resultadoRemision = clasificacion.remision.length
                        ? await insertarEnSaeConRetry(clasificacion.remision, 3)
                        : null;

                    var folioFactura  = (typeof resultadoFactura  === 'string') ? resultadoFactura  : null;
                    var folioRemision = (typeof resultadoRemision === 'string') ? resultadoRemision : null;

                    var idsPendientes = [];
                    if (resultadoFactura  && resultadoFactura.queued)  idsPendientes.push(resultadoFactura.id_pendiente);
                    if (resultadoRemision && resultadoRemision.queued) idsPendientes.push(resultadoRemision.id_pendiente);
                    var hayQueued = idsPendientes.length > 0;

                    // 8. Espejo local — guardamos los folios que SI se lograron y
                    //    enviamos los ids_pendientes_sae para que el controller
                    //    enlace al PedidoWeb recien creado. Asi el job artisan
                    //    podra actualizar el espejo cuando termine de insertar.
                    var idPedido = await guardarPedidoWebLocal({
                        cliente: cliente.CLAVE,
                        folio_factura:        folioFactura,
                        folio_remision:       folioRemision,
                        partidas_sae:         clasificacion.factura.concat(clasificacion.remision),
                        especiales:           separadas.especiales,
                        regalo:               regalo,
                        ids_pendientes_sae:   idsPendientes,
                    });

                    // 9. Si hay pendientes encolados, mostrar mensaje distinto;
                    //    sino, redirigir a exito normal
                    if (hayQueued) {
                        mostrarPendiente(idPedido);
                    } else {
                        redirigirExito(idPedido);
                    }

                } catch (err) {
                    console.error('guardar_pedido_v2 fallo:', err);
                    mostrarError(err.message || 'Ocurrio un error inesperado');
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
                // pueda intentar de nuevo.
                $("#staticBackdropLabel").text('Error');
                $("#staticBackdrop .modal-body").html(
                    '<h5>' + (mensaje || 'Ocurrio un error inesperado.') + '</h5>'
                );
                $("#staticBackdrop .modal-footer").show();
                $('#staticBackdrop').modal('show');

                $("#guardar").removeAttr('disabled').text('Generar pedido');
            }

            function redirigirExito(idPedido) {
                // Redirige a la pantalla de exito con el id del pedido local.
                var url = "{{ route('tienda_online.guardado_exitoso') }}" +
                          '?id_pedido=' + encodeURIComponent(idPedido || '');
                window.location.href = url;
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

                // 2. Partidas del cartEspecial — siempre a especiales por proveedor
                for (var j = 0; j < partidas_especiales_finales.length; j++) {
                    var pe = Object.assign({}, partidas_especiales_finales[j]);
                    var claveProvE = (pe.clave_proveedor || '').trim();
                    pushEspecial(claveProvE, pe);
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

            function clasificarPorEmpresa(partidasSae, clasif) {
                // Decide si cada partida SAE va a empresa 1 (factura, con IVA)
                // o empresa 3 (remision, sin IVA), segun la CLASIFIC del cliente.
                //
                // Reglas (replicadas de externos/guardarPedido original):
                //   - Si CLASIFIC NO tiene W en pos.4 → todo a factura (E01)
                //   - Si CLASIFIC tiene W en pos.4:
                //       existencia_remision >= 0       → remision (E03)
                //       existencia_factura > 0
                //         AND cantidad <= existencia_factura → factura (E01)
                //       resto → DESCARTADA (bug heredado, se preserva)
                //
                // El descarte se loguea como warning para que se pueda diagnosticar
                // desde devtools sin afectar el comportamiento visible al cliente.
                var factura  = [];
                var remision = [];

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

            function capturarEnSoma(cliente, partidas) {
                // POST al proxy local soma.capturar_proxy, que reenvia a SOMA
                // /api/pedidos/capturar con X-API-Key.
                //
                // Fire-and-forget: no esperamos respuesta. Si SOMA cae, el flujo
                // sigue normal (graceful degradation). Si SOMA acepta, queda
                // espejo del pedido alla.
                //
                // SOMA es la fuente de verdad nueva: recibe TODAS las partidas
                // (cart + cartEspecial) sin separar; aplica sus reglas internas
                // (politicas, descuentos, division normal/especial, etc.).
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
                    return {
                        codigo:              String(pr.clave || ''),
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
                if (!especialesPorProveedor) return;

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

                    await guardarEspecialUnGrupo(data, claveProveedor);
                }
            }

            // Wrapper que envuelve $.post en una promise para usar con await,
            // y no truena el flujo si una falla — solo loguea warning.
            function guardarEspecialUnGrupo(data, claveProveedor) {
                return new Promise(function(resolve) {
                    $.post("{{ route('pedidos.guardar_especial') }}", data)
                        .done(function() { resolve(); })
                        .fail(function(xhr) {
                            console.warn(
                                'guardarEspecialesGenerales fallo proveedor=' + claveProveedor,
                                xhr && xhr.status,
                                xhr && xhr.responseText
                            );
                            resolve();   // continuar con el siguiente proveedor
                        });
                });
            }

            async function insertarEnSaeConRetry(partidas, empresa) {
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
                console.warn('insertarEnSaeConRetry agoto reintentos para empresa=' + empresa + ', encolando...');
                var idPendiente = await encolarSaePendiente(partidas, empresa, ultimoError);

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
            async function encolarSaePendiente(partidas, empresa, ultimoError) {
                var partidasParaSae = (partidas || []).map(function(p) {
                    return partidaParaSae(p, empresa);
                });

                var data = {
                    '_token':       '{{ csrf_token() }}',
                    cliente:        '{{ \Auth::user()->clave_cliente }}',
                    empresa:        empresa,
                    usuario:        '{{ \Auth::user()->name }}',
                    su_pedido:      'Pedido Online',
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
            // Reglas:
            //   - empresa 1 (factura): precio sin IVA, total con IVA
            //   - empresa 3 (remision): precio sin IVA, total sin IVA
            function partidaParaSae(p, empresa) {
                var cantidad     = parseInt(p.cantidad) || 0;
                var precioSinIva = parseFloat(p.precio_iva) || 0;
                var totalConIva  = parseFloat(p.total) || 0;

                return {
                    clave:    p.codigo,
                    cantidad: cantidad,
                    precio:   precioSinIva,
                    total:    empresa === 1 ? totalConIva : (precioSinIva * cantidad),
                };
            }

            async function guardarPedidoWebLocal(payload) {
                // POST a tienda_online.guardar_pedido para crear el espejo del
                // pedido en pedidos_web (Postgres local). Es lo ultimo del flujo:
                // ya tenemos folios SAE y pedidos especiales guardados, esto solo
                // deja constancia local para "Mis pedidos" del cliente.
                //
                // Mandamos folio_factura y folio_remision por separado para que
                // el controller los persista en pedido_sae y pedido_sae_remision
                // respectivamente. pedido_sae sigue conteniendo SOLO el folio
                // de factura (sin breaking change con codigo en produccion que
                // lo lee como folio principal).
                //
                // Devuelve el id local de PedidoWeb para redirigir a la
                // pantalla de exito.
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
                    ids_pendientes_sae:  payload.ids_pendientes_sae || [],
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


            // ════════════════════════════════════════════════════════════════════
            // FIN del esqueleto v2 — abajo continua el guardar_pedido() VIEJO
            // ════════════════════════════════════════════════════════════════════


            function guardar_pedido() {

                var partidas_syd_finales = [];
                var partidas_finales_ajustadas = [];
                for (var i = 0; i < partidas_finales.length; i++) {
                    var p = Object.assign({}, partidas_finales[i]);
                    if (p.clave_proveedor === 'S227') {
                        var cant = parseInt(p.cantidad);
                        var existenciaSae = parseInt(p.existencia_sae != null ? p.existencia_sae : 0);
                        if (existenciaSae <= 0) {
                            partidas_syd_finales.push(p);
                            continue;
                        } else if (cant > existenciaSae) {
                            var faltante = cant - existenciaSae;
                            var pSae = Object.assign({}, p);
                            pSae.cantidad = existenciaSae;
                            pSae.total = (existenciaSae * parseFloat(pSae.precio)).toFixed(2);
                            partidas_finales_ajustadas.push(pSae);

                            var pSyd = Object.assign({}, p);
                            pSyd.cantidad = faltante;
                            pSyd.total = (faltante * parseFloat(pSyd.precio)).toFixed(2);
                            partidas_syd_finales.push(pSyd);
                            continue;
                        }
                    }
                    partidas_finales_ajustadas.push(p);
                }
                partidas_finales = partidas_finales_ajustadas;
                var partidas_formulario = partidas_finales;

                // Mover partidas especiales S227 al pedido SYD (no van a especial tradicional)
                var partidas_especiales_ajustadas = [];
                for (var i = 0; i < partidas_especiales_finales.length; i++) {
                    var pe = Object.assign({}, partidas_especiales_finales[i]);
                    if (pe.clave_proveedor === 'S227') {
                        partidas_syd_finales.push(pe);
                    } else {
                        partidas_especiales_ajustadas.push(pe);
                    }
                }
                partidas_especiales_finales = partidas_especiales_ajustadas;

                // Consolidar partidas SYD por codigo (sumar cantidades y totales de duplicados)
                var sydPorCodigo = {};
                for (var i = 0; i < partidas_syd_finales.length; i++) {
                    var ps = partidas_syd_finales[i];
                    var cod = ps.codigo;
                    if (!sydPorCodigo[cod]) {
                        sydPorCodigo[cod] = Object.assign({}, ps);
                        sydPorCodigo[cod].cantidad = parseInt(ps.cantidad) || 0;
                        sydPorCodigo[cod].total = parseFloat(ps.total) || 0;
                    } else {
                        sydPorCodigo[cod].cantidad += parseInt(ps.cantidad) || 0;
                        sydPorCodigo[cod].total += parseFloat(ps.total) || 0;
                    }
                }
                partidas_syd_finales = Object.values(sydPorCodigo).map(function(p){
                    p.total = (parseFloat(p.total)).toFixed(2);
                    return p;
                });

                function enviarSYD(onDone) {
                    if (partidas_syd_finales.length === 0) { onDone && onDone(null); return; }
                    var dataSYD = {
                        cliente: '{{ \Auth::user()->clave_cliente }}',
                        '_token': "{{ csrf_token() }}",
                        partidas: partidas_syd_finales,
                        carrito: 1,
                        clave_proveedor: 'S227'
                    };
                    $.post("{{ route('pedidos.guardar_especial') }}", dataSYD,
                        function(res) {
                            try {
                                var r = (typeof res === 'string') ? JSON.parse(res) : res;
                                onDone && onDone(r);
                            } catch(e) { onDone && onDone(null); }
                        }
                    ).fail(function() { onDone && onDone(null); });
                }

                var variables = {
                    usuario: '{{ \Auth::user()->name }}',
                    cliente: '{{ \Auth::user()->clave_cliente }}',
                    partidas: partidas_formulario,
                    partidas_detalle: partidas,
                    partidas_especiales: partidas_especiales_finales,
                    partidas_especiales_detalle: partidas_especiales,
                    'su_pedido': 'Pedido Online',
                    'empresa_seleccionada': 1,
                    'tipo': 'factura',
                    'pedido_sae': 0,
                    '_token': "{{ csrf_token() }}",
                    'fecha_recoge': $("#fecha_recoge").val(),
                    'metodo_pago': $("#metodo_pago").val(),
                    'forma_pago': $("#forma_pago").val(),
                    'uso_cfdi': $("#uso_cfdi").val(),
                    'gran_total' : gran_total + gran_total_especial
                };

                var partidas_soma_especial = [];
                for (var i = 0; i < partidas_especiales_finales.length; i++) {
                    partidas_soma_especial.push({
                        "clave": partidas_especiales_finales[i].codigo,
                        "cantidad": partidas_especiales_finales[i].cantidad,
                        "precio": partidas_especiales_finales[i].precio,
                        "total": partidas_especiales_finales[i].total
                    });
                }

                var soma_especial = {
                    "clave_cliente": "{{ \Auth::user()->clave_cliente }}",
                    "clave_sucursal": "E01",
                    "tipo_serie": "PE",
                    "partidas": partidas_soma_especial
                }

                var partidas_soma_pedido = [];
                for (var i = 0; i < partidas_formulario.length; i++) {
                    partidas_soma_pedido.push({
                        "clave": partidas_formulario[i].codigo,
                        "cantidad": partidas_formulario[i].cantidad,
                        "precio": partidas_formulario[i].precio,
                        "total": partidas_formulario[i].total
                    });
                }

                var soma_pedido = {
                    "clave_cliente": "{{ \Auth::user()->clave_cliente }}",
                    "clave_sucursal": "E01",
                    "tipo_serie": "W",
                    "partidas": partidas_soma_pedido
                }

                $('#staticBackdrop').modal('show');

                @if(\Auth::user()->clave_cliente == "M014M")
                    $.post("{{ route('pedidos.guardar_pedido_pendiente_web') }}", variables,
                            function(data, textStatus, jqXHR) {
                                if (data.code) {
                                    window.location.href =
                                        "{{ route('tienda_online.guardado_pendiente_exitoso') }}?id_pedido=" + data
                                        .id_pedido;
                                } else {
                                    $(".modal-body").html(
                                        "<h5>Tu pedido no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>"
                                        );
                                    $(".modal-footer").show();
                                }
                            },
                            "json"
                        );


                @else
                    @if (count($productos_especiales) >= 0 && count($productos) <= 0)
                        (function enviarSoloEspeciales() {
                            var finalizar = function(idEspecial, idSyd) {
                                var idPedido = idEspecial || idSyd || '';
                                $.get("{{ route('tienda_online.vaciar_carrito') }}").always(function() {
                                    window.location.href = "{{ route('tienda_online.guardado_exitoso') }}?id_pedido=" + idPedido + "&tipo=especial";
                                });
                            };

                            var tieneEspeciales = partidas_especiales_finales.length > 0;
                            var tieneSYD = partidas_syd_finales.length > 0;

                            if (!tieneEspeciales && !tieneSYD) {
                                $(".modal-body").html("<h5>No hay partidas para guardar.</h5>");
                                $(".modal-footer").show();
                                return;
                            }

                            if (tieneEspeciales) {
                                var data = {
                                    cliente: '{{ \Auth::user()->clave_cliente }}',
                                    '_token': "{{ csrf_token() }}",
                                    partidas: partidas_especiales_finales,
                                    carrito: 1
                                };
                                $.ajax({
                                    url: "{{ route('pedidos.guardar_especial') }}",
                                    type: "POST",
                                    data: data,
                                    success: function(responseData) {
                                        var idEspecial = '';
                                        try {
                                            var res = (typeof responseData === 'string') ? JSON.parse(responseData) : responseData;
                                            idEspecial = (res && (res.id_pedido || res.id)) ? (res.id_pedido || res.id) : '';
                                        } catch(e) {}
                                        enviarSYD(function(sydRes) {
                                            var idSyd = (sydRes && (sydRes.id_pedido || sydRes.id)) ? (sydRes.id_pedido || sydRes.id) : '';
                                            finalizar(idEspecial, idSyd);
                                        });
                                    },
                                    error: function(jqXHR) {
                                        console.log('guardar_especial error:', jqXHR.status, jqXHR.responseText);
                                        $(".modal-body").html("<h5>Tu pedido especial no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
                                        $(".modal-footer").show();
                                    }
                                });
                            } else {
                                enviarSYD(function(sydRes) {
                                    var idSyd = (sydRes && (sydRes.id_pedido || sydRes.id)) ? (sydRes.id_pedido || sydRes.id) : '';
                                    finalizar('', idSyd);
                                });
                            }
                        })();

                    @else

                        if (partidas_finales.length === 0) {
                            (function enviarSoloEspecialesAux() {
                                var finalizar = function(idEspecial, idSyd) {
                                    var idPedido = idEspecial || idSyd || '';
                                    window.location.href = "{{ route('tienda_online.guardado_exitoso') }}?id_pedido=" + idPedido + "&tipo=especial";
                                };

                                if (partidas_especiales_finales.length > 0) {
                                    var data = {
                                        cliente: '{{ \Auth::user()->clave_cliente }}',
                                        '_token': "{{ csrf_token() }}",
                                        partidas: partidas_especiales_finales,
                                        carrito: 1
                                    };
                                    $.post("{{ route('pedidos.guardar_especial') }}", data, function(responseData) {
                                        var idEspecial = '';
                                        try {
                                            var res = (typeof responseData === 'string') ? JSON.parse(responseData) : responseData;
                                            idEspecial = (res && (res.id_pedido || res.id)) ? (res.id_pedido || res.id) : '';
                                        } catch(e) {}
                                        enviarSYD(function(sydRes) {
                                            var idSyd = (sydRes && (sydRes.id_pedido || sydRes.id)) ? (sydRes.id_pedido || sydRes.id) : '';
                                            finalizar(idEspecial, idSyd);
                                        });
                                    }).fail(function() {
                                        $(".modal-body").html("<h5>Tu pedido especial no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
                                        $(".modal-footer").show();
                                    });
                                } else {
                                    enviarSYD(function(sydRes) {
                                        var idSyd = (sydRes && (sydRes.id_pedido || sydRes.id)) ? (sydRes.id_pedido || sydRes.id) : '';
                                        finalizar('', idSyd);
                                    });
                                }
                            })();
                            return;
                        }

                        $.post("https://sistemasowari.com:8443/catalowari/api/guardar_web", variables,
                            function(data, textStatus, jqXHR) {
                                if (data.code) {
                                    variables.pedido_sae = data.pedido;
                                    var folioSae = data.pedido;

                                    // ── Captura paralela en SOMA (independiente, no bloqueante) ──
                                    try {
                                        var idEnvioExterno = (window.crypto && crypto.randomUUID) ? crypto.randomUUID()
                                            : ('env-' + Date.now() + '-' + Math.random().toString(36).slice(2));

                                        var partidasParaSoma = [];
                                        for (var i = 0; i < partidas_formulario.length; i++) {
                                            partidasParaSoma.push({
                                                clave: partidas_formulario[i].codigo,
                                                cantidad: partidas_formulario[i].cantidad
                                            });
                                        }
                                        for (var j = 0; j < partidas_especiales_finales.length; j++) {
                                            partidasParaSoma.push({
                                                clave: partidas_especiales_finales[j].codigo,
                                                cantidad: partidas_especiales_finales[j].cantidad
                                            });
                                        }

                                        var somaPayload = {
                                            clave_cliente: '{{ \Auth::user()->clave_cliente }}',
                                            partidas: partidasParaSoma,
                                            origen: 'TIENDA_ONLINE',
                                            id_envio_externo: idEnvioExterno,
                                            destino_sucursal: 'E01',
                                            gran_total_origen: gran_total + gran_total_especial,
                                            pedidos_sae: [{
                                                folio: folioSae,
                                                sucursal_sae: 'E01',
                                                gran_total: gran_total + gran_total_especial,
                                                partidas: partidasParaSoma.length
                                            }]
                                        };

                                        $.ajax({
                                            url: '{{ route('soma.capturar_proxy') }}',
                                            method: 'POST',
                                            contentType: 'application/json',
                                            data: JSON.stringify(somaPayload),
                                            timeout: 30000
                                        }).fail(function (xhr) {
                                            if (window.console) console.warn('SOMA capture fallo', xhr && xhr.status, xhr && xhr.responseText);
                                        });
                                    } catch (e) {
                                        if (window.console) console.warn('SOMA capture excepcion', e);
                                    }
                                    // ── Fin captura paralela SOMA ──

                                    var mensajeErrorPostSAE = function(detalle) {
                                        $(".modal-body").html(
                                            "<div style='text-align:left;'>" +
                                            "<h5 style='color:#c62828;'>Tu pedido ya fue registrado en SAE con el folio <b>" + folioSae + "</b>.</h5>" +
                                            "<p>Sin embargo, ocurrio un problema al registrar informacion adicional. <b>Por favor NO vuelvas a enviar este pedido</b>.</p>" +
                                            "<p>Comunicate con ventas y reporta el folio <b>" + folioSae + "</b> para que se le de seguimiento.</p>" +
                                            (detalle ? "<p style='font-size:12px;color:#888;'>Detalle: " + detalle + "</p>" : "") +
                                            "</div>"
                                        );
                                        $(".modal-footer").show();
                                    };

                                    var pasoEspecial = function(cb) {
                                        if (partidas_especiales_finales.length > 0) {
                                            var dataEsp = {
                                                cliente: '{{ \Auth::user()->clave_cliente }}',
                                                '_token': "{{ csrf_token() }}",
                                                partidas: partidas_especiales_finales,
                                                carrito: 1
                                            };
                                            $.post("{{ route('pedidos.guardar_especial') }}", dataEsp, function() { cb(); }, "json")
                                                .fail(function() { cb(); });
                                        } else { cb(); }
                                    };
                                    var pasoSYD = function(cb) { enviarSYD(function() { cb(); }); };
                                    var pasoPedido = function() {
                                        $.post("{{ route('tienda_online.guardar_pedido') }}", variables,
                                            function(data) {
                                                if (data.code) {
                                                    window.location.href = "{{ route('tienda_online.guardado_exitoso') }}?id_pedido=" + data.id_pedido;
                                                } else {
                                                    mensajeErrorPostSAE('No se pudo registrar localmente.');
                                                }
                                            }, "json"
                                        ).fail(function() {
                                            mensajeErrorPostSAE('Error de red al registrar localmente.');
                                        });
                                    };

                                    pasoEspecial(function() { pasoSYD(function() { pasoPedido(); }); });
                                } else {
                                    pedidoEnviado = false;
                                    $("#guardar").removeAttr('disabled').text('Generar pedido');
                                    $(".modal-body").html("<h5>Tu pedido no se guardo en SAE. Da click en el boton cerrar e intenta guardarlo nuevamente.</h5>");
                                    $(".modal-footer").show();
                                }
                            },
                            "json"
                        ).fail(function() {
                            pedidoEnviado = false;
                            $("#guardar").removeAttr('disabled').text('Generar pedido');
                            $(".modal-body").html("<h5>No se pudo contactar a SAE. Da click en el boton cerrar e intenta guardarlo nuevamente.</h5>");
                            $(".modal-footer").show();
                        });
                    @endif
                @endif
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
