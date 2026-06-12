<?php declare(strict_types=1);

/**
 * Firebase Cloud Messaging — HTTP Legacy API (v1 requiere OAuth2 y es más complejo).
 *
 * Para activar: agrega FCM_SERVER_KEY=<tu clave> en .env
 * La clave se obtiene en: Firebase Console → Configuración del proyecto → Cloud Messaging
 */
class FcmService
{
    private const ENDPOINT = 'https://fcm.googleapis.com/fcm/send';

    public static function push(string $fcmToken, string $titulo, string $cuerpo, array $data = []): bool
    {
        $serverKey = $_ENV['FCM_SERVER_KEY'] ?? '';

        if ($serverKey === '' || $fcmToken === '') {
            return false; // FCM no configurado — silencioso
        }

        $payload = json_encode([
            'to'           => $fcmToken,
            'notification' => [
                'title' => $titulo,
                'body'  => $cuerpo,
                'sound' => 'default',
                'badge' => '1',
            ],
            'data'         => $data,
            'priority'     => 'high',
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", [
                    'Content-Type: application/json',
                    'Authorization: key=' . $serverKey,
                ]),
                'content' => $payload,
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents(self::ENDPOINT, false, $ctx);

        if ($result === false) return false;

        $decoded = json_decode($result, true);
        return isset($decoded['success']) && $decoded['success'] === 1;
    }

    /** Notifica a un conductor cuando se le asigna un viaje. */
    public static function notificarViajeAsignado(string $fcmToken, int $viajeId, string $origen): bool
    {
        return self::push(
            $fcmToken,
            '¡Nuevo viaje asignado!',
            "Recoge al cliente en: {$origen}",
            ['tipo' => 'viaje_asignado', 'viaje_id' => (string)$viajeId]
        );
    }
}
