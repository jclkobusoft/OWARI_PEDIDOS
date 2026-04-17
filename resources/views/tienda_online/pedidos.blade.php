@extends('tienda_online.base.base')
@section('contenido')
    <section class="my-account-area ptb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class=" mb-30">

                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home"
                                    aria-selected="true">
                                    <h2>Mis pedidos en linea</h2>
                                </button>
                                <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile"
                                    aria-selected="false">
                                    <h2>Mis pedidos especiales</h2>
                                </button>
                                <button class="nav-link" id="nav-mostra-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-mostra" type="button" role="tab" aria-controls="nav-mostra"
                                    aria-selected="false">
                                    <h2>Mis pedidos de mostrador</h2>
                                </button>

                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                aria-labelledby="nav-home-tab">
                                <small>Da click en el pedido para ver mas detalles.</small>
                                <div class="table-responsive" style="margin-top: 20px;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>

                                                <td>Pedido</td>
                                                <td>Estado</td>
                                                <td>Fecha de creación</td>
                                                <td>Partidas</td>
                                                <td>Gran total</td>
                                                <td>PDF</td>
                                                <td>Factura(s)</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pedidos as $pedido)
                                                @php $esSyd = !empty($pedido->es_syd); @endphp
                                                <tr>
                                                    <td class="{{ $esSyd ? 'enlace_pedido_especial' : 'enlace_pedido' }}" data-pedido="{{ $pedido->id }}">
                                                        <b>{{ $esSyd ? ('EN PROCESO') : $pedido->pedido_sae }}</b></td>
                                                    <td class="{{ $esSyd ? 'enlace_pedido_especial' : 'enlace_pedido' }}" data-pedido="{{ $pedido->id }}">
                                                        {{ $esSyd ? 'EN PROCESO' : $pedido->estado }}</td>
                                                    <td class="{{ $esSyd ? 'enlace_pedido_especial' : 'enlace_pedido' }}" data-pedido="{{ $pedido->id }}">
                                                        {{ \Carbon::createFromFormat('Y-m-d H:i:s', $pedido->created_at)->format('d/m/Y h:i A') }}
                                                    </td>
                                                    <td class="{{ $esSyd ? 'enlace_pedido_especial' : 'enlace_pedido' }}" data-pedido="{{ $pedido->id }}">
                                                        {{ count($pedido->partidas) }}</td>
                                                    <td class="{{ $esSyd ? 'enlace_pedido_especial' : 'enlace_pedido' }}" data-pedido="{{ $pedido->id }}"
                                                        align="right">${{ number_format($pedido->gran_total, 2, '.', ',') }}
                                                    </td>
                                                    <td align="center">
                                                        @if(!$esSyd)
                                                            <button class="ver_pdf btn-primary"
                                                                style="color:white; background-color:rgb(43,57,145);"
                                                                data-pedido="{{ $pedido->id }}">Ver pedido PDF</button>
                                                        @else
                                                            <span class="text-muted" style="font-size:12px;">En proceso</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$esSyd)
                                                            <button class="revisar_factura btn-primary"
                                                                style="color:white; background-color:#d31531;"
                                                                data-pedido="{{ $pedido->pedido_sae }}">Revisar factura(s)</button>
                                                        @else
                                                            <span class="text-muted" style="font-size:12px;">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                <small>Da click en el pedido para ver mas detalles.</small>
                                <div class="table-responsive" style="margin-top: 20px;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>

                                                <td>Especial</td>
                                                <td>Fecha de creación</td>
                                                <td>Partidas</td>
                                                <td>Gran total aprox.</td>
                                                <td>Pedidos finales -- Facturas</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pedidos_especiales as $pedido)
                                                <tr>
                                                    <td class="enlace_pedido_especial" data-pedido="{{ $pedido->id }}">
                                                        <b>{{ $pedido->id }}</b></td>
                                                    <td class="enlace_pedido_especial" data-pedido="{{ $pedido->id }}">
                                                        {{ \Carbon::createFromFormat('Y-m-d H:i:s', $pedido->created_at)->format('d/m/Y h:i A') }}
                                                    </td>
                                                    <td class="enlace_pedido_especial" data-pedido="{{ $pedido->id }}">
                                                        {{ count($pedido->partidas) }}</td>
                                                    <td class="enlace_pedido_especial" data-pedido="{{ $pedido->id }}"
                                                        align="right">${{ number_format($pedido->gran_total, 2, '.', ',') }}
                                                    </td>
                                                    <td class="enlace_pedido_especial" data-pedido="{{ $pedido->id }}"
                                                        align="right">
                                                        @foreach($pedido->generados as $generado)
                                                    
                                                        <button class="revisar_factura btn-primary"
                                                            style="color:white; background-color:#d31531; display:block; font-size:12px;margin-top:10px"
                                                            data-pedido="{{ $generado->pedido_sae }}">{{$generado->pedido_sae}} -- Revisar factura(s)</button>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                                <div class="tab-pane fade" id="nav-mostra" role="tabpanel" aria-labelledby="nav-home-tab">
                                    <small>Da click en el pedido para ver mas detalles.</small>
                                    <div class="table-responsive" style="margin-top: 20px;">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>

                                                    <td>Pedido</td>
                                                    <td>Fecha de creación</td>
                                                    <td>Gran total</td>
                                                    <td>Factura(s)</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($pedidos_mostrador)
                                                    @foreach ($pedidos_mostrador as $key => $pedido)
                                                        <tr>
                                                            <td><b>{{ $pedido['CVE_DOC'] }}</b></td>
                                                            <td>{{ \Carbon::createFromFormat('Y-m-d H:i:s', $pedido['FECHA_DOC'])->format('d/m/Y') }}
                                                            </td>
                                                            <td align="right">
                                                                ${{ number_format($pedido['CAN_TOT'], 2, '.', ',') }}</td>
                                                            <td>
                                                                <button class="revisar_factura btn-primary"
                                                            style="color:white; background-color:#d31531;"
                                                            data-pedido="{{ $pedido['CVE_DOC'] }}">Revisar factura(s)</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                </div>





                        </div>
                    </div>

    </section>
