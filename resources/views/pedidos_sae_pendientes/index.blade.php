@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-hourglass-split"></i>
                    Pedidos SAE pendientes
                </span>
                <small class="text-muted">
                    Cola de pedidos que el carrito intento insertar en SAE pero fallaron tras 5 reintentos. El cron los reintenta cada 5 min.
                </small>
            </div>
            <div class="card-body">

                {{-- Chips de conteos por estado --}}
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <a href="{{ route('pedidos_sae_pendientes.index') }}" class="btn btn-outline-secondary btn-sm">
                        Todos
                    </a>
                    <a href="?estado_filtro=pendiente" class="btn btn-outline-warning btn-sm">
                        <span class="badge bg-warning text-dark">{{ $conteos['pendiente'] ?? 0 }}</span>
                        Pendiente
                    </a>
                    <a href="?estado_filtro=en_proceso" class="btn btn-outline-info btn-sm">
                        <span class="badge bg-info text-dark">{{ $conteos['en_proceso'] ?? 0 }}</span>
                        En proceso
                    </a>
                    <a href="?estado_filtro=completado" class="btn btn-outline-success btn-sm">
                        <span class="badge bg-success">{{ $conteos['completado'] ?? 0 }}</span>
                        Completado
                    </a>
                    <a href="?estado_filtro=fallido" class="btn btn-outline-danger btn-sm">
                        <span class="badge bg-danger">{{ $conteos['fallido'] ?? 0 }}</span>
                        Fallido
                    </a>
                </div>

                {{ $dataTable->table(['class' => 'table table-striped table-hover'], true) }}
            </div>
        </div>
    </div>

    {{-- Modal Detalle --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del pendiente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalle_contenido">
                        <p class="text-center text-muted">Cargando...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var modalDetalle = null;

        $(document).ready(function() {
            modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalle'));
        });

        // Ver detalle
        $('body').on('click', '.ver_detalle', function() {
            var id = $(this).data('id');
            $('#detalle_contenido').html('<p class="text-center text-muted">Cargando...</p>');
            modalDetalle.show();

            $.get('{{ url('/pedidos-sae-pendientes') }}/' + id + '/detalle', function(resp) {
                if (!resp.code) {
                    $('#detalle_contenido').html('<p class="text-danger">No se pudo cargar el detalle.</p>');
                    return;
                }
                var p = resp.pendiente;
                var html = '<table class="table table-sm">';
                html += '<tr><th width="180">ID</th><td>' + p.id + '</td></tr>';
                html += '<tr><th>Cliente</th><td>' + escapeHtml(p.cliente) + '</td></tr>';
                html += '<tr><th>Empresa</th><td>' + (p.empresa == 1 ? 'E01 Factura' : 'E03 Remision') + '</td></tr>';
                html += '<tr><th>Estado</th><td><span class="badge bg-secondary">' + p.estado.toUpperCase() + '</span></td></tr>';
                html += '<tr><th>Intentos</th><td>' + p.intentos + ' / 20</td></tr>';
                html += '<tr><th>Folio SAE</th><td>' + (p.folio_sae || '—') + '</td></tr>';
                html += '<tr><th>PedidoWeb id</th><td>' + (p.id_pedido_web || '—') + '</td></tr>';
                html += '<tr><th>Encolado</th><td>' + (p.created_at || '—') + '</td></tr>';
                html += '<tr><th>Completado</th><td>' + (p.completed_at || '—') + '</td></tr>';
                html += '</table>';

                if (p.ultimo_error) {
                    html += '<div class="mt-3"><h6 class="text-danger">Ultimo error</h6>';
                    html += '<pre class="bg-light p-2 small" style="white-space:pre-wrap;">' + escapeHtml(p.ultimo_error) + '</pre></div>';
                }

                html += '<div class="mt-3"><h6>Payload (lo que se envia a SAE)</h6>';
                html += '<pre class="bg-light p-2 small" style="white-space:pre-wrap;max-height:400px;overflow:auto;">' + escapeHtml(JSON.stringify(p.payload, null, 2)) + '</pre></div>';

                $('#detalle_contenido').html(html);
            }).fail(function() {
                $('#detalle_contenido').html('<p class="text-danger">Error de red al cargar el detalle.</p>');
            });
        });

        // Reintentar ahora
        $('body').on('click', '.reintentar_ahora', function() {
            if (!confirm('¿Reintentar este pedido en SAE ahora?')) return;
            var $btn = $(this);
            var id = $btn.data('id');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.post('{{ url('/pedidos-sae-pendientes') }}/' + id + '/reintentar',
                { _token: '{{ csrf_token() }}' },
                function(resp) {
                    if (resp.code) {
                        alert('Exito: ' + (resp.mensaje || 'OK') + (resp.folio ? '\nFolio SAE: ' + resp.folio : ''));
                    } else {
                        alert('No se logro: ' + (resp.mensaje || 'error desconocido'));
                    }
                    location.reload();
                }
            ).fail(function() {
                alert('Error de red. Reintenta mas tarde.');
                $btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i>');
            });
        });

        // Cancelar (marcar fallido)
        $('body').on('click', '.cancelar_pendiente', function() {
            if (!confirm('¿Marcar como fallido? Ya no se reintentara automaticamente.')) return;
            var id = $(this).data('id');

            $.post('{{ url('/pedidos-sae-pendientes') }}/' + id + '/cancelar',
                { _token: '{{ csrf_token() }}' },
                function(resp) {
                    if (resp.code) {
                        alert(resp.mensaje);
                    } else {
                        alert('Error: ' + (resp.mensaje || 'desconocido'));
                    }
                    location.reload();
                }
            ).fail(function() {
                alert('Error de red.');
            });
        });

        function escapeHtml(s) {
            if (s === null || s === undefined) return '';
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
