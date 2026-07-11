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
                    <h2>Restablecer contraseña</h2>

                    @if ($errors->any())
                        <div class="contact-info-box">
                            <strong>¡Error!</strong>&nbsp;{{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('tienda_online.password.update') }}" method="post">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $email) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Nueva contraseña</label>
                            <input type="password" class="form-control" placeholder="Nueva contraseña" name="password" required>
                        </div>

                        <div class="form-group">
                            <label>Confirmar contraseña</label>
                            <input type="password" class="form-control" placeholder="Repite la contraseña" name="password_confirmation" required>
                        </div>

                        <button type="submit">Guardar contraseña</button>
                    </form>
                    <a href="{{ route('tienda_online.login') }}" style="width:100%;text-align:center;margin-top:40px;display:block;">Volver a iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</section>
@stop