@endsection
@section('css')
    <style type="text/css">
        .login-form {
            max-width: 100%;
        }

        thead td {
            font-weight: bold;
        }

        table.table-bordered tr:nth-child(even) {
            background-color: transparent
        }

        table.table-bordered tr:nth-child(odd) {
            background-color: #f9f7fc
        }

        table tbody tr:hover {
            background-color: #d31531 !important;
            color: white;
            cursor: pointer;
            z-index: 5;
        }

        .ver_pdf {
            z-index: 10;
        }
    </style>
@endsection
@section('js')
    <script>
        $('.enlace_pedido').click(function(e) {
            window.location = "{{ route('tienda_online.detalle_pedido') }}?q=" + $(this).data('pedido');
        });
        $('.enlace_pedido_especial').click(function(e) {
            window.location = "{{ route('tienda_online.detalle_pedido_especial') }}?q=" + $(this).data('pedido');
        });

        $(".ver_pdf").click(function(event) {
            event.preventDefault();
            /* Act on the event */
            $.get('{{ route('tienda_online.generar_pdf') }}', {
                id_pedido: $(this).data('pedido'),
                nombre: ""
            }, function(data) {
                /*optional stuff to do after success */
                var data = $.parseJSON(data);
                if (data.code) {
                    window.open('/pdfs/pedidos/' + data.archivo);
                } else {
                    alert("Ocurrio un error, intentalo nuevamente mas tarde")
                }
            });
        });


       

        $(document).on('click', '.revisar_factura', function (e) {
            const pedido = $(this).data('pedido');
            window.location.href = "{{ route('tienda_online.factura_sae') }}?id_pedido=" + encodeURIComponent(pedido);
        });


    </script>
@endsection
