@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="mb-30">
                                <h4>Creado el: {{  \Carbon::createFromFormat('Y-m-d H:i:s',$pedido->created_at)->format('d/m/Y h:i A')  }}</h4>
                                <div class="table-responsive" style="margin-top: 20px;">
                                <h4>Partidas</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>Clave</td>
                                            <td>Descripción</td>
                                            <td>Cantidad</td>
                                            <td>Precio unitario</td>                                            
                                            <td>Total partida</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pedido->partidas as $partida) 
                                                <tr>
                                                    <td>
                                                        <?php
                                                        if(str_contains($partida->clave, '/'))
                                                            $codigo_nikko = str_replace("/", "_", $partida->clave);
                                                        else 
                                                            $codigo_nikko = $partida->clave;

                                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                     


                                                        if(is_dir($directory))
                                                            $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                        else
                                                            $files = [];
                                                    
                                                        arsort($files);
                                                        
                                                    ?>
                                                    @if(count($files) > 0)
                                                        <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image" width="150px">
                                                    @else
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image" width="150px">
                                                    @endif
                                                    </td>
                                                    <td>{{ $partida->clave }}</td>
                                                    <td>{{ $partida->descripcion }}</td>
                                                    <td>{{ number_format($partida->cantidad,0,'.',',') }}</td>
                                                    <td>$ {{ number_format($partida->precio_unitario,2,'.',',') }}</td>
                                                    <td align="right">${{ number_format($partida->gran_total,2,'.',',') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                        </div>
                        <div style="width: 100%;text-align: right">
                            <h3>Gran total: $ {{ number_format($pedido->gran_total,2,'.',',') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(count($pedidos_sae) > 0)
<div class="container">
    <h3 class="mt-50" style="margin:20px 0 20px">¡Ya tenemos tus productos!</h3>
</div>
@endif
@foreach($pedidos_sae as $pedido)
     <section class="my-account-area pb-100">
            <div class="container">

             <div class="card">
                    <div class="card-header">Pedidos finales</div>
                        <div class="card-body">

                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="mb-30">



                                <h5>Pedido: <b>{{ $pedido->pedido_sae }}</b></h5>
                                <h5>Creado el: {{  \Carbon::createFromFormat('Y-m-d H:i:s',$pedido->created_at)->format('d/m/Y h:i A')  }}</h5>
                                <div class="table-responsive" style="margin-top: 20px;">
                                <h4>Partidas</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>Clave</td>
                                            <td>Cantidad</td>
                                            <td>Precio unitario</td>                                            
                                            <td>Total partida</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pedido->partidas as $partida) 
                                                <tr>
                                                    <td>
                                                        <?php
                                                        if(str_contains($partida->clave, '/'))
                                                            $codigo_nikko = str_replace("/", "_", $partida->clave);
                                                        else 
                                                            $codigo_nikko = $partida->clave;

                                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
                                                     


                                                        if(is_dir($directory))
                                                            $files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
                                                        else
                                                            $files = [];
                                                    
                                                        arsort($files);
                                                        
                                                    ?>
                                                    @if(count($files) > 0)
                                                        <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image" width="150px">
                                                    @else
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image" width="150px">
                                                    @endif
                                                    </td>
                                                    <td>{{ $partida->clave }}</td>
                                                    <td>{{ number_format($partida->cantidad,0,'.',',') }}</td>
                                                    <td>$ {{ number_format($partida->precio_unitario,2,'.',',') }}</td>
                                                    <td align="right">${{ number_format($partida->gran_total,2,'.',',') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                        </div>
                        <div style="width: 100%;text-align: right">
                            <h3>Gran total: $ {{ number_format($pedido->gran_total,2,'.',',') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
         </div>
     </div>
 </div>
        </section>
@endforeach
@endsection
@section('css')
<style type="text/css">
    .login-form{
        max-width: 100%;
    }
    table.table-bordered tr:nth-child(even) {background-color:  transparent}
    table.table-bordered tr:nth-child(odd) {background-color: #f9f7fc}
    thead td {font-weight:bold;}
</style>
@endsection
@section('js')

@endsection