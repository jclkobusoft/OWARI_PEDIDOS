@extends('tienda_online.base.base')
@section('contenido')
<section class="shop-area pb-5 pt-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">

                <h4 class="mb-1">Recuperar carrito anterior</h4>
                <p class="text-muted" style="font-size:13px;">
                    Estos son los productos que tenías guardados antes de la actualización de tu carrito.
                    Da clic en <b>Agregar a mi carrito</b> para pasarlos a tu carrito actual y no perderlos.
                </p>

                @if (Session::has('status'))
                    <div class="contact-info-box" style="background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32;">
                        {{ Session::get('status') }}
                    </div>
                @endif

                @if(count($items) > 0)
                    <form action="{{ route('tienda_online.carrito_anterior_importar') }}" method="post" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-plus"></i>&nbsp;Agregar a mi carrito
                        </button>
                        <a href="{{ route('tienda_online.carrito') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-cart"></i>&nbsp;Ir a mi carrito
                        </a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Clave</th>
                                    <th style="width:110px;">Cantidad</th>
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
                        <h5 class="text-muted">No hay un carrito anterior por recuperar.</h5>
                        <a href="{{ route('tienda_online.carrito') }}" class="btn btn-primary mt-2">Ir a mi carrito</a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</section>
@endsection
