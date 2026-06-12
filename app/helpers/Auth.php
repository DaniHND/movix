<?php declare(strict_types=1);

class Auth
{
    public static function check(): bool
    {
        return self::isCliente() || self::isConductor() || self::isAdmin();
    }

    public static function isCliente(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    public static function isConductor(): bool
    {
        return !empty($_SESSION['conductor_id']);
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function requireCliente(): void
    {
        if (!self::isCliente()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireConductor(): void
    {
        if (!self::isConductor()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    public static function conductorId(): ?int
    {
        return isset($_SESSION['conductor_id']) ? (int) $_SESSION['conductor_id'] : null;
    }

    public static function adminId(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    public static function nombre(): string
    {
        return (string) (
            $_SESSION['usuario_nombre'] ??
            $_SESSION['conductor_nombre'] ??
            $_SESSION['admin_nombre'] ??
            ''
        );
    }
}
