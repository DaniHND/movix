<?php declare(strict_types=1);

class AuthController extends Controller
{
    private Usuario $usuarios;
    private Admin $admins;
    private Conductor $conductores;
    private string $loginIp = '';

    // Mensajes de error para la UI
    private const ERRORES = [
        'campos_requeridos'      => 'Por favor completa todos los campos.',
        'email_invalido'         => 'El formato del correo electrónico no es válido.',
        'password_corto'         => 'La contraseña debe tener al menos 8 caracteres.',
        'passwords_no_coinciden' => 'Las contraseñas no coinciden.',
        'email_existente'        => 'Ya existe una cuenta con ese correo electrónico.',
        'telefono_existente'     => 'Ya existe una cuenta con ese número de teléfono.',
        'credenciales_invalidas' => 'Correo o contraseña incorrectos.',
        'email_no_verificado'    => 'Debes verificar tu correo antes de iniciar sesión. Revisa tu bandeja de entrada.',
        'token_invalido'         => 'El enlace no es válido o ya fue usado.',
        'token_expirado'         => 'El enlace ha expirado. Solicita uno nuevo.',
        'password_invalido'      => 'La contraseña no cumple los requisitos o las contraseñas no coinciden.',
        'error_servidor'         => 'Error interno del servidor. Intenta de nuevo.',
        'timeout'                => 'Tu sesión expiró. Inicia sesión de nuevo.',
        'rate_limited'           => 'Demasiados intentos fallidos. Espera 15 minutos e intenta de nuevo.',
    ];

    private const EXITOS = [
        'registro_exitoso'    => 'Cuenta creada. Revisa tu correo para verificarla.',
        'email_verificado'    => '¡Correo verificado! Ya puedes iniciar sesión.',
        'recuperacion_enviada'=> 'Si el correo existe, recibirás un enlace de recuperación en minutos.',
        'password_actualizado'      => 'Contraseña actualizada. Ya puedes iniciar sesión.',
        'conductor_registrado'      => 'Solicitud enviada. Tu cuenta estará activa una vez aprobada.',
    ];

    public function __construct()
    {
        $this->usuarios    = new Usuario();
        $this->admins      = new Admin();
        $this->conductores = new Conductor();
    }

    // ------------------------------------------------------------------
    // Login
    // ------------------------------------------------------------------

    public function mostrarLogin(array $params = []): void
    {
        if (Auth::isCliente())   $this->redirect('cliente');
        if (Auth::isConductor()) $this->redirect('conductor');
        if (Auth::isAdmin())     $this->redirect('admin');

        $errorKey   = $_GET['error']   ?? null;
        $successKey = $_GET['success'] ?? null;

        $this->view('auth/login', [
            'title'   => 'Iniciar Sesión',
            'error'   => $errorKey   ? (self::ERRORES[$errorKey]   ?? $errorKey)   : null,
            'success' => $successKey ? (self::EXITOS[$successKey]  ?? $successKey) : null,
        ]);
    }

    public function login(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->mostrarLogin($params);
            return;
        }

        $this->verifyCsrf();

        $this->loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (RateLimit::isBlocked($this->loginIp)) {
            $this->redirect('login?error=rate_limited');
        }

        $email    = strtolower(trim($_POST['email']    ?? ''));
        $password = $_POST['password'] ?? '';
        $portal   = $_POST['portal']   ?? 'cliente';

        if ($email === '' || $password === '') {
            $this->redirect('login?error=campos_requeridos');
        }

