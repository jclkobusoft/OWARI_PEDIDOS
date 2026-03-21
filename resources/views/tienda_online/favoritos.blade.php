@extends('tienda_online.base.base')
@section('contenido')
<!-- Start Page Banner -->
    <div class="pb-2 pt-5">
        <div class="d-table">
            <div class="d-table-cell">
                <div class="container">
                    <div class="col-12 text-left">
                        <h4><i class="bi bi-bookmark-star"></i>&nbsp;Favoritos</h4>
                        <h4>{{ count($resultados) }} producto(s)</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Banner -->

    <!-- Start Shop Area -->
    <section class="shop-area pb-5 pt-5">
        <div class="container">
            <div class="row">
               

                <div class="col-lg-12 col-md-12">
                    <div class="row">
                        @foreach($resultados as $key => $resultado)
                            <div class="col-md-4 col-6">
                                <div class="shop-item-box">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-sm-6 col-12">
                                            <div class="shop-image">
                                                <a href="{{route('tienda_online.detalles_producto',str_replace('/','_',$resultado->codigo_nikko))}}">
                                                <?php
                                                        if(str_contains($resultado->codigo_nikko, '/'))
                                                            $codigo_nikko = str_replace("/", "_", $resultado->codigo_nikko);
                                                        else 
                                                            $codigo_nikko = $resultado->codigo_nikko;

                                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                     


                                                        if(is_dir($directory))
                                                            $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                        else
                                                            $files = [];
                                                    
                                                        arsort($files);
                                                        
                                                    ?>
                                                    @if(count($files) > 0)
                                                        <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image">
                                                    @else
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image">
                                                    @endif
                                                </a>
                                            </div>
                                           
                                            
                                        </div>
                                        <div class="col-lg-6 col-sm-6 col-12">
                                            <div class="shop-content">
                                                <h3 style="margin:0;">
                                                    <a href="{{ route('tienda_online.detalles_producto',str_replace('/','_',$resultado->codigo_nikko)) }}">{{$resultado->codigo_nikko }}</a>
                                                    <small>{{ $resultado->marca_comercial }}</small>
                                                </h3>
                                                @foreach($resultado->equivalencias as $equiv)
                                                    <small>{{$equiv}}</small>
                                                @endforeach

                                                <ul class="shop-list">
                                                    <li>{{$resultado->descripcion_1}} @if($resultado->descripcion_2 != "") {{$resultado->descripcion_2}} @endif @if($resultado->descripcion_3 != "") {{$resultado->descripcion_3}} @endif</li>
                                                    @if($resultado->caracteristicas_1 != "") 
                                                        <li>{{$resultado->caracteristicas_1}}</li>
                                                    @endif
                                                    @if($resultado->caracteristicas_2 != "") 
                                                        <li>{{$resultado->caracteristicas_2}}</li>
                                                    @endif
                                                    @if($resultado->caracteristicas_3 != "") 
                                                        <li>{{$resultado->caracteristicas_3}}</li>
                                                    @endif
                                                    @if($resultado->caracteristicas_4 != "") 
                                                        <li>{{$resultado->caracteristicas_4}}</li>
                                                    @endif
                                                    <li>{{$resultado->grupo}} - {{$resultado->subgrupo}}</li>
                                                    <li>Stock: <b class="existencia_real_{{ $key }}"></b></li>
                                                    <li>Precio: <b class="precio_real_{{ $key }}"></b></li>
                                                    <li class="notas_precio_{{ $key }}">
                                                        
                                                    </li>

                                                </ul>
                                                <script>
                                                        setTimeout(() => {
                                                            $.get( "https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto",
                                                                    { cliente: '{{ \Auth::user()->clave_cliente }}', clave: '{{ $resultado->codigo_nikko }}', tipo: 'normal' },
                                                                    function (data, textStatus, jqXHR) {


                                                                            var obj = data;
                                                                            if(data.code == 0 ){
                                                                                alert(data.mensaje);
                                                                                return false;
                                                                            }


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
                                                                                                    parseFloat(obj.descuentos[i].precio_lista*porcentaje).toFixed(2)+"</b><br>";
                                                                                                continue;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } else {precio = obj.descuentos[0].precio_lista;precio_iva = obj.descuentos[0].precio_iva; }

                                                                              

                                                                            $('.precio_real_{{ $key }}').html("$ "+parseFloat(precio * porcentaje).toFixed(2));
                                                                            $('.existencia_real_{{ $key }}').html(parseInt(obj.existencia));
                                                                            $('.notas_precio_{{ $key }}').html(notas);
                                                                    }
                                                                );
                                                        }, 500);
                                                    </script>
                                            </div>
                                        </div>
                                    
                                        <div class="col-lg-12 col-sm-12 col-12">
                                            <div class="shop-content">
                                                <ul class="shop-btn-list">
                                                    <li>
                                                        <!--<a href="wishlist.html" class="mb-1 btn-primary">Agregar a mis favoritos</a>-->
                                                        <a href="{{route('tienda_online.detalles_producto',str_replace('/','_',$resultado->codigo_nikko))}}">Ver detalles&nbsp;<i class="bi bi-eye-fill"></i></a>
                                                    </li>
                                                    <li>
                                                        <!--<a href="wishlist.html" class="mb-1 btn-primary">Agregar a mis favoritos</a>-->
                                                        <?php
                                                            $favorito = App\Models\Favorito::where('numero_parte',$resultado->codigo_nikko)->first();
                                                        ?>
                                                        @if($favorito)
                                                        <button data-numero="{{ $resultado->codigo_nikko }}" data-funcion="quitar" class="boton-fav favorito">Quitar de favoritos&nbsp;<i class="bi bi-x-circle-fill"></i></button>
                                                        @else
                                                        <button data-numero="{{ $resultado->codigo_nikko }}" data-funcion="agregar" class="boton-fav favorito">Añadir a favoritos&nbsp;<i class="bi bi-bookmark-plus-fill"></i></button>
                                                        @endif
                                                    </li>
                                                          
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <style>
                        small{
                            font-size:10px;
                            line-height:12px;
                            display:block;
                        }
                        .shop-item-box .shop-content .shop-list li {
                            font-size:15px;
                            line-height:17px;
                            margin-bottom:2px;
                        }
                        .shop-image img{
                            max-height: 150px
                        }

                        @media only screen and (max-width: 768px) {
                            .shop-item-box .shop-content{ 
                                text-align:left;
                            }
                            .shop-item-box .shop-content .shop-list li {
                                font-size:11px;
                                line-height:13px;
                                margin-bottom:0px;
                                text-align: left;
                            }
                            .shop-item-box .shop-content h3 {
                                font-size:14px;
                                line-height:16px;
                            }
                            .shop-image img{
                                max-height: 100px
                            }
                            .shop-item-box {
                                padding:5px 10px;
                            }
                            .shop-item-box .shop-content {
                                margin-bottom:10px;
                            }
                            .shop-item-box{
                                margin: 5px 0px!important;
                            }
                        }
                    </style>

                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Area -->
@endsection
@section('css')
<style type="text/css">
    .shop-item-box .shop-content .shop-list li {
        font-size:12px;
    }
    .boton-fav{
        color:white;
        width: 100%;
        background-color:rgb(43,57,145);
        font-size:14px;
        border-radius:50px;
    }
</style>
@endsection
@section('js')
<script>
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
      $('#alerta_carrito').removeClass('show');
    });
</script>
@endsection