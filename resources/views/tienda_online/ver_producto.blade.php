@extends('tienda_online.base.base')                   
@section('contenido')

    <!-- Start Products Details Area -->
    <section class="products-details-area ptb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="products-details-desc">
                        <div class="row align-items-center">
                            <div class="col-lg-7 col-md-6" style="position:relative">
                                <img class="foto_liquidacion" src="/images/liquidacion.png" style="width:80px;position:absolute;top:0px;left:0;z-index:1;display:none;">
                                <div class="main-products-image">
                                    <div class="slider-productos">
                                          <?php
                                                        if(str_contains($producto->codigo_nikko, '/'))
                                                            $codigo_nikko = str_replace("/", "_", $producto->codigo_nikko);
                                                        else 
                                                            $codigo_nikko = $producto->codigo_nikko;

                                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                     


                                                        if(is_dir($directory))
                                                            $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                        else
                                                            $files = [];
                                                    
                                                        arsort($files);
                                                        
                                                    ?>
                                        @if(count($files) > 0)
                                                @foreach($files as $key => $value)
                                                    <div class="slide">
 <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[$key],PHP_EOL) }}" alt="Product Image">                                                    </div>
                                                @endforeach
                                        @else
                                        <div class="slide">
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image">
                                        </div>
                                        @endif
                                    </div>
                                    <div class="slider-productos-mini">
                                     
                                        @if(count($files) > 0)
                                                @foreach($files as $key => $value)
                                                    <div class="slide">
                                                            <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[$key],PHP_EOL) }}" alt="Product Image">                               
                                                    </div>
                                                @endforeach
                                        @else
                                        <div class="slide">
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image">
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 col-md-6">
                                <div class="product-content">
                                    <h3>{{$producto->codigo_nikko}}</h3>
                                    <!--
                                    <div class="price">
                                        <span class="old-price">$150.00</span>
                                        <span class="new-price">$75.00</span>
                                    </div>
                                    -->
                                    <p>
                                        @if($producto->marca_comercial != "")
                                           <b>Marca: {{$producto->marca_comercial}}</b>
                                        @endif
                                    </p>
                                   

                                    <ul class="products-info">
                                        
                                        @if($producto->descripcion_1 != "")
                                            <li><span>{{$producto->descripcion_1}}</span> </li>
                                        @endif
                                        @if($producto->descripcion_2 != "")
                                            <li><span>{{$producto->descripcion_2}}</span> </li>
                                        @endif
                                        @if($producto->descripcion_3 != "")
                                            <li><span>{{$producto->descripcion_3}}</span> </li>
                                        @endif
                                        @if($producto->caracteristicas_1 != "")
                                            <li><span>{{$producto->caracteristicas_1}}</span> </li>
                                        @endif
                                        @if($producto->caracteristicas_2 != "")
                                            <li><span>{{$producto->caracteristicas_2}}</span> </li>
                                        @endif
                                        @if($producto->caracteristicas_3 != "")
                                            <li><span>{{$producto->caracteristicas_3}}</span> </li>
                                        @endif
                                        @if($producto->caracteristicas_4 != "")
                                            <li><span>{{$producto->caracteristicas_4}}</span> </li>
                                        @endif
                                    </ul>
                                   
                                    <ul class="products-info no_sae">
                                                    <li>Disponible: <b class="existencia_real"></b></li>
                                                    <li class="tachado" style="display:none;">Precio original: <b class="precio_original" style="color:red;text-decoration: line-through;"></b></li>
                                                    <li>Precio: <b class="precio_real"></b></li>
                                                    <li class="notas_precio"></li>
                                    </ul>
                                    <ul class="products-info info_proveedor" style="display: none;">
                                                    
                                                    <li><b></b></li>
                                                     @if(\Auth::user()->clienteData)
                                                        @if(\Auth::user()->clienteData->tiendita)                          
                                                                <li><b class="precio_real">${{ $producto->precio_normal *(1+(\Auth::user()->clienteData->porcentaje/100)) }}</b></li>
                                                        @else
                                                                <li><b class="precio_real">${{ $producto->precio_normal }}</b></li>                       
                                                        @endif
                                                    @else
                                                        <li><b class="precio_real">${{ $producto->precio_normal }}</b></li>     
                                                    @endif
                                                    
                                    </ul>
                                    <div class="venta" style="display:none;">
                                    <div class="product-quantities">
                                            <span>Cantidad:</span>
        
                                            <div class="input-counter input-counter-especial">
                                                <span class="minus-btn">
                                                    <i class="bx bx-minus"></i>
                                                </span>
                                                <input name="cantidad" class="cantidad" type="text" value="1" min="1" max="1000000">
                                                <span class="plus-btn">
                                                    <i class="bx bx-plus"></i>
                                                </span>
                                            </div>
                                        </div>
                                    <div class="product-add-to-cart">
                                        <input type="hidden" id="tipo" name="tipo" value="N">
                                        <button type="button" class="default-btn agregar_al_carrito" data-numero="{{ $producto->codigo_nikko }}">
                                            <i class="bi bi-cart"></i>
                                            Agregar al carrito
                                            <span id="texto_especial"></span>
                                        </button>
                                    </div>
                                </div>
                                 <?php
                                                            $favorito = App\Models\Favorito::where('numero_parte',$producto->codigo_nikko)->first();
                                                        ?>
                                                        @if($favorito)
                                                        <button data-numero="{{ $producto->codigo_nikko }}" data-funcion="quitar" class="boton-fav favorito">Quitar de favoritos&nbsp;<i class="bi bi-x-circle-fill"></i></button>
                                                        @else
                                                        <button data-numero="{{ $producto->codigo_nikko }}" data-funcion="agregar" class="boton-fav favorito">Añadir a favoritos&nbsp;<i class="bi bi-bookmark-plus-fill"></i></button>
                                                        @endif

                                    <script>

                                        var producto_partida;
                                        setTimeout(() => {
                                                            $.get( "https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto",
                                                                    { cliente: '{{ \Auth::user()->clave_cliente }}', clave: '{{ $producto->codigo_nikko }}', tipo: 'factura' },
                                                                    function (data, textStatus, jqXHR) {


                                                                            var obj = data;
                                                                            if(data.code == 0 ){
                                                                                alert(data.mensaje);
                                                                                return false;
                                                                            }
                                                                            

                                                                            if(obj.existencia <= 0 && obj.en_liquidacion){
                                                                                $('.venta').hide();
                                                                                alert("Este producto esta descontinuado.");
                                                                                return false;
                                                                            }

                                                                            if(obj.en_liquidacion){
                                                                                $(".precio_original").html("$"+parseFloat(obj.precio_publico));
                                                                                $(".tachado").show();
                                                                                $(".foto_liquidacion").show();
                                                                            }
                                                                            

                                                                            $(".venta").show();

                                                                            producto_partida = obj;

                                                                            //analisis para saber que politiva le toca
                                                                            var notas = "";
                                                                            var precio = obj.precio_publico;
                                                                            var precio_iva = obj.precio_iva;
                                                                            var cantidad = $("#cantidad").val() != "" ? $("#cantidad").val() : 1;
                                                                            //console.log("TAMANO:" + obj.descuentos.length);
                                                                            var notas = "";

                                                                              var porcentaje = 1;
                                                                            @if(isset(\Auth::user()->clienteData))
                                                                                @if(\Auth::user()->clienteData->tiendita)
                                                                                          
                                                                                    porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }}/100);
                                                                                    
                                                                                @endif
                                                                            @endif

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

                                                                            if(obj.cliente=="N/A"){
                                                                                $("#texto_especial").text(" producto especial")
                                                                                $('.no_sae').hide();
                                                                                $('.info_proveedor').show();
                                                                                precio = {{ $producto->precio_normal }}
                                                                                const first = obj.clasificacion?.[0]?.toUpperCase();
                                                                                const mapa = { A: 0.85, B: 0.9, C: 0.95 };
                                                                                const descuento = mapa[first] ?? 0.95; 
                                                                                console.log(descuento);
                                                                                precio = precio * descuento * 1.16;
                                                                            }

                                                                            if(obj.paqueteria > 0)
                                                                                notas += '<br><b style="color:red">Incluye costo de envio</b>';

                                                                            
                                                                            $('.precio_real').html("$ "+parseFloat(precio * porcentaje).toFixed(2));
                                                                            $('.existencia_real').html(parseInt(obj.existencia));
                                                                            $('.notas_precio').html(notas);

                                                                            var spinner = jQuery(this),
                                                                                input = spinner.find('input[name="cantidad"]'),
                                                                                btnUp = spinner.find('.plus-btn'),
                                                                                btnDown = spinner.find('.minus-btn'),
                                                                                min = input.attr('min'),
                                                                                max = input.attr('max');

                                                                             btnUp.on('click', function() {
                                                                                    var oldValue = parseFloat(input.val());
                                                                                    if (oldValue >= max) {
                                                                                        var newVal = oldValue;
                                                                                    } else {
                                                                                        var newVal = oldValue + 1;
                                                                                    }
                                                                                    console.log(input,min,max,oldValue,newVal);
                                                                                    spinner.find('input[name="cantidad"]').val(newVal);
                                                                                    spinner.find('input[name="cantidad"]').trigger("change");
                                                                                });
                                                                                btnDown.on('click', function() {
                                                                                    var oldValue = parseFloat(input.val());
                                                                                    if (oldValue <= min) {
                                                                                        var newVal = oldValue;
                                                                                    } else {
                                                                                        var newVal = oldValue - 1;
                                                                                    }
                                                                                    console.log(input,min,max,oldValue,newVal);
                                                                                    spinner.find('input[name="cantidad"]').val(newVal);
                                                                                    spinner.find('input[name="cantidad"]').trigger("change");
                                                                                });
                                                                            

                                                                           


                                                                    }
                                                                );
                                                        }, 500);
                                    </script>
                                    @php
                                        $equivConMarca = collect($equivalencias)->filter(fn($e) => $e->id_marca > 0);
                                        $equivSinMarca = collect($equivalencias)->filter(fn($e) => !$e->id_marca || $e->id_marca == 0);
                                    @endphp
                                    @if($equivConMarca->count() > 0)
                                    <br><br><h5>Mismo producto, en otras marcas:</h5>
                                    <ul class="products-info">
                                        @foreach($equivConMarca as $eq)
                                            <li>
                                                <a href="{{route('tienda_online.detalles_producto',$eq->clave)}}">{{$eq->marca}} - {{$eq->clave}}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="products-details-tabs">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description">Compatibilidad</a></li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="description" role="tabpanel">
                                <div class="table-responsive" >
                                <table class="table table-bordered">
                                    <tr>
                                        <th>
                                            Marca
                                        </th>
                                        <th>
                                            Modelo
                                        </th>
                                        <th>
                                            Desde
                                        </th>
                                        <th>
                                            Hasta
                                        </th>
                                        <th>
                                            Generación
                                        </th>
                                        <th>
                                            Versión
                                        </th>
                                        <th>
                                            Motor
                                        </th>
                                        <th>
                                            Nota
                                        </th>
                                    </tr>
                                    @foreach($especificaciones as $especificacion)
                                        @if($especificacion->modelo != "")
                                        <tr>
                                            <td>
                                                {{$especificacion->armadora}}
                                            </td>
                                            <td>
                                                {{$especificacion->modelo}}
                                            </td>
                                            <td>
                                                {{$especificacion->ano_inicial}}
                                            </td>
                                            <td>
                                                {{$especificacion->ano_final}}
                                            </td>
                                            <td>
                                                {{$especificacion->generacion_mexico}}
                                            </td>
                                            <td>
                                                {{$especificacion->version}}
                                            </td>
                                            <td>
                                                {{$especificacion->motor}}
                                            </td>
                                            <td>
                                                {{$especificacion->especificacion}}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    @foreach($especificaciones_extra as $especificacion)
                                        @if($especificacion->modelo != "")
                                        <tr>
                                            <td>
                                                {{$especificacion->armadora}}
                                            </td>
                                            <td>
                                                {{$especificacion->modelo}}
                                            </td>
                                            <td>
                                                {{$especificacion->ano_inicial}}
                                            </td>
                                            <td>
                                                {{$especificacion->ano_final}}
                                            </td>
                                            <td>
                                                {{$especificacion->generacion_mexico}}
                                            </td>
                                            <td>
                                                {{$especificacion->version}}
                                            </td>
                                            <td>
                                                {{$especificacion->motor}}
                                            </td>
                                            <td>
                                                {{$especificacion->especificacion}}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <aside class="widget-area">
                          <div class="col-12 text-right p-3 " style="background-color: #eee;">
                        <form action="{{ route('tienda_online.productos') }}" method="get">
                            <input type="hidden" value="1" name="p">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="q" placeholder="Buscar: Clave, Marca, Modelo, Año" value="">
                                <button class="btn btn-outline-secondary" style="color:white; background-color:rgb(43,57,145)" type="submit" id="button-addon2"><i class="bi bi-search"></i>&nbsp;Buscar</button>
                            </div>
                        </form>
                    </div>


                        <section class="widget widget_categories mt-5">
                            <h3 class="widget-title">Categoria</h3>

                            <ul>
                                <li>
                                    <span>{{$producto->grupo}}</span>
                                </li>
                                <li>
                                    <span>{{$producto->subgrupo}}</span>
                                </li>
                            </ul>
                        </section>

                        @if($equivSinMarca->count() > 0)
                            <section class="widget widget_categories">
                                <h3 class="widget-title">Equivalencias</h3>
                                <ul>
                                    @foreach($equivSinMarca as $equiv)
                                        <li>
                                            <span>{{$equiv->clave}}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                    </aside>
                </div>
            </div>
        </div>
    </section>
    <!-- End Products Details Area -->

    <!-- Start Top Products Area -->
    <section class="top-products-area bg-color pt-100 pb-70">
        <div class="container">
            <div class="section-title">
                <h2>Productos Relacionados</h2>
            </div>

            <div class="row">
                @foreach($relacionados as $relacionado)
                    <div class="col-lg-3 col-sm-6">
                        <div class="">
                            <div class="top-products-image text-center">
                                <a href="{{route('tienda_online.detalles_producto',$relacionado->codigo_nikko)}}">
                                <?php
                                    if(str_contains($relacionado->codigo_nikko, '/'))
                                        $codigo_nikko = str_replace("/", ":", $relacionado->codigo_nikko);
                                    else 
                                        $codigo_nikko = $relacionado->codigo_nikko;


                                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;


                                        if(file_exists($directory))
                                             $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                        else
                                            $files = [];
                                    ?>
                                    @if(count($files) > 0)
 <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image" style="height:200px">                                    @else
                                                                                                    <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image" style="height:200px">

                                    @endif
                                </a>
                                
                        
                           
                            </div>

                            <div class="top-products-content text-center" style="margin-bottom:35px;">
                                <h3>
                                    <a href="{{route('tienda_online.detalles_producto',$relacionado->codigo_nikko)}}">{{$relacionado->codigo_nikko}}</a>
                                    <p>{{$relacionado->descripcion_1}}</p>
                                </h3>
                                <!-- <span>$89.00</span>-->
                            </div>
                        </div>
                    </div>
                @endforeach


            </div>
        </div>
    </section>
   
    @stop

    @section('js')

     <!-- End Top Products Area -->
     <script>

