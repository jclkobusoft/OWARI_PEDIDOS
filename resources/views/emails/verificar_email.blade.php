@php
    $appName = 'Owari Autopartes';
    $logo = 'https://owari.com.mx/upload/gral/general-Owari_007.png';
    $from = 'tiendaonline@owari.com.mx';
    $expire = config('auth.verification.expire', 60);
@endphp

@component('mail::message')
    @slot('header')
        @component('mail::header', ['url' => 'https://owari.com.mx'])
            <img src="{{ $logo }}" alt="{{ $appName }}" height="48" style="height:48px; max-height:48px;">
        @endcomponent
    @endslot
    

# Confirma tu correo

Hola{{ isset($user->name) ? ' '.$user->name : '' }},

Gracias por registrarte en **{{ $appName }}**. Verifica tu correo para activar tu cuenta.

@component('mail::button', ['url' => $url])
Verificar correo
@endcomponent

El enlace expira en **{{ $expire }} minutos**. Si necesitas otro, solicita uno nuevo desde la página de verificación.

@component('mail::panel')
**¿No fuiste tú?** Ignora este mensaje. La cuenta no se activará sin confirmar el correo.
@endcomponent

Si tienes dudas, responde a este correo o escribe a **{{ $from }}**.

    @slot('subcopy')
        @component('mail::subcopy')
Si el botón no funciona, copia y pega esta URL en tu navegador:

{{ $url }}
        @endcomponent
    @endslot
@endcomponent
