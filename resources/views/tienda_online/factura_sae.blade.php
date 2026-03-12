@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="mb-30">
                    <h2>Pedido: {{ $id_pedido }}</h2>
                    
                    <h4>{{ $mensaje }}</h4>
                </div>
                @foreach($facturas as $key => $value)
                <div style="padding-top:30px">
                   
                            <h4>Factura: {{ $value['factura'] }}</h4>
                            <a class="btn btn-success" style="margin-bottom:20px" href="{{ route('cfdi.zip',$value['uuid']) }}">Descargar PDF + XML (.zip)</a>
                       
                            <iframe src="{{ route('ver_pdf', $value['uuid']) }}" width="100%" height="800" style="border:0"></iframe>
                  
                    
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
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