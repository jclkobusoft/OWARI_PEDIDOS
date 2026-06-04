@extends('layouts.app')
 
@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Pedidos Especiales</div>
            <div class="card-body">
                <form action="{{ route('pedidos_especiales.reporte_pendientes') }}" method="get" class="row g-2 align-items-end mb-4">
                    <div class="col-auto">
                        <label for="mes" class="form-label mb-1"><b>Reporte de partidas pendientes de surtir</b></label>
                        <input type="month" id="mes" name="mes" class="form-control form-control-sm" value="{{ date('Y-m') }}" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-spreadsheet"></i>&nbsp;Descargar CSV
                        </button>
                    </div>
                </form>
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
@endsection
 
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $('body').on('click', '.eliminar_registro', function () {

            if(confirm('¿Estas segur@ que deses eliminar el usuario?')){
                var pedido = $(this).data('id');
                $.post('{{ route('pedidos_especiales.eliminar') }}',{ pedido: pedido, _method:'DELETE', _token: '{{ csrf_token() }}' } , function(data){
                    data = $.parseJSON(data);
                    if(data.code){
                        alert(data.mensaje);
                        location.reload();
                    }
                }).fail(function(){
                    alert('Ocurrio un error, intentalo mas tarde.');
                });
            } 
        });
    </script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush