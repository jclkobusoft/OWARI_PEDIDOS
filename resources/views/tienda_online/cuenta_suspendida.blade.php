@extends('tienda_online.base.base')
@section('contenido')
<section class="my-account-area ptb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1 col-sm-12">
                <div class="login-form mb-30" style="text-align:center;">
                    <div style="background:#fdecea; color:#a02020; border:1px solid #f5b6b6; padding:30px; border-radius:8px;">
                        <h2 style="color:#a02020; margin-bottom:20px;">Carrito suspendido</h2>
                        <p style="font-size:16px;">
                            Por falta de compras en linea, tu carrito ha sido <b>suspendido</b>.
                        </p>
                        <p style="font-size:16px;">
                            Favor de llamar a <b>ventas</b> para reactivarlo.
                        </p>
                        <p style="margin-top:30px;">
                            <a href="{{ route('tienda_online.logout') }}" style="display:inline-block; padding:10px 25px; background:#a02020; color:#fff; text-decoration:none; border-radius:4px;">
                                Cerrar sesion
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@stop
