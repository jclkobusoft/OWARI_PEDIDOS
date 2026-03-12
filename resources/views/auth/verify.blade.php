@php
    $titulo="Verificar correo electronico";
    $general = App\Models\DatosGenerales::find(1);
@endphp

@extends('tienda_online.base.base')
@section('contenido')
<section class="my-account-area ptb-100c">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center pb-70">
                    	 <img src="{{'https://owari.com.mx/upload/gral/general-Owari_007.png'}}" width="250px" alt="image">
                    </div>
                    <div class="col-lg-6 col-sm-12 offset-lg-3 offset-md-3 col-md-6">
                        <div class="login-form mb-30">
                            <h2>Verifica tu correo eletronico</h2>
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('Un nuevo correo electronico fue enviado para que validez tu cuenta, valida en tu bandeja de entrada o en tu correo de SPAM.') }}
                                </div>
                            @else
                                <div class="contact-info-box">
                                    Tu cuenta requiere ser confirmada antes de poder darte acceso, revisa tu correo eletronico o solicita un nuevo correo de validación.
                                </div>
                            @endif
                            @if (!session('resent'))
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" style="border: none;
                                margin-top: 25px;
                                padding: 15px 30px;
                                width: 100%;
                                border-radius: 5px;
                                cursor: pointer;
                                background-color: #d31531;
                                color: #ffffff;
                                -webkit-transition: 0.5s;
                                transition: 0.5s;">{{ __('Presiona aqui para enviar un nuevo correo') }}</button>.
                            </form>
                            @endif
                        
                            
                        </div>
                    </div>

                   
                </div>
            </div>
        </section>
@stop
