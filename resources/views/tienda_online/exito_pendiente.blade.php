@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 offset-md-3 offset-lg-3">
                        <div class="login-form mb-30">
                            <h2>¡Tu pedido fue guardado correctamente!</h2>
                            <h3>{!! $titulo !!}</h3>
                            <a href="{{ route('tienda_online.dashboard') }}" class="default-btn">Ir a inicio</a>
                            
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