<?php

namespace App\Services;

/**
 * FASE 1 migracion-tienda: puente de autenticacion contra SOMA.
 *
 * SOMA es el dueño de las credenciales de la tienda (clientes_accesos); esta
 * app valida el login/reset alla via api/tienda/* (X-API-Key). Todos los
 * metodos regresan el JSON decodificado, o NULL si SOMA no respondio — el
 * caller decide el fallback local (la tienda no se cae si SOMA esta caido).
 */
class SomaTiendaAuth
{
    private static function post(string $ruta, array $datos): ?array
    {
        $url = rtrim(config('services.somma.api_url'), '/') . $ruta;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($datos),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . config('services.somma.api_key'),
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $respuesta = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($respuesta === false || $http !== 200) {
            \Log::warning("SomaTiendaAuth {$ruta}: sin respuesta (HTTP {$http})");
            return null;
        }
        $json = json_decode($respuesta, true);
        return is_array($json) ? $json : null;
    }

    public static function login(string $email, string $password): ?array
    {
        return self::post('/api/tienda/login', ['email' => $email, 'password' => $password]);
    }

    public static function passwordSolicitar(string $email): ?array
    {
        return self::post('/api/tienda/password/solicitar', ['email' => $email]);
    }

    public static function passwordRestablecer(string $email, string $token, string $password): ?array
    {
        return self::post('/api/tienda/password/restablecer', [
            'email' => $email, 'token' => $token, 'password' => $password,
        ]);
    }

    public static function registroProspecto(array $datos): ?array
    {
        return self::post('/api/tienda/registro-prospecto', $datos);
    }

    public static function registrarCuenta(array $datos): ?array
    {
        return self::post('/api/tienda/registrar-cuenta', $datos);
    }
}
