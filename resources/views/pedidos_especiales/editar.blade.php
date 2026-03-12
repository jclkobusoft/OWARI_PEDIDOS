@extends('layouts.app')
@section('content')
<script>
    var generar_pedido = false;
    var partidas_finales = [];
    var partidas = [];
</script>
<div class="container">
    <div class="card">
        <div class="card-header">Pedido especial original</div>
        <div class="card-body">
           <h5>Pedido <strong>{{$pedido->id}}</strong></h5>
            <h5>Cliente <strong>{{$pedido->cliente}}</strong></h5>
           <h5>Creado el: <strong>{{  \Carbon::createFromFormat('Y-m-d H:i:s',$pedido->created_at)->format('d/m/Y h:i A')  }}</strong></h5>
                                <div class="table-responsive" style="margin-top: 20px;">
                                <h4>Partidas</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>Clave</td>
                                            <td>Cantidad</td>
                                            <td>Stock</td>
                                            <td>Surtido</td>
                                            <td>Precio unitario</td>                                            
                                            <td>Total partida</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pedido->partidas as $partida) 
                                                <tr id="check_{{ $partida->id }}">
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
                                                        <img src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image" height="100px">
                                                    @else
                                                        <img src="{{ 'https://owari.com.mx/img/sin-foto.jpg' }}" alt="Product Image" width="150px">
                                                    @endif
                                                    </td>
                                                    <td>{{ $partida->clave }}</td>
                                                    <td>{{ number_format($partida->cantidad,0,'.',',') }}</td>
                                                    <td id="stock_{{ $partida->id }}"></td>
                                                    <td>{{ number_format($partida->surtido,0,'.',',') }}</td>
                                                    <td>$ {{ number_format($partida->precio_unitario,2,'.',',') }}</td>
                                                    <td align="right">
                                                        ${{ number_format($partida->gran_total,2,'.',',') }}<br>
                                                        <div class="palomita_{{ $partida->id }}" style="display:none;">
                                                            ✅ Partida lista
                                                        </div>
                                                    </td>
                                            </tr>
                                             <script>
                                                    setTimeout(() => {
                                                        $.get("https://sistemasowari.com:8443/catalowari/api/producto-existencia-especial?clave={{ urlencode($partida->clave) }}",{},
                                                                function (data, textStatus, jqXHR) {
                                                                    data = JSON.parse(data);
                                                                    var cantidad_{{ $partida->id }} = {{ $partida->cantidad }};
                                                                    var surtido_{{ $partida->id }} = {{ $partida->surtido }};
                                                                    var existencia = parseInt(data.existencia);
                                                                    $('#stock_{{ $partida->id }}').text("").text(existencia);

                                                                    if(surtido_{{ $partida->id }} >= cantidad_{{ $partida->id }}){
                                                                        $('#check_{{ $partida->id }} td').css('background-color','#CEEFBF');
                                                                    }
                                                                    else{
                                                                        if(existencia > 0){
                                                                            $('#check_{{ $partida->id }} td').css('background-color','#EFBFBF');
                                                                             $("#generar_pedido").show();

                                                                              var cantidad_pendiente = {{ $partida->cantidad - $partida->surtido }};

                                                                              var cantidad_surtir = 0;

                                                                            if(cantidad_pendiente <= existencia)
                                                                                cantidad_surtir = cantidad_pendiente;
                                                                            else
                                                                                cantidad_surtir = existencia;


                                                                            analisis_precios('{{ $pedido->cliente }}','{{ $partida->clave}}',cantidad_surtir,'{{ $partida->id }}');
                                                                        }
                                                                    }

                                                            

                                                                }
                                                            );
                                                    }, 1500);
                                                </script>
                                        @endforeach
                                    </tbody>
                                </table>
                        </div>
                        <div style="width: 100%;text-align: right">
                            <h3>Gran total: <strong>$ {{ number_format($pedido->gran_total,2,'.',',') }}</strong></h3>
                        </div>
                        <div style="width: 100%;text-align: right; display:none;" id="generar_pedido">
                            <div class="col-lg-4 col-md-4 offset-lg-8 col-12 offset-md-8">
                        <div class="cart-totals">
                            <div class="mb-3" >
                                  <label for="fecha_recoge" class="form-label" id="texto_fecha_recoge">Pasare por mi pedido a las:</label>
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
                                  <label for="forma_pago" class="form-label">Forma de pago:</label>
                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" id="forma_pago">
                                        <option value="01">01 Efectivo</option>
                                        <option value="02">02 Cheque nominativo</option>
                                        <option value="03">03 Transferencia electrónica de fondos</option>
                                        <option value="04">04 Tarjeta de crédito</option>
                                        <option value="05">05 Monedero electrónico</option>
                                        <option value="06">06 Dinero electrónico</option>
                                        <option value="08">08 Vales de despensa</option>
                                        <option value="12">12 Dación en pago</option>
                                        <option value="13">13 Pago por subrogación</option>
                                        <option value="14">14 Pago por consignación</option>
                                        <option value="15">15 Condonación</option>
                                        <option value="17">17 Compensación</option>
                                        <option value="23">23 Novación</option>
                                        <option value="24">24 Confusión</option>
                                        <option value="25">25 Remisión de deuda</option>
                                        <option value="26">26 Prescripción o caducidad</option>
                                        <option value="27">27 A satisfacción del acreedor</option>
                                        <option value="28">28 Tarjeta de débito</option>
                                        <option value="29">29 Tarjeta de servicios</option>
                                        <option value="30">30 Aplicación de anticipos</option>
                                        <option value="31">31 Intermediario pagos</option>
                                        <option value="99">99 Por definir</option>
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
                        </div>
                    </div>
                            <small class="text-danger">Todos las partidas en rojo van a crear pedido para surtir</small><br>
                            <button id="crear_pedido" class="btn btn-sm btn-danger">Crear pedido</button>
                        </div>
           
        </div>
    </div>
