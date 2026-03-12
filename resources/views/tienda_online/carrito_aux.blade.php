@extends('tienda_online.base.base')
@section('contenido')
 <!-- Start Cart Area -->
        @if(count($productos) <= 0 && count($productos_especiales) <= 0)
        <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class=" mb-30">
                            <h2>No hay productos en tu carrito</h2>
                             <a href="{{ route('tienda_online.productos') }}?q=&p=1"class="default-btn" style="margin-top: 20px;">
                                Ver y buscar productos
                            </a>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class=" mb-30">
                            <br><br>
                            <h4>o sube tu excel <a href="{{ asset('/tienda_online/PlantillaOwari.xlsx') }}" target=_blank>(descarga la plantilla aqui)</a></h4>
                            <div class="input-group mb-2">
                              <input type="file" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                              <button class="btn btn-outline-secondary" type="button" id="inputGroupFileAddon04">Agregar al carrito</button>
                            </div>
                    </div>
                </div>
            </div>
        </section>
          <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
            $i=0;
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
                            <h4>Tambien puedes subir tu excel <a href="{{ asset('/tienda_online/PlantillaOwari.xlsx') }}" target=_blank>(descarga la plantilla aqui)</a></h4>
                            <div class="input-group mb-2">
                              <input type="file" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                              <button class="btn btn-outline-secondary" type="button" id="inputGroupFileAddon04">Agregar al carrito</button>
                            </div>
                        </div>
                    </div>
                    @if(count($productos) > 0)
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
                                        @foreach($productos as $key =>  $producto)
                                            <?php
                                                $i++;
                                                $producto = (object) $producto;
                                                if (str_contains($producto->codigo_nikko, '/')) {
                                                    $codigo_nikko = str_replace("/", ":", $producto->codigo_nikko);
                                                } else {
                                                    $codigo_nikko = $producto->codigo_nikko;
                                                }

                                                
                                                $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                if (file_exists($directory)) {
                                                     $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                } else {
                                                    $files = [];
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <a class="remove quitar_producto" data-numero="{{ $producto->codigo_nikko }}"><i class='bi bi-x-circle'></i></a>
                                                </td>
                                                <td class="product-thumbnail">
                                                    <a href="#">
                                                        @if(count($files) > 0)
                                                                 <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[0],PHP_EOL) }}" alt="Product Image">
                                                        @else
                                                            <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image">

                                                        @endif
                                                    </a>
                                                </td>
            
                                                <td class="product-name">
                                                   <span class="numero_parte">
                                                        <a href="{{route('tienda_online.detalles_producto',$producto->codigo_nikko)}}">
                                                        <b>{{ $producto->codigo_nikko }}</b>
                                                        </a>
                                                    </span>
                                                    <span class="posicion">
                                                        <b>Marca: </b>{{ $producto->marca_comercial }}
                                                    </span>
                                                    <span class="posicion">
                                                        {{ $producto->descripcion_1 }}
                                                    </span>
                                                    @if(isset($producto->sustituto))
                                                    @if($producto->sustituto == "true")
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
                                                    <small style="color:red; display:block;">{!! $producto->mensaje_existencia !!}</small>
                                                    @if($producto->solicitado > 0)
                                                        <div class="input-counter">
                                                            <span class="minus-btn"><i class='bx bx-minus'></i></span>
                                                            <input type="text" value="{{ $producto->solicitado }}" min="1" max="1" class="cantidad_{{ $i }}">
                                                            <span class="plus-btn"><i class='bx bx-plus'></i></span>
                                                        </div>
                                                        @if($producto->codigo_nikko != "MOCHILA")
                                                        <button class="actualizar_producto update_{{ $i }}" data-i="{{ $i }}" data-numero="{{ $producto->codigo_nikko }}" data-maximo="1"><i class="bx bx-refresh"></i> Actualizar cantidad</button>
                                                        @endif
                                                    @else
                                                        <button data-clave="{{ $producto->codigo_nikko }}" data-cantidad="{{ $producto->solicitado_original }}" data-partida="{{ json_encode($producto->partida) }}" class="pasar_especial_producto boton_especial_{{ $i }}" type="button"><i class="bx bx-refresh"></i> Pasar a pedido especial</button>
                                                        <span style="color:red; display:none;" class="es_liquidacion_{{ $i }}">Son existencias unicas, apresurate a cerrar tu pedido.</span>
                                                    @endif

                                                </td>
            
                                                <td class="product-subtotal">
                                                    <span class="total_partida_{{ $i }}">$0.00</span>
                                                </td>
                                                <script>
                                                     var obj = $.parseJSON('{!! html_entity_decode(str_replace(['\'','\"'],'',json_encode($producto->partida))) !!}');
                                                     var obj_{{$i}} = $.parseJSON('{!! html_entity_decode(str_replace(['\'','\"'],'',json_encode($producto->partida))) !!}');
                                                     var producto_partida = obj;

                                                    //analisis para saber que politiva le toca
                                                    var notas = "";
                                                    var precio = obj.precio_publico;
                                                    var precio_iva = obj.precio_iva;
                                                    var cantidad = {{ $producto->solicitado }};
                                                    //console.log("TAMANO:" + obj.descuentos.length);
                                                      var porcentaje = 1;
                                                        @if(isset(\Auth::user()->clienteData))
                                                            @if(\Auth::user()->clienteData->tiendita)
                                                                      
                                                                porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }}/100);
                                                                
                                                            @endif
                                                        @endif
                                                    var notas = "";

                                                    if(obj.en_liquidacion){
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
                                                                            parseFloat(obj.descuentos[i].precio_lista * porcentaje).toFixed(2)+"</b><br>";
                                                                        continue;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {precio = obj.descuentos[0].precio_lista;precio_iva = obj.descuentos[0].precio_iva; }

                                                    $('.precio-unitario-{{ $i }}').html("$ "+parseFloat(precio*porcentaje).toFixed(2));
                                                    $('.total_partida_{{ $i }}').html("$ "+parseFloat(precio*cantidad*porcentaje).toFixed(2));
                                                    $('.cantidad_{{ $i }}').attr('max',obj.existencia);
                                                    $('.update_{{ $i }}').attr('data-maximo',obj.existencia);
                                                    @if($producto->solicitado > 0)
                                                        partidas_finales.push(
                                                            {
                                                                "codigo" : '{{ $producto->codigo_nikko }}',
                                                                "descripcion" : '{{ $producto->descripcion_1 }}',
                                                                "cantidad" : cantidad,
                                                                "precio" : parseFloat(precio).toFixed(2),
                                                                "precio_iva" : parseFloat(precio_iva).toFixed(2),
                                                                "total" : (cantidad*precio).toFixed(2)
                                                            }
                                                        )
                                                        partidas.push(producto_partida);

                                                        gran_total+=precio*cantidad;
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
                    @if(count($productos_especiales) > 0)
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
                                        
                                        @foreach($productos_especiales as $key =>  $producto)
                                            <?php
                                                $i++;
                                                $producto = (object) $producto;
                                                if (str_contains($producto->codigo_nikko, '/')) {
                                                    $codigo_nikko = str_replace("/", ":", $producto->codigo_nikko);
                                                } else {
                                                    $codigo_nikko = $producto->codigo_nikko;
                                                }

                                                
                                                $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                if (file_exists($directory)) {
                                                     $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                } else {
                                                    $files = [];
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <a class="remove quitar_producto_especial" data-numero="{{ $producto->codigo_nikko }}"><i class='bi bi-x-circle'></i></a>
                                                </td>
                                                <td class="product-thumbnail">
                                                    <a href="#">
                                                        @if(count($files) > 0)
                                                                 <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[0],PHP_EOL) }}" alt="Product Image">
                                                        @else
                                                            <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image">

                                                        @endif
                                                    </a>
                                                </td>
            
                                                <td class="product-name">
                                                   <span class="numero_parte">
                                                        <a href="{{route('tienda_online.detalles_producto',$producto->codigo_nikko)}}">
                                                        <b>{{ $producto->codigo_nikko }}</b>
                                                        </a>
                                                    </span>
                                                    <span class="posicion">
                                                        <b>Marca: </b>{{ $producto->marca_comercial }}
                                                    </span>
                                                    <span class="posicion">
                                                        {{ $producto->descripcion_1 }}
                                                    </span>
                                                    @if(isset($producto->sustituto))
                                                    @if($producto->sustituto == "true")
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
                                                        <input type="text" value="{{ $producto->solicitado }}" min="1" max="1" class="cantidad_{{ $i }}">
                                                        <span class="plus-btn"><i class='bx bx-plus'></i></span>
                                                    </div>
                                                    <button class="actualizar_producto_especial update_{{ $i }}" data-i="{{ $i }}" data-numero="{{ $producto->codigo_nikko }}" data-maximo="1"><i class="bx bx-refresh"></i> Actualizar cantidad</button>

                                                </td>
            
                                                <td class="product-subtotal">
                                                    <span class="total_partida_{{ $i }}">$0.00</span>
                                                </td>
                                                <script>
                                                     var obj = $.parseJSON('{!! html_entity_decode(str_replace(['\'','\"'],'',json_encode($producto->partida))) !!}');
                                                     var obj_{{$i}} = $.parseJSON('{!! html_entity_decode(str_replace(['\'','\"'],'',json_encode($producto->partida))) !!}');
                                                     var producto_partida = obj;

                                                    //analisis para saber que politiva le toca
                                                    var notas = "";
                                                    var precio = obj.precio_publico;
                                                    var precio_iva = obj.precio_iva;
                                                    var cantidad = {{ $producto->solicitado }};
                                                    //console.log("TAMANO:" + obj.descuentos.length);
                                                      var porcentaje = 1;
                                                        @if(isset(\Auth::user()->clienteData))
                                                            @if(\Auth::user()->clienteData->tiendita)
                                                                      
                                                                porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }}/100);
                                                                
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
                                                                            parseFloat(obj.descuentos[i].precio_lista * porcentaje).toFixed(2)+"</b><br>";
                                                                        continue;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {precio = obj.descuentos[0].precio_lista;precio_iva = obj.descuentos[0].precio_iva; }


                                                    if(obj.cliente=="N/A")
                                                        precio = {{ $producto->precio_normal }}

                                                    $('.precio-unitario-{{ $i }}').html("$ "+parseFloat(precio*porcentaje).toFixed(2));
                                                    $('.total_partida_{{ $i }}').html("$ "+parseFloat(precio*cantidad*porcentaje).toFixed(2));
                                                    $('.cantidad_{{ $i }}').attr('max',obj.existencia);
                                                    $('.update_{{ $i }}').attr('data-maximo',obj.existencia);

                                                    partidas_especiales_finales.push(
                                                        {
                                                            "codigo" : '{{ $producto->codigo_nikko }}',
                                                            "descripcion" : '{{ $producto->descripcion_1 }}',
                                                            "cantidad" : cantidad,
                                                            "precio" : parseFloat(precio).toFixed(2),
                                                            "precio_iva" : parseFloat(precio_iva).toFixed(2),
                                                            "total" : (cantidad*precio).toFixed(2),
                                                            "sae" : obj.cliente=="N/A" ? "NO ESTA EN SAE" : 'EN SAE'
                                                        }
                                                    )
                                                    partidas_especiales.push(producto_partida);

                                                    gran_total_especial+=precio*cantidad;
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
                           

                            <h3>Entrega y factura</h3>
                            <div class="mb-3">
                                  <label for="recibir" class="form-label">¿Como recibire mi mercancia?:</label>
                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" id="recibir">
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
                            <div class="mb-3" >
                                  <label for="fecha_recoge" class="form-label" id="texto_fecha_recoge">Pasare por mi pedido a las (solo si recogo en tienda):</label>
                                  <input type="datetime-local" id="fecha_recoge" class="form-control" placeholder="Dia/Hora para recoger el pedido" min="{{ date('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="mb-3">
                                  <label for="metodo_pago" class="form-label">Metodo pago:</label>
                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" id="metodo_pago">
                                        <option value="PUE">PUE Pago en una sola exhibición</option>
                                        <option value="PPD">PPD Pago en parcialidades o diferido</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="uso_cfdi" class="form-label">Uso del CFDI:</label>
                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" id="uso_cfdi">
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
                                        <option value="D01">D01 Honorarios médicos, dentales y gastos hospitalarios.</option>
                                        <option value="D02">D02 Gastos médicos por incapacidad o discapacidad.</option>
                                        <option value="D03">D03 Gastos funerales.</option>
                                        <option value="D04">D04 Donativos.</option>
                                        <option value="D05">D05 Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).</option>
                                        <option value="D06">D06 Aportaciones voluntarias al SAR.</option>
                                        <option value="D07">D07 Primas por seguros de gastos médicos.</option>
                                        <option value="D08">D08 Gastos de transportación escolar obligatoria.</option>
                                        <option value="D09">D09 Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.</option>
                                        <option value="D10">D10 Pagos por servicios educativos (colegiaturas).</option>
                                        <option value="S01">S01 Sin efectos fiscales.  </option>
                                    </select>
                                </div>
                             <h3 class="mt-3">Total</h3>
                            <ul>
                                <li>Total a pagar <span class="gran_total">$0.00</span></li>
                            </ul>
    
                            <button id="guardar" class="default-btn">
                                Generar pedido
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Guardando pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

         <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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

            $.get('https://sistemasowari.com:8443/catalowari/api/cliente', { cliente: '{{ \Auth::user()->clave_cliente }}'} ,function(data) {
                /*optional stuff to do after success */
                data = jQuery.parseJSON(data);

                if(data.cliente.STATUS != "A"){
                    alert("Tu cuenta se encuentra suspendida, favor de comunitarte al area de cobranza. Cel. 56-1318-4858");
                    location.href="{{ route("tienda_online.dashboard") }}";
                }


                if(data.cliente.CAMPLIB1 == "MOSTRADOR" || String(data.cliente.CAMPLIB1).includes('CTE')){

                }
                else{
                    //$("#fecha_recoge").attr('type','text').attr('readonly', 'readonly').val(data.cliente.CAMPLIB1);
                    //$("#texto_fecha_recoge").text('Enviado por:');
                }

                //$("#forma_pago").val(data.cliente.FORMADEPAGOSAT);
                $("#uso_cfdi").val(data.cliente.USO_CFDI);
                $("#metodo_pago").val(data.cliente.METODODEPAGO).trigger('change');

            });

              var porcentaje = 1;
                @if(isset(\Auth::user()->clienteData))
                    @if(\Auth::user()->clienteData->tiendita)
                              
                        porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }}/100);
                        
                    @endif
                @endif

            $('.gran_total').html("$ "+parseFloat(gran_total*porcentaje).toFixed(2));
            $('.gran_total_especial').html("$ "+parseFloat(gran_total_especial*porcentaje).toFixed(2));

		

		@if(count($productos_especiales) <= 0)
            if(parseFloat(gran_total) < 400)
                $("#guardar").text("Tu pedido debe de ser mayor a $400.00").attr('disabled','disabled');
        @endif


            $('.pasar_especial_producto').click(function (e) {
                e.preventDefault();
                var $cantidad = $(this).data('cantidad');
                var $numero_parte = $(this).data('clave');
                var $producto_partida = $(this).data('partida');
                console.log($producto_partida);

                $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'quitar', 'numero_parte': $numero_parte, 'cantidad': 0 ,'_token' : '{{  csrf_token() }}' },
                    function (data, textStatus, jqXHR) {
                        $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : $producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                            function (data, textStatus, jqXHR) {
                                if(data.code){
                                     window.location = "{{ route('tienda_online.carrito') }}";
                                }
                            },
                            'json'
                        );
                    },
                    'json'
                );
            });



            $('.quitar_producto').click(function (e) {
                e.preventDefault();
                var $cantidad = $(this).closest('.agregar_carrito').find('input').val();
                var $numero_parte = $(this).data('numero');

                $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'quitar', 'numero_parte': $numero_parte, 'cantidad': 0 ,'_token' : '{{  csrf_token() }}' },
                    function (data, textStatus, jqXHR) {
                        if(data.code){
                          window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });
            $('.quitar_producto_especial').click(function (e) {
                e.preventDefault();
                var $cantidad = $(this).closest('.agregar_carrito').find('input').val();
                var $numero_parte = $(this).data('numero');

                $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", { 'funcion' : 'quitar', 'numero_parte': $numero_parte, 'cantidad': 0 ,'_token' : '{{  csrf_token() }}' },
                    function (data, textStatus, jqXHR) {
                        if(data.code){
                          window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });

            $('.actualizar_producto').click(function (e) {
                e.preventDefault();
            
                var $cantidad = $('.cantidad_'+$(this).data('i')).val();
                
                var $numero_parte = $(this).data('numero');
                var $maximo_stock = $(this).data('maximo');

                if(parseInt($cantidad) > parseInt($maximo_stock)){
                    alert('Solo tenemos en existencia: '+$maximo_stock+'. Ingresa una cantidad menor.');
                    return false;
                }



                $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'actualizar', 'numero_parte': $numero_parte, 'cantidad': $cantidad, partida: window['obj_'+$(this).data('i')] ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                    function (data, textStatus, jqXHR) {
                        if(data.code){
                          window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });

            $('.actualizar_producto_especial').click(function (e) {
                e.preventDefault();
            
                var $cantidad = $('.cantidad_'+$(this).data('i')).val();
                
                var $numero_parte = $(this).data('numero');
                var $maximo_stock = 1000000;

                if(parseInt($cantidad) > parseInt($maximo_stock)){
                    alert('Solo tenemos en existencia: '+$maximo_stock+'. Ingresa una cantidad menor.');
                    return false;
                }



                $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", { 'funcion' : 'actualizar', 'numero_parte': $numero_parte, 'cantidad': $cantidad, partida: window['obj_'+$(this).data('i')] ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                    function (data, textStatus, jqXHR) {
                        if(data.code){
                          window.location = "{{ route('tienda_online.carrito') }}";
                        }
                    },
                    'json'
                );
            });


            $("#guardar").click(function(event) {
                /* Act on the event */
                if($("#recibir").val() == "" || $("#forma_pago").val() == ""){
                	alert("Selecciona la forma de recepcion de tu mercancia y como vas a pagar tus productos");
                	return false;
                }
                $(this).attr('disabled','disabled');
                //console.log('hola');
                guardar_pedido();
            });


    function guardar_pedido(){
        
        var partidas_formulario = partidas_finales;
        
        var variables = {
            usuario: '{{ \Auth::user()->name }}',
            cliente: '{{ \Auth::user()->clave_cliente }}',
            partidas: partidas_formulario,
            partidas_detalle: partidas,
            partidas_especiales: partidas_especiales_finales,
            partidas_especiales_detalle: partidas_especiales,
            'su_pedido': 'Pedido Online',
            'empresa_seleccionada' : 1,
            'tipo': 'factura',
            'pedido_sae' : 0,
            '_token' : "{{ csrf_token() }}",
            'fecha_recoge':$("#fecha_recoge").val(),
            'metodo_pago':$("#metodo_pago").val(),
            'forma_pago':$("#forma_pago").val(),
            'uso_cfdi':$("#uso_cfdi").val()
        };

        $('#staticBackdrop').modal('show');
        
        
        @if(count($productos_especiales) >= 0 && count($productos) <= 0) 
        	var data = {
                            cliente: '{{ \Auth::user()->clave_cliente }}',
                            '_token' : "{{ csrf_token() }}",
                            partidas: partidas_especiales_finales,
                            carrito : 1
                        };


                        $.post("{{ route('pedidos.guardar_especial') }}", data,
                            function (data, textStatus, jqXHR) {
								alert('Tu pedido fue enviado correctamente');
								location.reload();
                            },
                            "json"
                        );
        
        
        @else

        $.post("https://sistemasowari.com:8443/catalowari/api/guardar_web", variables,
            function (data, textStatus, jqXHR) {
                if(data.code){
                    variables.pedido_sae = data.pedido;

                    if(partidas_especiales_finales.length > 0){

                        var data = {
                            cliente: '{{ \Auth::user()->clave_cliente }}',
                            '_token' : "{{ csrf_token() }}",
                            partidas: partidas_especiales_finales,
                            carrito : 1
                        };


                        $.post("{{ route('pedidos.guardar_especial') }}", data,
                            function (data, textStatus, jqXHR) {
								
                            },
                            "json"
                        );
                    }


                    $.post("{{ route('tienda_online.guardar_pedido') }}", variables,
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                                window.location.href = "{{ route('tienda_online.guardado_exitoso') }}?id_pedido="+data.id_pedido;
                            }
                            else{
                                $(".modal-body").html("<h5>Tu pedido no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
                                $(".modal-footer").show();
                            }
                        },
                        "json"
                    ).fail(function(){
                        $(".modal-body").html("<h5>Tu pedido no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
                        $(".modal-footer").show();
                    });
                }
                else{
                      $(".modal-body").html("<h5>Tu pedido no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
                     $(".modal-footer").show();
                }
            },
            "json"
        ).fail(function(){
            $(".modal-body").html("<h5>Tu pedido no se guardo, da click en el boton cerrar e intenta guardarlo nuevamente .</h5>");
            $(".modal-footer").show();
        });
        
        @endif
    }

        $('#metodo_pago').change(function(event) {
            /* Act on the event */
            if($(this).val() == "PPD"){
                $("#forma_pago option:not(:contains('99'))").attr("disabled","disabled");
                $("#forma_pago").val('99');
            }
            else{
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
                }
                else{
                    var formData = new FormData();
                    formData.append('excel', $('#inputGroupFile04')[0].files[0]);
                    formData.append('_token','{{ csrf_token() }}');
                    $('#staticBackdrop2').modal('show');

                    $.ajax({
                      url: "{{ route('tienda_online.excel_carrito') }}",
                      type: "POST",
                      data: formData,
                      processData: false,  // tell jQuery not to process the data
                      contentType: false 
                    }).done(function(data) {
                        //window.location.href = "{{ route('tienda_online.carrito')}}"
                        var obj = jQuery.parseJSON(data);
                        console.log(obj);
                        if(obj.mensajes != ""){
                            $(".modal-excel").html(obj.mensajes);
                            $("#staticBackdropLabel2").text('Espera un momento mas, estamos actualizando tu carrito...')
                        }

                        if(obj.productos.length <= 0){
                            $(".modal-excel").append("<br>Tu excel no tiene productos que podamos agregar, revisalo");
                            $("#staticBackdropLabel2").text('Revisa tu excel');
                            $(".botones-excel").show();
                        }
                        else{
                            var i = 0;
                            $.ajaxSetup({async: false});  
                            $.each(obj.productos, function( index, value ) {
                                
                                $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': value.clave, 'cantidad': value.cantidad, 'partida' : value.partida ,'_token' : '{{  csrf_token() }}', 'sustituto': value.sustituto },
                                        function (data, textStatus, jqXHR) {
                                            i++;
                                            if(obj.productos.length == i ){
                                                $("#staticBackdropLabel2").text('Listo!')
                                                $(".botones-excel").show();
                                            }
                                        },
                                        'json'
                                    );
                                });


                        }


                    }).fail(function(){
                        alert('Ocurrio un error, valida tu archivo.')
                    });
                }
            });
        </script>
@endsection
@section('css')
<style type="text/css">
    .posicion{
        display:block;
        text-align:left;
        word-wrap: break-word;
        white-space: normal !important;
    }
    .pasar_especial_producto{
        display: block;
        color:white;
        background-color:#d31531;
        border-radius:50px;
        padding:3px;
        margin-top:10px;
        width: 100%;
    }
    .actualizar_producto{
        display: block;
        color:white;
        background-color:rgb(43, 57, 145);
        border-radius:50px;
        padding:3px;
        margin-top:10px;
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
        border:none;
}

</style>
@endsection
@section('js')

@endsection