$('.slider-productos').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  fade: true,
  asNavFor: '.slider-productos-mini'
});
$('.slider-productos-mini').slick({
  slidesToShow: 3,
  slidesToScroll: 1,
  asNavFor: '.slider-productos',
  dots: true,
  centerMode: true,
  focusOnSelect: true
});
    

       

        $('.favorito').click(function (e) {
        e.preventDefault();
        var $numero_parte = $(this).data('numero');
        var $funcion = $(this).data('funcion');
        console.log($funcion);
        var elemento = $(this);

        $.post("{{ route('tienda_online.actualizar_favoritos') }}", { 'funcion' : $funcion, 'numero_parte': $numero_parte ,'_token' : '{{  csrf_token() }}' },
            function (data, textStatus, jqXHR) {
                if(data.code){

                  if($funcion == "agregar"){
                    elemento.data('funcion','quitar');
                    elemento.html('Quitar de favoritos <i class="bi bi-x-circle-fill"></i>');
                    $("#alerta_carrito p").text('Este producto fue agregado a tus favoritos correctamente.');

                  }
                  else{
                    elemento.data('funcion','agregar');
                    elemento.html('Añadir a favoritos <i class="bi-bookmark-plus-fill"></i>');
                    $("#alerta_carrito p").text('Este producto fue eliminado de tus favoritos correctamente.');

                  }

                  $("#alerta_carrito").addClass('show');
                }
            },
            'json'
        );
    });

    $('.cerrar_alerta').click(function (e) {
      e.preventDefault();
      $('#alerta_carrito').css('display','none').removeClass('show');
    });

     $('.agregar_al_carrito').click(function (e) {
        e.preventDefault();


        var $cantidad = $('.cantidad').val();
        var $numero_parte = $(this).data('numero');
        var $disponible_proveedor = 1;

        if(producto_partida.cliente=="N/A"){

            if($disponible_proveedor = 1){
                $("#tipo").val("E");
                if(!confirm("Este producto es para pedido especial, y sera agregado a tu carrito de productos especiales.¿Deseas continuar?"))
                    return false;

                $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                    function (data, textStatus, jqXHR) {
                        if(data.code){
                        $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito especial.');
                        $('.cantidad_carrito').html(Object.values(data.carrito).length);
                        $("#alerta_carrito").css('display','inline-block').addClass('show');
                        }
                    },
                    'json'
                );
            }
            else{
                alert("Este producto no se encuentra disponible con el proveedor, no puede solicitarse como especial.");
                return false;
            }

        }
        else{
            
            if($cantidad > producto_partida.existencia){
                
                if(producto_partida.en_liquidacion){
                    alert('Este producto esta en liquidación y solo se puede comprar la cantidad disponible.');
                    $cantidad = producto_partida.existencia;
                    $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                              $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito.');
                              $('.cantidad_carrito').html(Object.values(data.carrito).length);
                              $("#alerta_carrito").css('display','inline-block').addClass('show');
                            }
                        },
                        'json'
                    );
                    return false;
                }

                if($disponible_proveedor != 1){
                    alert('Este producto no esta disponible con proveedor y no se puede pedir como pedido especial, solo se puede comprar la cantidad disponible.');
                    $cantidad = producto_partida.existencia;
                    $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                              $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito.');
                              $('.cantidad_carrito').html(Object.values(data.carrito).length);
                              $("#alerta_carrito").css('display','inline-block').addClass('show');
                            }
                        },
                        'json'
                    );
                    return false;
                }


                if(confirm(`Estas solicitando mas producto del que tenemos en existencia, ${ producto_partida.existencia} se agregaran al carrito y ${ $cantidad - producto_partida.existencia} se agregaran a tu carrito de productos especiales. Presiona aceptar para solicitar todo el producto o presiona cancelar para solo pedir lo que hay en existencia`)){
                    $cantidad_especial = $cantidad - producto_partida.existencia;
                    $cantidad = producto_partida.existencia;
                    
                    $.post("{{ route('tienda_online.carrito_actualizar_especial') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad_especial, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                            function (data, textStatus, jqXHR) {

                                    $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                                        function (data, textStatus, jqXHR) {
                                            if(data.code){
                                              $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito y a tu carrito especial.');
                                              $('.cantidad_carrito').html(Object.values(data.carrito).length);
                                              $("#alerta_carrito").css('display','inline-block').addClass('show');
                                            }
                                        },
                                        'json'
                                    );
                            },
                            'json'
                        );
                }
                else{
                    $cantidad = producto_partida.existencia;
                    $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                              $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito.');
                              $('.cantidad_carrito').html(Object.values(data.carrito).length);
                              $("#alerta_carrito").css('display','inline-block').addClass('show');
                            }
                        },
                        'json'
                    );
                }
            }
            else{
                $.post("{{ route('tienda_online.carrito_actualizar') }}", { 'funcion' : 'agregar', 'numero_parte': $numero_parte ,'cantidad': $cantidad, 'partida' : producto_partida ,'_token' : '{{  csrf_token() }}','sustituto' : false },
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                              $("#alerta_carrito p").text('El producto fue agregado correctamente a tu carrito.');
                              $('.cantidad_carrito').html(Object.values(data.carrito).length);
                              $("#alerta_carrito").css('display','inline-block').addClass('show');
                            }
                        },
                        'json'
                    );
            }

            
        }

        
    });

    

    </script>
    @stop
    @section('css')
    <style>
        .slider-productos{
            margin-top: 30px;
        }
        .slider-productos-mini .slide{
            padding:10px;
            max-height: 250px;
        }
        .slick-arrow::before{
            font-size: 30px;
            color:black;
        }
        .favorito{
                margin-top:10px;
                position: relative;
                border: none;
                padding: 12px 30px;
                background-color: rgb(43,57,145);
                color: #ffffff;
                border: 1px solid rgb(43,57,145);
                cursor: pointer;
                border-radius: 0;
        }
        @media (max-width: 800px) {
            #alerta_carrito {
                position: fixed;
                top: 10px;
                left: 10px;
                width: calc(100% - 20px);
            
        }
}

        

    </style>
    @stop