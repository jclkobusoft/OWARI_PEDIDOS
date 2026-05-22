<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware para autenticar peticiones entrantes desde SOMA cloud hacia
 * los endpoints /api/qz/* de este proyecto (pedidos).
 *
 * SOMA cloud no puede llegar al MySQL de Quezada directamente (10.10.0.160
 * está en LAN privada). Este proyecto vive en la oficina y sí lo alcanza,
 * por lo que actúa como PUENTE: SOMA cloud envía HTTP con header X-API-Key
 * y este middleware lo valida contra config('services.soma_inbound.api_key').
 *
 * Es complementario al ApiKeyAuth de SOMA (que valida peticiones que vienen
 * DESDE este proyecto HACIA SOMA). Ambos comparten patrón pero la clave
 * es distinta — la maneja config aparte para poder rotarlas independiente.
 */
class SomaInboundApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $configured = config('services.soma_inbound.api_key');

        if (empty($configured)) {
            return response()->json([
                'response' => 0,
                'message'  => 'SOMA_INBOUND_API_KEY no configurada en este servidor',
            ], 500);
        }

        $apiKey = $request->header('X-API-Key');
        if (!$apiKey) {
            return response()->json([
                'response' => 0,
                'message'  => 'Header X-API-Key requerido',
            ], 401);
        }

        // Comparación constant-time para evitar timing attacks.
        if (!hash_equals((string) $configured, (string) $apiKey)) {
            return response()->json([
                'response' => 0,
                'message'  => 'API key inválida',
            ], 401);
        }

        return $next($request);
    }
}
