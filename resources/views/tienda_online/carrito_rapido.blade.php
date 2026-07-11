@extends('tienda_online.base.base')
@section('contenido')
<section class="shop-area pb-5 pt-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">

                <h4 class="mb-1">Carrito rápido</h4>
                <p class="text-muted" style="font-size:13px;">
                    Vista rápida de tu pedido (clave y cantidad), sin esperar el análisis de precios.
                    Descárgalo en Excel y compártelo con telemarketing para que lo capturen.
                </p>

                @if (Session::has('status'))
                    <div class="contact-info-box" style="background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32;">
                        {{ Session::get('status') }}
                    </div>
                @endif

                @if(count($items) > 0)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('tienda_online.carrito_rapido_excel') }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-excel"></i>&nbsp;Descargar Excel
                        </a>
                        <form action="{{ route('tienda_online.carrito_rapido_vaciar') }}" method="post"
                              onsubmit="return confirm('¿Seguro que quieres vaciar todo el carrito? Esta acción no se puede deshacer.');"
                              class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i>&nbsp;Vaciar carrito
                            </button>
                        </form>
                        <a href="{{ route('tienda_online.carrito') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-cart"></i>&nbsp;Ir al carrito completo
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>Clave</th>
                                    <th style="width:120px;">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $i => $it)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $it['clave'] }}</td>
                                        <td>{{ $it['cantidad'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total de piezas</th>
                                    <th>{{ $total_piezas }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <h5 class="text-muted">Tu carrito está vacío.</h5>
                        <a href="{{ route('tienda_online.productos') }}?q=&p=1" class="btn btn-primary mt-2">
                            Ver productos
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</section>
@endsection
