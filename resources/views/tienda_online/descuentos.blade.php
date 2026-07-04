@extends('tienda_online.base.base')
@section('contenido')
<!-- Start Page Banner -->
    <div class="pb-2 pt-5">
    <div class="">
        <div class="">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6 text-left offset-md-3">
                        <h4>Nuestros productos en descuento</h4>
                        <h4>
                            @if($q !== '' || $grupoFiltro !== '' || $subgrupoFiltro !== '')
                                {{ $total_filtrados }} de {{ $total_resultados }} producto(s)
                                <small class="text-muted">
                                    @if($subgrupoFiltro !== '')— {{ $subgrupoFiltro }}@endif
                                    @if($q !== '')— buscando: "{{ $q }}"@endif
                                </small>
                            @else
                                {{ $total_resultados }} producto(s)
                            @endif
                        </h4>
                    </div>
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
                <div class="col-lg-3 col-md-3">
                    {{-- Buscador propio de descuentos (independiente del de /productos).
                         Va en el sidebar y se mantiene visible tambien en movil. --}}
                    {{-- La busqueda es global: NO envia subgrupo, asi buscar limpia
                         cualquier categoria seleccionada (son excluyentes). --}}
                    <form action="{{ route('tienda_online.descuentos') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ $q }}" class="form-control"
                                   placeholder="Buscar en descuentos...">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                        @if($q !== '')
                            <a class="d-inline-block mt-1 small" href="{{ route('tienda_online.descuentos') }}">
                                <i class="bi bi-x-circle"></i> Limpiar búsqueda
                            </a>
                        @endif
                    </form>

                    <div class="shop-category d-none d-md-block">
                        <div class="category-title">
                            <a href="{{ route('tienda_online.descuentos') }}">Filtrar descuentos por:</a>
                        </div>
                        <div class="shop-category-menu">
                            <ul class="w-100 list-group">
                                <li class="list-group-item w-100 {{ $subgrupoFiltro === '' ? 'active' : '' }}">
                                    <small>
                                        <a href="{{ route('tienda_online.descuentos') }}"
                                           class="{{ $subgrupoFiltro === '' ? 'text-white' : '' }}">
                                            Ver todos
                                        </a>
                                    </small>
                                </li>
                                @foreach($subgrupos as $sg)
                                    <li class="list-group-item w-100 {{ $subgrupoFiltro === $sg ? 'active' : '' }}">
                                        <small>
                                            <a href="{{ route('tienda_online.descuentos', ['subgrupo' => $sg]) }}"
                                               class="{{ $subgrupoFiltro === $sg ? 'text-white' : '' }}">
                                                {{ $sg }}
                                            </a>
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-lg-9 col-md-9">
                    <div class="row">
                        @forelse($resultados as $resultado)
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
                                                    <a href="{{ route('tienda_online.detalles_producto',str_replace('/','_',$resultado->codigo_nikko)) }}">{{$resultado->codigo_nikko}}</a>
                                                    <small>{{ $resultado->marca_comercial }}</small>
                                                </h3>
                                                @if($resultado->equivalencia_1 !="")<small>{{$resultado->equivalencia_1}}</small> @endif
                                                @if($resultado->equivalencia_2 !="")<small>{{$resultado->equivalencia_2}}</small> @endif
                                                @if($resultado->equivalencia_3 !="")<small>{{$resultado->equivalencia_3}}</small> @endif
                                                @if($resultado->equivalencia_4 !="")<small>{{$resultado->equivalencia_4}}</small> @endif
                                                @if($resultado->equivalencia_5 !="")<small>{{$resultado->equivalencia_5}}</small> @endif

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
                                                    <li>{{$resultado->grupo}} - {{$resultado->subgrupo}}</li>
                                                    <li>Disponible: <b class="existencia_real_{{ $loop->index }}"></b></li>
                                                    <li>Precio: <b class="rayado precio_standar_{{ $loop->index }}"></b>&nbsp;<b class="precio_real_{{ $loop->index }}"></b></li>
                                                    <li class="notas_precio_{{ $loop->index }}"></li>
                                                    <li>
                                                        @if(!is_null($resultado->vigencia))
                                                            <span class="badge" style="background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;">
                                                                Hasta el {{ \Carbon::parse($resultado->vigencia)->format('d/m/Y') }}
                                                            </span>
                                                        @else
                                                            <span class="badge" style="background:#eef2ff;color:#3949ab;border:1px solid #c5cae9;">
                                                                Promo sin fecha de vencimiento
                                                            </span>
                                                        @endif
                                                    </li>
                                                </ul>
                                                <script>
                                                        setTimeout(() => {
                                                            $.get( "https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto",
                                                                    { cliente: '{{ \Auth::user()->clave_cliente }}', clave: '{{ $resultado->codigo_nikko }}', tipo: 'normal' },
                                                                    function (data, textStatus, jqXHR) {

                                                                            var obj = data;
                                                                            if(data.code == 0 ){
                                                                                return false;
                                                                            }

                                                                            producto_partida = obj;

                                                                            var notas = "";
                                                                            var precio = obj.precio_publico;
                                                                            var precio_iva = obj.precio_iva;
                                                                            var cantidad = $("#cantidad").val() != "" ? $("#cantidad").val() : 1;

                                                                            var precio_viejo = obj.descuentos[0].precio_iva;
                                                                            var precio_diferente = 0;
                                                                            var porcentaje = 1;
                                                                            @if(isset(\Auth::user()->clienteData))
                                                                                @if(\Auth::user()->clienteData->tiendita)
                                                                                    porcentaje = 1 + ({{ \Auth::user()->clienteData->porcentaje }}/100);
                                                                                @endif
                                                                            @endif

                                                                            if (obj.descuentos.length > 1) {
                                                                                for (i = obj.descuentos.length - 1; i >= 1; i--) {
                                                                                    if (obj.descuentos[i].tipo == "S") {
                                                                                        if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                                                                            precio = obj.descuentos[i].precio_lista;
                                                                                            precio_iva = obj.descuentos[i].precio_iva;
                                                                                            break;
                                                                                        } else {
                                                                                            if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                                                                                precio = obj.descuentos[i].precio_lista;
                                                                                                precio_iva = obj.descuentos[i].precio_iva;
                                                                                                break;
                                                                                            } else {
                                                                                                if(parseFloat(obj.descuentos[i].unidades_minimas) == 1){
                                                                                                    precio_diferente = obj.descuentos[i].precio_lista;
                                                                                                }
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

                                                                            if(parseFloat(precio_diferente) != 0){
                                                                                precio = precio_diferente;
                                                                            }
                                                                            if(precio_viejo < precio){
                                                                                        $.each(obj.descuentos, function(i, val) {
                                                                                            if(val.precio_lista > precio_viejo)
                                                                                                precio_viejo = val.precio_lista;
                                                                                        });
                                                                            }

                                                                            if(precio_viejo > precio)
                                                                                $('.precio_standar_{{ $loop->index }}').html("$ "+parseFloat(precio_viejo * porcentaje).toFixed(2));

                                                                            $('.precio_real_{{ $loop->index }}').html("$ "+parseFloat(precio * porcentaje).toFixed(2));
                                                                            $('.existencia_real_{{ $loop->index }}').html(parseInt(obj.existencia));
                                                                            $('.notas_precio_{{ $loop->index }}').html(notas);
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
                                                        <a href="{{route('tienda_online.detalles_producto',str_replace('/','_',$resultado->codigo_nikko))}}">Ver detalles&nbsp;<i class="bi bi-eye-fill"></i></a>
                                                    </li>
                                                    <li>
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
                        @empty
                            <div class="col-12 text-center py-5">
                                <h5 class="text-muted">No hay productos en descuento
                                    @if($q !== '') para "{{ $q }}"
                                    @elseif($grupoFiltro !== '' || $subgrupoFiltro !== '') para este filtro @endif.
                                </h5>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginacion con el mismo estilo que /tienda_online/productos --}}
                    <div class="col-lg-12 col-md-12">
                        <div class="pagination-area">
                            @if($pagina > 1)
                                <a href="{{ \Request::url().$peticion.($pagina-1) }}" class="prev page-numbers">
                                    <i class='bx bxs-chevron-left'></i>
                                </a>
                            @endif
                            @foreach($botones as $value)
                                @if($pagina == $value)
                                    <span class="page-numbers current" aria-current="page">{{ $value }}</span>
                                @elseif("..." == $value)
                                    <span class="page-numbers">{{ $value }}</span>
                                @else
                                    <a href="{{ \Request::url().$peticion.$value }}" class="page-numbers">{{ $value }}</a>
                                @endif
                            @endforeach
                            @if($pagina < $total_paginas)
                                <a href="{{ \Request::url().$peticion.($pagina+1) }}" class="next page-numbers">
                                    <i class='bx bxs-chevron-right'></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <style>
                        .rayado{
                            text-decoration:line-through;
                            color:red;
                        }
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
