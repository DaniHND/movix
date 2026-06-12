<?php declare(strict_types=1);

abstract class Controller
{
    /**
     * Renderiza una vista dentro de un layout.
     *
     * @param string $view   Ruta relativa a app/views/ (sin .php), ej: 'auth/login'
     * @param array  $data   Variables que estarán disponibles en la vista
     * @param string $layout Nombre del layout en app/views/layouts/ (sin .php)
     */
    protected function view(string $view, array $data = [], string $layout = 'auth'): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        $viewFile = APP_PATH . 'views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            ob_end_clean();
            http_response_code(500);
            die("Vista no encontrada: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = APP_PATH . 'views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            die("Layout no encontrado: {$layout}");
        }
        require $layoutFile;
    }

    /**
     * Redirige a una ruta relativa a BASE_URL.
     */
    protected function redirect(string $path): never
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Responde con JSON y termina la ejecución.
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Verifica el token CSRF del POST. Termina con 403 si falla.
     */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        $sesion = $_SESSION['csrf_token'] ?? '';

        if (!hash_equals($sesion, $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e intenta de nuevo.');
        }
    }

    /**
     * Verifica que el método HTTP coincida. Termina con 405 si no.
     */
    protected function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            http_response_code(405);
            die('Método no permitido.');
        }
    }
}
