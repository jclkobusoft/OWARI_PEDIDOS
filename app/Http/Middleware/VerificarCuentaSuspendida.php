<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Si el cliente autenticado tiene users.cuenta_suspendida = true, lo
 * redirigimos SIEMPRE a la pantalla informativa con el banner. Literalmente
 * no puede hacer nada en la tienda en linea hasta que ventas lo reactive
 * manualmente desde /clientes/{id}/editar.
 *
 * Las unicas rutas permitidas mientras esta suspendido son la propia pantalla
 * informativa y el logout, para que pueda salir si lo desea.
 */
class VerificarCuentaSuspendida
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->cliente || !$user->cuenta_suspendida) {
            return $next($request);
        }

        $rutasPermitidas = [
            'tienda_online.cuenta_suspendida',
            'tienda_online.logout',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $rutasPermitidas, true)) {
            return $next($request);
        }

        return redirect()->route('tienda_online.cuenta_suspendida');
    }
}