</div>
@if(count($pedidos_sae) > 0)
<div class="container">
    <h3 class="mt-50" style="margin:40px 0 20px">¡Ya tenemos tus productos!</h3>
</div>
@endif
@foreach($pedidos_sae as $pedido_sae)
     <section class="my-account-area">
            <div class="container">

             <div class="card">
                    <div class="card-header">Pedidos finales</div>
                        <div class="card-body">

                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="mb-30">



                                <h5>Pedido: <b>{{ $pedido_sae->pedido_sae }}</b></h5>
                                <h5>Creado el: {{  \Carbon::createFromFormat('Y-m-d H:i:s',$pedido_sae->created_at)->format('d/m/Y h:i A')  }}</h5>
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
                                        @foreach ($pedido_sae->partidas as $partida) 
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
                            <h3>Gran total: $ {{ number_format($pedido_sae->gran_total,2,'.',',') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
         </div>
     </div>
 </div>
        </section>
@endforeach




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
                <a class="default-btn" href="javascript:location.reload();">Cerrar</a>
              </div>
            </div>
          </div>
        </div>
@endsection
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>


        $.get('https://sistemasowari.com:8443/catalowari/api/cliente', { cliente: '{{ $pedido->cliente }}'} ,function(data) {
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

                $("#forma_pago").val(data.cliente.FORMADEPAGOSAT);
                $("#uso_cfdi").val(data.cliente.USO_CFDI);
                $("#metodo_pago").val(data.cliente.METODODEPAGO).trigger('change');

            });

        $('#formulario').submit(function(e){
            $('button[type="submit"]').attr('disabled', 'disabled');
            return true;
        });

        $("#crear_pedido").click(function(event) {
            guardar_pedido();
        });

        const analisis_precios = ($cliente,$clave,$cantidad,$idpaloma) => {

            $.get("https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto_especial",
                { cliente: $cliente, clave: $clave, tipo: $("input:radio[name ='tipo_pedido']:checked").val() },
                function (data) {
                    //console.log(data);
                    var obj = data;
                    if(data.code == 0 ){
                        alert(data.mensaje);
                        return false;
                    }


                    producto_partida = obj;
                    if(obj.cliente == "N/A")
                        obj.precio_publico = $precio_normal;

                    //analisis para saber que politiva le toca
                    var precio = obj.precio_publico;
                    var precio_iva = obj.precio_iva;
                    var cantidad = $cantidad;
                    //console.log("TAMANO:" + obj.descuentos.length);
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
                                        continue;
                                    }
                                }
                            }
                        }
                    } else {precio = obj.descuentos[0].precio_lista;precio_iva = obj.descuentos[0].precio_iva; }

                    if(obj.cliente=='N/A')
                        precio = $precio_normal

                     partidas_finales.push(
                        {
                            "codigo" : $clave,
                            "descripcion" : '',
                            "cantidad" : cantidad,
                            "precio" : parseFloat(precio).toFixed(2),
                            "precio_iva" : parseFloat(precio_iva).toFixed(2),
                            "total" : (cantidad*precio).toFixed(2)
                        }
                    );
                     partidas.push(producto_partida);
                     $('.palomita_'+$idpaloma).show();


                     console.log(partidas_finales);
                     console.log(partidas);
                });
        }



        function guardar_pedido(){
        
            if($('#fecha_recoge').val() == ""){
                alert('Selecciona la fecha de entrega del pedido');
                return false;
            }

        var variables = {
            usuario: '{{ \Auth::user()->name }}',
            cliente: '{{ $pedido->cliente }}',
            partidas: partidas_finales,
            partidas_detalle: partidas,
            partidas_especiales: [],
            partidas_especiales_detalle: [],
            'su_pedido': 'Pedido Online',
            'empresa_seleccionada' : 1,
            'tipo': 'factura',
            'pedido_sae' : 0,
            '_token' : "{{ csrf_token() }}",
            'fecha_recoge':$("#fecha_recoge").val(),
            'metodo_pago':$("#metodo_pago").val(),
            'forma_pago':$("#forma_pago").val(),
            'uso_cfdi':$("#uso_cfdi").val(),
            'pedido_especial' : {{ $pedido->id }}
        };

        $('#staticBackdrop').modal('show');

        console.log(variables);
               
        
        $.post("https://sistemasowari.com:8443/catalowari/api/guardar_web", variables,
            function (data, textStatus, jqXHR) {
                if(data.code){
                    variables.pedido_sae = data.pedido;

                    $.post("{{ route('pedidos_especiales_sae.guardar') }}", variables,
                        function (data, textStatus, jqXHR) {
                            if(data.code){
                                location.reload();
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
        
    }


    </script>
@endpush