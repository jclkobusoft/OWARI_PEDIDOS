@extends('layouts.app')
@section('content')
    <script>
        var generar_pedido = false;
        var partidas_finales = [];
        var partidas = [];
    </script>
    <div class="container">
        <div class="card">
            <div class="card-header">Pedido pendiente original</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5>Pedido: <strong>{{ $pedido->id }}</strong></h5>
                        <h5>Cliente: <strong>{{ $pedido->cliente }}</strong></h5>
                        <h5>Telefono: <strong>{{ $registrado->telefono }}</strong></h5>
                        <h5>Email: <strong>{{ $registrado->email }}</strong></h5>
                        <h5>Creado el:
                            <strong>{{ \Carbon::createFromFormat('Y-m-d H:i:s', $pedido->created_at)->format('d/m/Y h:i A') }}</strong>
                        </h5>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col d-flex justify-content-end mb-3">
                                <h5 for="cliente" class="col-sm-3"><b>Cliente SAE:</b></h5>
                                <div class="col-sm-9">
                                    <select class="form-select" id="cliente" name="cliente">
                                        <option value="-1">Selecciona o busca un cliente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col d-flex text-start">
                                <button class="btn btn-sm btn-danger pedido" value="cancelar">No aprobado</button>
                            </div>
                            <div class="col d-flex text-end">
                                <button class="btn btn-sm btn-warning pedido" value="bodega">Pedido solo bodega</button>
                            </div>
                            <div class="col d-flex text-start">
                                <button class="btn btn-sm btn-warning pedido" value="especial">Pedido solo especial</button>
                            </div>
                            <div class="col d-flex text-end">
                                <button class="btn btn-sm btn-success pedido" value="todo">Pedido completo</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="margin-top: 20px;">
                    <h4>Bodega</h4>
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
                            @foreach ($partidas as $partida)
                                <tr>
                                    <td>
                                        <?php
                                        if (str_contains($partida->codigo, '/')) {
                                            $codigo_nikko = str_replace('/', '_', $partida->codigo);
                                        } else {
                                            $codigo_nikko = $partida->codigo;
                                        }
                                        
                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/' . $codigo_nikko;
                                        
                                        if (is_dir($directory)) {
                                            $files = \Storage::disk('cms')->allFiles('productos/' . $codigo_nikko . '/');
                                        } else {
                                            $files = [];
                                        }
                                        
                                        arsort($files);
                                        
                                        ?>
                                        @if (count($files) > 0)
                                            <img src="{{ 'https://owari.com.mx/storage/productos/' . $codigo_nikko . '/' . basename($files[array_key_first($files)], PHP_EOL) }}"
                                                alt="Product Image" height="100px">
                                        @else
                                            <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image"
                                                width="150px">
                                        @endif
                                    </td>
                                    <td>{{ $partida->codigo }}</td>
                                    <td>{{ number_format($partida->cantidad, 0, '.', ',') }}</td>
                                    <td>$ {{ number_format($partida->precio, 2, '.', ',') }}</td>
                                    <td align="right">
                                        ${{ number_format($partida->total, 2, '.', ',') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive" style="margin-top: 20px;">
                    <h4>Especial</h4>
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
                            @foreach ($partidas_especiales as $partida)
                                <tr>
                                    <td>
                                        <?php
                                        if (str_contains($partida->codigo, '/')) {
                                            $codigo_nikko = str_replace('/', '_', $partida->codigo);
                                        } else {
                                            $codigo_nikko = $partida->codigo;
                                        }
                                        
                                        $directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/' . $codigo_nikko;
                                        
                                        if (is_dir($directory)) {
                                            $files = \Storage::disk('cms')->allFiles('productos/' . $codigo_nikko . '/');
                                        } else {
                                            $files = [];
                                        }
                                        
                                        arsort($files);
                                        
                                        ?>
                                        @if (count($files) > 0)
                                            <img src="{{ 'https://owari.com.mx/storage/productos/' . $codigo_nikko . '/' . basename($files[array_key_first($files)], PHP_EOL) }}"
                                                alt="Product Image" height="100px">
                                        @else
                                            <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image"
                                                width="150px">
                                        @endif
                                    </td>
                                    <td>{{ $partida->codigo }}</td>
                                    <td>{{ number_format($partida->cantidad, 0, '.', ',') }}</td>
                                    <td>$ {{ number_format($partida->precio, 2, '.', ',') }}</td>
                                    <td align="right">
                                        ${{ number_format($partida->total, 2, '.', ',') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="width: 100%;text-align: right">
                    <h3>Gran total: <strong>$ {{ number_format($pedido->gran_total, 2, '.', ',') }}</strong></h3>
                </div>
            </div>
        </div>
    </div>






    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Actualizando pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Espera un momento, estamos guardando tu pedido...</h5>
                </div>
                <div class="modal-footer" style="display: none;">
                    <a class="default-btn" href="javascript:location.reload();">Cerrar</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <script>
        const url = "https://sistemasowari.com:8443/catalowari/api/clientes_factura";
        $.get(
        url,{ vendedor: '{{ \Auth::user()->vendedor_sae }}' },
        function (data) {
            var obj = jQuery.parseJSON(data);
            clientes = obj;
            
            $("#cliente").html('<option value="-1">Selecciona o busca un cliente</option>')
            $.each(obj, function (i, val) {
                $("#cliente").append(
                    '<option value="' +
                    i +
                    '">' +
                    val.clave +
                    " " +
                    val.nombre +
                    "</option>"
                );
            });

            $("#cliente").val('-1')
            .chosen({ no_results_text: "Oops, no hay resultados!" })
            .trigger('chosen:updated').trigger('chosen:activate');

        });

        $('.pedido').click(function(){

            let accion = $(this).value();

            if(accion == 'cancelar'){
                
                $('#staticBackdrop').find('.modal-body').html('<h5>Estamos actualizando el estado del pedido</h5>');
                $("#staticBackdrop").modal("show");
                
                $.get('{{ route('pedidos_pendientes.cancelar') }}',{},function (data) {
                    window.location.href = '{{ route('pedidos_pendientes.index') }}';
                });
            }

        })

    </script>
@endpush