        try {
            match ($portal) {
                'conductor' => $this->autenticarConductor($email, $password),
                'admin'     => $this->autenticarAdmin($email, $password),
                default     => $this->autenticarCliente($email, $password),
            };
        } catch (\Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->redirect('login?error=error_servidor');
        }
    }

    private function autenticarCliente(string $email, string $password): void
    {
        $user = $this->usuarios->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            RateLimit::hit($this->loginIp);
            $this->redirect('login?error=credenciales_invalidas');
        }

        if (!$user['email_verificado']) {
            $this->redirect('login?error=email_no_verificado');
        }

        RateLimit::clear($this->loginIp);
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email']  = $user['email'];
        $_SESSION['last_activity']  = time();

        $this->redirect('cliente');
    }

    private function autenticarConductor(string $email, string $password): void
    {
        $conductor = $this->conductores->findByEmail($email);

        if (!$conductor || !password_verify($password, $conductor['password'])) {
            RateLimit::hit($this->loginIp);
            $this->redirect('login?error=credenciales_invalidas');
        }

        if (!$conductor['email_verificado']) {
            $this->redirect('login?error=email_no_verificado');
        }

        if ($conductor['estado'] !== 'aprobado' || !$conductor['activo']) {
            RateLimit::hit($this->loginIp);
            $this->redirect('login?error=credenciales_invalidas');
        }

        RateLimit::clear($this->loginIp);
        session_regenerate_id(true);
        $_SESSION['conductor_id']     = $conductor['id'];
        $_SESSION['conductor_nombre'] = $conductor['nombre'];
        $_SESSION['conductor_email']  = $conductor['email'];
        $_SESSION['last_activity']    = time();

        $this->redirect('conductor');
    }

    private function autenticarAdmin(string $email, string $password): void
    {
        $admin = $this->admins->findByEmail($email);

        if (!$admin || !password_verify($password, $admin['password'])) {
            RateLimit::hit($this->loginIp);
            $this->redirect('login?error=credenciales_invalidas');
        }

        RateLimit::clear($this->loginIp);
        session_regenerate_id(true);
        $_SESSION['admin_id']     = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_email']  = $admin['email'];
        $_SESSION['last_activity'] = time();

        $this->redirect('admin');
    }

    // ------------------------------------------------------------------
    // Registro de cliente
    // ------------------------------------------------------------------

    public function registro(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (Auth::check()) $this->redirect('');
            $this->view('auth/registro_cliente', ['title' => 'Crear Cuenta']);
            return;
        }

        $this->verifyCsrf();

        $nombre    = trim($_POST['nombre']            ?? '');
        $email     = strtolower(trim($_POST['email']  ?? ''));
        $telefono  = trim($_POST['telefono']           ?? '');
        $password  = $_POST['password']               ?? '';
        $confirmar = $_POST['confirmar_password']      ?? '';
        $fecha_nac = trim($_POST['fecha_nacimiento']   ?? '');
        $sexo      = $_POST['sexo']                   ?? null;

        if ($nombre === '' || $email === '' || $telefono === '' || $password === '') {
            $this->redirect('registro?error=campos_requeridos');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('registro?error=email_invalido');
        }

        if (strlen($password) < 8) {
            $this->redirect('registro?error=password_corto');
        }

        if ($password !== $confirmar) {
            $this->redirect('registro?error=passwords_no_coinciden');
        }

        if ($this->usuarios->findByEmail($email)) {
            $this->redirect('registro?error=email_existente');
        }

        if ($this->usuarios->findByTelefono($telefono)) {
            $this->redirect('registro?error=telefono_existente');
        }

        $token  = bin2hex(random_bytes(32));
        $userId = $this->usuarios->create([
            'nombre'             => $nombre,
            'email'              => $email,
            'telefono'           => $telefono,
            'password'           => password_hash($password, PASSWORD_BCRYPT),
            'fecha_nacimiento'   => $fecha_nac !== '' ? $fecha_nac : null,
            'sexo'               => in_array($sexo, ['M','F','otro'], true) ? $sexo : null,
            'token_verificacion' => $token,
        ]);

        if ($userId === 0) {
            $this->redirect('registro?error=error_servidor');
        }

        try {
            Mailer::verificacion($email, $nombre, $token);
        } catch (\Throwable $e) {
            error_log('Error enviando email de verificación: ' . $e->getMessage());
        }

        $this->redirect('login?success=registro_exitoso');
    }

    // ------------------------------------------------------------------
    // Verificación de email
    // ------------------------------------------------------------------

    public function verificarEmail(array $params = []): void
    {
        $token = $params[0] ?? '';

        if ($token === '') {
            $this->redirect('login?error=token_invalido');
        }

        $verificado = $this->usuarios->verificarEmail($token);

        $this->redirect($verificado
            ? 'login?success=email_verificado'
            : 'login?error=token_invalido'
        );
    }

    // ------------------------------------------------------------------
    // Recuperación de contraseña
    // ------------------------------------------------------------------

    public function recuperar(array $params = []): void
    {
        $token = $params[0] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($token !== '') {
                $this->mostrarFormNuevaPassword($token);
                return;
            }
            $this->view('auth/recuperar', ['title' => 'Recuperar Contraseña']);
            return;
        }

        $this->verifyCsrf();

        if (isset($_POST['nueva_password'])) {
            $this->procesarNuevaPassword();
        } else {
            $this->enviarEnlaceRecuperacion();
        }
    }

    private function enviarEnlaceRecuperacion(): void
    {
        $email = strtolower(trim($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('recuperar?error=email_invalido');
        }

        // Siempre responder igual para no revelar si el email existe
        $user = $this->usuarios->findByEmail($email);
        if ($user) {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            $this->usuarios->setTokenRecuperacion((int) $user['id'], $token, $expira);

            try {
                Mailer::recuperacion($email, $user['nombre'], $token);
            } catch (\Throwable $e) {
                error_log('Error enviando email de recuperación: ' . $e->getMessage());
            }
        }

        $this->redirect('login?success=recuperacion_enviada');
    }

    private function mostrarFormNuevaPassword(string $token): void
    {
        $user = $this->usuarios->findByTokenRecuperacion($token);

        if (!$user) {
            $this->redirect('login?error=token_expirado');
        }

        $errorKey = $_GET['error'] ?? null;
        $this->view('auth/recuperar_nueva', [
            'title'  => 'Nueva Contraseña',
            'token'  => $token,
            'error'  => $errorKey ? (self::ERRORES[$errorKey] ?? $errorKey) : null,
        ]);
    }

    private function procesarNuevaPassword(): void
    {
        $token     = $_POST['token']            ?? '';
        $password  = $_POST['nueva_password']   ?? '';
        $confirmar = $_POST['confirmar_password']?? '';

        $user = $this->usuarios->findByTokenRecuperacion($token);

        if (!$user) {
            $this->redirect('login?error=token_expirado');
        }

        if (strlen($password) < 8 || $password !== $confirmar) {
            $this->redirect('recuperar/' . $token . '?error=password_invalido');
        }

        $this->usuarios->updatePassword((int) $user['id'], password_hash($password, PASSWORD_BCRYPT));
        $this->redirect('login?success=password_actualizado');
    }

    // ------------------------------------------------------------------
    // Cerrar sesión
    // ------------------------------------------------------------------

    public function cerrarSesion(array $params = []): void
    {
        session_unset();
        session_destroy();
        $this->redirect('login');
    }
}
