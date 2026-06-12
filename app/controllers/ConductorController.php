<?php declare(strict_types=1);

class ConductorController extends Controller
{
    private Conductor $conductorRepo;
    private Viaje     $viajeRepo;

    /** Rutas relativas a UPLOAD_PATH de archivos ya guardados en esta petición */
    private array $uploadedPaths = [];

    public function __construct()
    {
        $this->conductorRepo = new Conductor();
        $this->viajeRepo     = new Viaje();
    }

    // ── Rutas protegidas ─────────────────────────────────────

    public function dispatch(array $params = []): void
    {
        Auth::requireConductor();

        $accion = $params[0] ?? '';

        match ($accion) {
            '', 'inicio' => $this->inicio(),
            'viaje'      => $this->viajeAccion($params),
            'historial'  => $this->historial(),
            'perfil'     => $this->perfil(),
            default      => $this->noEncontrado(),
        };
    }

    private function inicio(): void
    {
        $id          = Auth::conductorId();
        $conductor   = $this->conductorRepo->findByIdWithVehicle($id);
        $viajeActivo = null;
        $pendientes  = [];

        if ($conductor && $conductor['estado'] === 'aprobado') {
            $viajeActivo = $this->viajeRepo->activoConductor($id);
            if (!$viajeActivo && $conductor['activo']) {
                $pendientes = $this->viajeRepo->pendientes(5);
            }
        }

        $this->view('conductor/inicio', [
            'title'       => 'Inicio — Movix Conductor',
            'accion'      => 'inicio',
            'conductor'   => $conductor,
            'viajeActivo' => $viajeActivo,
            'pendientes'  => $pendientes,
        ], 'conductor');
    }

    private function viajeAccion(array $params): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        $sub = $params[1] ?? '';
        $id  = (int)($params[2] ?? 0);

        if ($id <= 0) $this->redirect('conductor');

        $conductorId = Auth::conductorId();

        match ($sub) {
            'tomar'     => $this->viajeRepo->tomarViaje($id, $conductorId),
            'iniciar'   => $this->viajeRepo->iniciar($id, $conductorId),
            'completar' => $this->viajeRepo->completar($id, $conductorId),
            default     => null,
        };

