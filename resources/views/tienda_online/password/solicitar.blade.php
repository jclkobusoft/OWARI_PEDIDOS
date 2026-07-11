@extends('tienda_online.base.base')
@section('contenido')
<section class="my-account-area ptb-100c">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center pb-70">
                <img src="{{'https://owari.com.mx/upload/gral/'.$general->logotipo_general}}" width="250px" alt="image">
            </div>
            <div class="col-lg-6 col-sm-12 offset-lg-3 offset-md-3 col-md-6">
                <div class="login-form mb-30">
                    <h2>Recuperar contraseña</h2>
                    <p>Escribe el correo con el que te registraste y te enviaremos un enlace para restablecer tu contraseña.</p>

                    @if (Session::has('status'))
                        <div class="contact-info-box" style="background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32;">
                            {{ Session::get('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="contact-info-box">
                            <strong>¡Error!</strong>&nbsp;{{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('tienda_online.password.email') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-control" placeholder="tucorreo@ejemplo.com" name="email" value="{{ old('email') }}" required>
                        </div>
                        <button type="submit">Enviar enlace</button>
                    </form>
                    <a href="{{ route('tienda_online.login') }}" style="width:100%;text-align:center;margin-top:40px;display:block;">Volver a iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</section>
@stop
