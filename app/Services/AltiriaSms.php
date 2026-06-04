<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Cliente minimo de la API REST de Altiria para envio de SMS.
 *
 * Credenciales en config/services.php (.env): ALTIRIA_URL, ALTIRIA_AUTH_TOKEN,
 * ALTIRIA_SENDER. El token es el valor que va despues de "Basic " en el header
 * Authorization.
 *
 * Doc del endpoint: POST https://api.altiria.com/api/rest/sms
 *   body: { "to": ["<numero>"], "from": "<sender>", "message": "<texto>" }
 *   200/202 = aceptado, 207 = multi-status (algunos aceptados), 400 = error.
 */
class AltiriaSms
{
    /**
     * Envia un SMS a un solo numero. Devuelve el resultado normalizado:
     *   ['ok' => bool, 'status' => int, 'error' => ?string, 'response' => mixed]
     *
     * `ok` es true solo si el transporte respondio 2xx y Altiria marco el
     * destinatario como accepted.
     */
    public function enviar(string $telefono, string $mensaje): array
    {
        $url    = config('services.altiria.url');
        $token  = config('services.altiria.token');
        $sender = config('services.altiria.sender');

        if (empty($token)) {
            return ['ok' => false, 'status' => 0, 'error' => 'ALTIRIA_AUTH_TOKEN no configurado', 'response' => null];
        }

        $numero = $this->normalizarTelefono($telefono);
        if ($numero === null) {
            return ['ok' => false, 'status' => 0, 'error' => 'Telefono invalido: ' . $telefono, 'response' => null];
        }

        $payload = json_encode([
            'to'      => [$numero],
            'from'    => $sender,
            'message' => $mensaje,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $token,
            ],
            CURLOPT_TIMEOUT => 20,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status === 0) {
            return ['ok' => false, 'status' => 0, 'error' => 'cURL: ' . $err, 'response' => null];
        }

        $data = json_decode($body, true);

        // Error de nivel API (400): { "error": { code, description } }
        if ($status >= 400) {
            $desc = $data['error']['description'] ?? substr((string) $body, 0, 200);
            return ['ok' => false, 'status' => $status, 'error' => 'Altiria: ' . $desc, 'response' => $data];
        }

        // 202 (todos aceptados) o 207 (multi-status): revisar el destinatario.
        $resultado = $data['result'][0] ?? null;
        $aceptado  = is_array($resultado) ? (bool) ($resultado['accepted'] ?? false) : false;

        if (!$aceptado) {
            $desc = $resultado['error']['description'] ?? 'destinatario no aceptado';
            return ['ok' => false, 'status' => $status, 'error' => 'Altiria: ' . $desc, 'response' => $data];
        }

        return ['ok' => true, 'status' => $status, 'error' => null, 'response' => $data];
    }

    /**
     * Normaliza un telefono a formato internacional sin "+". Asume Mexico:
     * un numero de 10 digitos se le antepone la lada 52. Si ya viene con lada
     * (mas de 10 digitos) se respeta tal cual. Devuelve null si no hay digitos.
     */
    public function normalizarTelefono(string $telefono): ?string
    {
        $digitos = preg_replace('/\D+/', '', $telefono);
        if ($digitos === '' || $digitos === null) {
            return null;
        }
        if (strlen($digitos) === 10) {
            return '52' . $digitos;
        }
        return $digitos;
    }
}
