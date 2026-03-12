@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 offset-md-3 offset-lg-3">
                        <div class="login-form mb-30">
                            <h2>¡Tu pedido fue guardado correctamente!</h2>
                            

                            @if(isset(\Auth::user()->clienteData))
                                @if(\Auth::user()->clienteData->tiendita)
                                    <p>Nuestro equipo a recibido su pedido y comenzara a ser despachado lo mas pronto posible.<br><br>
                                    <p>Puedes descagar o enviar por correo electronico el pedido a tu cliente:</p>
                                    <div class="form-group">
                                        <label>Nombre del cliente</label>
                                        <input type="text" class="form-control" placeholder="Nombre del cliente" name="nombre_cliente">
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>E-mail</label>
                                        <input type="email" class="form-control" placeholder="Correo electronico" name="email">
                                    </div>
                                    <a id="ver_pdf" class="default-btn mt-3">Ver pedido PDF</a>
                                    <a id="enviar_email" class="default-btn mt-3">Enviar al cliente</a>

                                     <a href="{{ route('tienda_online.dashboard') }}" class="default-btn mt-2">Terminar</a>
                                @else
                                    <p>Nuestro equipo a recibido su pedido y comenzara a ser despachado lo mas pronto posible.<br><br>
                                        Para ver el estado de tu pedido, puedes entrar a la seccion de <b>"Mis pedidos"</b> y seleccionar el ultimo pedido que acabas de realizar. 
                                    </p>
                                    <a href="{{ route('tienda_online.pedidos') }}" class="default-btn">Ir a mis pedidos</a>
                                @endif
                             @else
                                    <p>Nuestro equipo a recibido su pedido y comenzara a ser despachado lo mas pronto posible.<br><br>
                                        Para ver el estado de tu pedido, puedes entrar a la seccion de <b>"Mis pedidos"</b> y seleccionar el ultimo pedido que acabas de realizar. 
                                    </p>
                                    <a href="{{ route('tienda_online.pedidos') }}" class="default-btn">Ir a mis pedidos</a>
                            @endif
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection
@section('css')

@endsection
@section('js')
<script>
    
    $("#ver_pdf").click(function(event) {
        /* Act on the event */
        $.get('{{ route('tienda_online.generar_pdf') }}', {id_pedido:'{{ $id_pedido }}',nombre: $('input[name="nombre_cliente"]').val() } ,function(data) {
            /*optional stuff to do after success */
            var data = $.parseJSON(data);
            if(data.code){
                window.open('/pdfs/pedidos/'+data.archivo);
            }
            else{
                alert("Ocurrio un error, intentalo nuevamente mas tarde")
            }
        });
    });


    $("#enviar_email").click(function(event) {
        /* Act on the event */
        if($('input[name="email"]').val() == ""){
            alert("Inserta un email valido");
            return false;
        }
        $.get('{{ route('tienda_online.generar_pdf') }}', {id_pedido:'{{ $id_pedido }}',nombre: $('input[name="nombre_cliente"]').val(), email: $('input[name="email"]').val() } ,function(data) {
            /*optional stuff to do after success */
            var data = $.parseJSON(data);
            if(data.code){
                alert('El correo fue enviado correctamente.')
            }
            else{
                alert("Ocurrio un error, intentalo nuevamente mas tarde.")
            }
        });
    });

</script>
@endsection