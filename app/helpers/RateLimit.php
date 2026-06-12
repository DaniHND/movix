<?php declare(strict_types=1);

class RateLimit
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SEC   = 900;   // 15 minutos
    private const BLOCK_SEC    = 900;

    /** Devuelve true si la IP está temporalmente bloqueada. */
    public static function isBlocked(string $ip): bool
    {
        $data = self::readFile($ip);
        if ($data === null) return false;

        if ($data['blocked_until'] !== null && time() < $data['blocked_until']) {
            return true;
        }

        if (time() - $data['first_at'] > self::WINDOW_SEC) {
            self::deleteFile($ip);
        }

        return false;
    }

    /** Registra un intento fallido. Activa bloqueo al alcanzar el límite. */
    public static function hit(string $ip): void
    {
        $data = self::readFile($ip) ?? ['attempts' => 0, 'first_at' => time(), 'blocked_until' => null];

        if (time() - $data['first_at'] > self::WINDOW_SEC) {
            $data = ['attempts' => 0, 'first_at' => time(), 'blocked_until' => null];
        }

        $data['attempts']++;

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            $data['blocked_until'] = time() + self::BLOCK_SEC;
        }

        self::saveFile($ip, $data);
        self::cleanup();
    }

    /** Borra el registro al autenticarse con éxito. */
    public static function clear(string $ip): void
    {
        self::deleteFile($ip);
    }

    // ── Internos ─────────────────────────────────────────────────────────────

    private static function dir(): string
    {
        return LOG_PATH . 'ratelimit' . DIRECTORY_SEPARATOR;
    }

    private static function filePath(string $ip): string
    {
        return self::dir() . md5($ip) . '.json';
    }

    private static function readFile(string $ip): ?array
    {
        $file = self::filePath($ip);
        if (!file_exists($file)) return null;
        $raw = @file_get_contents($file);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private static function saveFile(string $ip, array $data): void
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::filePath($ip), json_encode($data), LOCK_EX);
    }

    private static function deleteFile(string $ip): void
    {
        $file = self::filePath($ip);
        if (file_exists($file)) @unlink($file);
    }

    private static function cleanup(): void
    {
        if (random_int(1, 10) !== 1) return;
        $dir = self::dir();
        if (!is_dir($dir)) return;
        $cutoff = time() - self::WINDOW_SEC * 2;
        foreach (glob($dir . '*.json') ?: [] as $file) {
            if (filemtime($file) < $cutoff) @unlink($file);
        }
    }
}
