@extends('layouts.app')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Crear usuario</div>
        <div class="card-body">
             @if($errors->any())
            <div class="alert alert-danger" role="alert">
              <h4 class="alert-heading">Error!</h4>
              <p>{{$errors->first()}}</p>
            </div>
            @endif

            @if(\Session('success'))
            <div class="alert alert-success" role="alert">
                  <h4 class="alert-heading">Bien!</h4>
                  <p>{{\Session('success')}}</p>
                </div>
            @endif
            {{ Form::model($cliente,['route' => ['clientes.actualizar',$cliente->id], 'method' => 'put','id' => 'formulario']) }}
                @include('clientes.formulario')
                <div class="row">
                    <div class="col-md-6 d-flex justify-content-end">
                         <button type="submit" class="btn btn-dark">Guardar cambios</button>
                    </div>
                </div>

            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')
     <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>

       <script>
       
            var  url = "https://sistemasowari.com:8443/catalowari/api/clientes_factura";

            $.get(
            url,{vendedor: ''},
            function (data) {
                var obj = jQuery.parseJSON(data);
                clientes = obj;
                
                $("#clave_cliente").html('<option value="-1">Selecciona o busca un cliente</option>')
                $.each(obj, function (i, val) {
                    $("#clave_cliente").append(
                        '<option value="' +
                        val.clave +
                        '">' +
                        val.clave +
                        " " +
                        val.nombre +
                        "</option>"
                    );
                });
                $("#clave_cliente").val('{{ $cliente->clave_cliente }}')
                    .chosen({ no_results_text: "Oops, no hay resultados!" })
                    .trigger('chosen:updated').trigger('chosen:activate');
            }
        );
    </script>
    <script>

        $('#formulario').submit(function(e){
            $('button[type="submit"]').attr('disabled', 'disabled');
            return true;
        });
    </script>
@endpush