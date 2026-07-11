<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo de recuperacion de contraseña para clientes de la tienda en linea.
 *
 * A diferencia de la notificacion por defecto de Laravel (que apunta a la ruta
 * 'password.reset' con las vistas de auth), esta enlaza al flujo propio de
 * clientes en /clientes/restablecer-password/{token}, en español.
 */
class ClienteRestablecerPassword extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('tienda_online.password.form', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        // Minutos de expiracion del token (config/auth passwords.users.expire).
        $minutos = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Recupera tu contraseña — OWARI Tienda Online')
            ->greeting('Hola')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta de la tienda en línea.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace caducará en ' . $minutos . ' minutos.')
            ->line('Si tú no solicitaste este cambio, puedes ignorar este correo; tu contraseña seguirá igual.')
            ->salutation('Saludos, OWARI Tienda Online');
    }
}
