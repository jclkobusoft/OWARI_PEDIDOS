@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    
                    <div class="col-lg-6 offset-md-3 col-md-6 col-12">
                        <div class="login-form mb-30" style="width: 100%;">
                            <h2>Cuidado, tienes tu sesión como usuario de pedidos</h2>
                            <p>{{ \Auth::user()->name. " -- ".\Auth::user()->email}}<br>Cierra tu sesion e inicia sesion como cliente.</p><br><br>
                            <a href="{{ route('tienda_online.logout') }}" class="btn-default">Cerrar sesión</a>
                        </div>
                    </div>
                     
                    </div>
                </div>
            </div>
        </section>
@endsection
@section('css')

@endsection
@section('js')

@endsection