        $this->redirect('conductor');
    }

    private function historial(): void
    {
        $id     = Auth::conductorId();
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $lim    = 15;
        $off    = ($pagina - 1) * $lim;
        $total  = $this->viajeRepo->totalHistorialConductor($id);

        $this->view('conductor/historial', [
            'title'     => 'Historial — Movix',
            'accion'    => 'historial',
            'viajes'    => $this->viajeRepo->historialConductor($id, $lim, $off),
            'total'     => $total,
            'pagina'    => $pagina,
            'totalPags' => (int)ceil($total / $lim),
        ], 'conductor');
    }

    private function perfil(): void
    {
        $id = Auth::conductorId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $tel = trim($_POST['telefono'] ?? '');
            if ($tel !== '') {
                $this->conductorRepo->actualizarTelefono($id, $tel);
            }
            $this->redirect('conductor/perfil?ok=1');
            return;
        }

        $this->view('conductor/perfil', [
            'title'     => 'Mi perfil — Movix',
            'accion'    => 'perfil',
            'conductor' => $this->conductorRepo->findByIdWithVehicle($id),
            'success'   => isset($_GET['ok']),
        ], 'conductor');
    }

    // ── Registro (sin auth) ───────────────────────────────────

    public function registro(array $params = []): void
    {
        if (Auth::isConductor()) $this->redirect('conductor');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $errKey = $_GET['error'] ?? null;
            $errores = [
                'campos_requeridos'      => 'Por favor completa todos los campos obligatorios.',
                'email_invalido'         => 'El formato del correo no es válido.',
                'password_corto'         => 'La contraseña debe tener al menos 8 caracteres.',
                'passwords_no_coinciden' => 'Las contraseñas no coinciden.',
                'email_existente'        => 'Ya existe una cuenta con ese correo.',
                'identidad_existente'    => 'Ya existe una cuenta con ese número de identidad.',
                'placa_existente'        => 'Ya existe un vehículo registrado con esa placa.',
                'doc_requerido'          => 'Debes subir todos los documentos y fotos marcados como obligatorios.',
                'doc_muy_grande'         => 'Un archivo supera el límite de 8 MB.',
                'doc_tipo_invalido'      => 'Solo se aceptan imágenes (JPEG, PNG) o archivos PDF.',
                'doc_upload_error'       => 'Error al procesar un archivo. Intenta de nuevo.',
                'error_servidor'         => 'Error interno del servidor. Intenta de nuevo.',
            ];
            $this->view('conductor/registro', [
                'title' => 'Registro de Conductor — Movix',
                'error' => $errKey ? ($errores[$errKey] ?? $errKey) : null,
            ], 'auth');
            return;
        }

        $this->verifyCsrf();
        $this->procesarRegistro();
    }

    private function procesarRegistro(): void
    {
        $nombre    = trim($_POST['nombre']           ?? '');
        $fechaNac  = trim($_POST['fecha_nacimiento'] ?? '');
        $identidad = trim($_POST['identidad']        ?? '');
        $telefono  = trim($_POST['telefono']         ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $password  = $_POST['password']              ?? '';
        $confirmar = $_POST['confirmar_password']    ?? '';
        $tipo      = $_POST['tipo']                  ?? '';
        $marca     = trim($_POST['marca']            ?? '');
        $modelo    = trim($_POST['modelo']           ?? '');
        $anio      = (int)($_POST['anio']            ?? 0);
        $placa     = strtoupper(trim($_POST['placa'] ?? ''));
        $color     = trim($_POST['color']            ?? '');
        $numTaxi   = trim($_POST['numero_taxi']      ?? '');

        if (!$nombre || !$fechaNac || !$identidad || !$telefono || !$email ||
            !$password || !in_array($tipo, ['convencional','vip'], true) ||
            !$marca || !$modelo || !$anio || !$placa || !$color) {
            $this->redirect('registro-conductor?error=campos_requeridos');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('registro-conductor?error=email_invalido');
        }

        if (strlen($password) < 8) {
            $this->redirect('registro-conductor?error=password_corto');
        }

        if ($password !== $confirmar) {
            $this->redirect('registro-conductor?error=passwords_no_coinciden');
        }

        if ($this->conductorRepo->findByEmail($email)) {
            $this->redirect('registro-conductor?error=email_existente');
        }

        if ($this->conductorRepo->findByIdentidad($identidad)) {
            $this->redirect('registro-conductor?error=identidad_existente');
        }

        $vehiculoRepo = new Vehiculo();
        if ($vehiculoRepo->findByPlaca($placa)) {
            $this->redirect('registro-conductor?error=placa_existente');
        }

        // Subir archivos — los requeridos abortan con limpieza si faltan
        $fotoPerfil        = $this->subirArchivo('foto_perfil',           'photos', true);
        $fotoIdentFrente   = $this->subirArchivo('foto_identidad_frente', 'docs',   true);
        $fotoIdentReverso  = $this->subirArchivo('foto_identidad_reverso','docs',   true);
        $fotoLicFrente     = $this->subirArchivo('foto_licencia_frente',  'docs',   true);
        $fotoLicReverso    = $this->subirArchivo('foto_licencia_reverso', 'docs',   true);
        $fotoVehFrente     = $this->subirArchivo('foto_veh_frente',       'photos', true);
        $fotoVehAtras      = $this->subirArchivo('foto_veh_atras',        'photos', false);

        try {
            $conductorId = $this->conductorRepo->crear([
                'nombre'                 => $nombre,
                'fecha_nacimiento'       => $fechaNac,
                'identidad'              => $identidad,
                'telefono'               => $telefono,
                'email'                  => $email,
                'password'               => password_hash($password, PASSWORD_BCRYPT),
                'tipo'                   => $tipo,
                'foto_perfil'            => $fotoPerfil,
                'foto_identidad_frente'  => $fotoIdentFrente,
                'foto_identidad_reverso' => $fotoIdentReverso,
                'foto_licencia_frente'   => $fotoLicFrente,
                'foto_licencia_reverso'  => $fotoLicReverso,
            ]);

            $vehiculoRepo->crear($conductorId, [
                'tipo'        => $tipo,
                'marca'       => $marca,
                'modelo'      => $modelo,
                'anio'        => $anio,
                'placa'       => $placa,
                'color'       => $color,
                'numero_taxi' => $numTaxi,
                'foto_frente' => $fotoVehFrente,
                'foto_atras'  => $fotoVehAtras,
            ]);
        } catch (\Throwable $e) {
            error_log('Registro conductor: ' . $e->getMessage());
            $this->limpiarUploads();
            $this->redirect('registro-conductor?error=error_servidor');
        }

        $this->redirect('login?portal=conductor&success=conductor_registrado');
    }

    /**
     * Sube un archivo del formulario a uploads/{subcarpeta}/.
     * Retorna la ruta relativa a UPLOAD_PATH, o null si no se envió y no era requerido.
     * Redirige con error y limpia uploads previos si la validación falla.
     */
    private function subirArchivo(string $campo, string $subcarpeta, bool $requerido): ?string
    {
        $file = $_FILES[$campo] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE || $file['tmp_name'] === '') {
            if ($requerido) {
                $this->limpiarUploads();
                $this->redirect('registro-conductor?error=doc_requerido');
            }
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->limpiarUploads();
            $this->redirect('registro-conductor?error=doc_upload_error');
        }

        if ($file['size'] > 8 * 1024 * 1024) {
            $this->limpiarUploads();
            $this->redirect('registro-conductor?error=doc_muy_grande');
        }

        $mime = @mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
            $this->limpiarUploads();
            $this->redirect('registro-conductor?error=doc_tipo_invalido');
        }

        $ext = match ($mime) {
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        };

        $dir  = UPLOAD_PATH . $subcarpeta . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $nombre)) {
            $this->limpiarUploads();
            $this->redirect('registro-conductor?error=error_servidor');
        }

        $ruta = $subcarpeta . '/' . $nombre;
        $this->uploadedPaths[] = $ruta;
        return $ruta;
    }

    private function limpiarUploads(): void
    {
        foreach ($this->uploadedPaths as $ruta) {
            $abs = UPLOAD_PATH . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
            if (file_exists($abs)) {
                @unlink($abs);
            }
        }
        $this->uploadedPaths = [];
    }

    private function noEncontrado(): void
    {
        http_response_code(404);
        require APP_PATH . 'views/errors/404.php';
        exit;
    }
}
