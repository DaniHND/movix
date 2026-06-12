<?php declare(strict_types=1);

class AdminController extends Controller
{
    private Admin  $adminRepo;
    private Viaje  $viajeRepo;
    private Tarifa $tarifaRepo;

    private Cupon $cuponRepo;

    public function __construct()
    {
        $this->adminRepo  = new Admin();
        $this->viajeRepo  = new Viaje();
        $this->tarifaRepo = new Tarifa();
        $this->cuponRepo  = new Cupon();
    }

    public function dispatch(array $params = []): void
    {
        Auth::requireAdmin();

        $accion = $params[0] ?? '';

        match ($accion) {
            '', 'dashboard' => $this->dashboard(),
            'conductores'   => $this->conductoresPage($params),
            'tarifas'       => $this->tarifasPage($params),
            'viajes'        => $this->viajesPage(),
            'clientes'      => $this->clientesPage(),
            'cupones'       => $this->cuponesPage($params),
            default         => $this->noEncontrado(),
        };
    }

    // ── Dashboard ────────────────────────────────────────────

    private function dashboard(): void
    {
        $this->view('admin/dashboard', [
            'title'   => 'Dashboard — Movix Admin',
            'accion'  => 'dashboard',
            'stats'   => $this->adminRepo->statsGlobales(),
            'dias30'  => $this->adminRepo->statsUltimos30Dias(30),
        ], 'admin');
    }

    // ── Conductores ──────────────────────────────────────────

    private function conductoresPage(array $params): void
    {
        $sub = $params[1] ?? '';

        if ($sub === 'ver')    { $this->verConductor((int)($params[2] ?? 0));    return; }
        if ($sub === 'accion') { $this->accionConductor((int)($params[2] ?? 0)); return; }

        $estado = $_GET['estado'] ?? '';
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $lim    = 20;
        $off    = ($pagina - 1) * $lim;
        $total  = $this->adminRepo->totalConductores($estado);

        $this->view('admin/conductores', [
            'title'        => 'Conductores — Movix Admin',
            'accion'       => 'conductores',
            'lista'        => $this->adminRepo->conductoresLista($estado, $lim, $off),
            'total'        => $total,
            'pagina'       => $pagina,
            'totalPags'    => (int)ceil($total / $lim),
            'filtroEstado' => $estado,
        ], 'admin');
    }

    private function verConductor(int $id): void
    {
        if ($id <= 0) $this->redirect('admin/conductores');

        $conductor = $this->adminRepo->conductorPorId($id);
        if (!$conductor) $this->redirect('admin/conductores');

        $mensajes = [
            'aprobar'   => 'Conductor aprobado correctamente.',
            'rechazar'  => 'Conductor rechazado.',
            'suspender' => 'Conductor suspendido.',
        ];
        $ok = $_GET['ok'] ?? '';

        $this->view('admin/conductor_detalle', [
            'title'     => 'Conductor — Movix Admin',
            'accion'    => 'conductores',
            'conductor' => $conductor,
            'okMsg'     => isset($mensajes[$ok]) ? $mensajes[$ok] : null,
        ], 'admin');
    }

    private function accionConductor(int $id): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        if ($id <= 0) $this->redirect('admin/conductores');

        $accion = $_POST['accion'] ?? '';

        match ($accion) {
            'aprobar'   => $this->adminRepo->aprobar($id),
            'rechazar'  => $this->adminRepo->rechazar($id),
            'suspender' => $this->adminRepo->suspender($id),
            default     => null,
        };

        $this->redirect("admin/conductores/ver/{$id}?ok={$accion}");
    }

    // ── Tarifas ──────────────────────────────────────────────

    private function tarifasPage(array $params): void
    {
        $sub = $params[1] ?? '';

        if ($sub === 'editar') {
            $this->editarTarifa((int)($params[2] ?? 0));
            return;
        }

        $this->view('admin/tarifas', [
            'title'   => 'Tarifas — Movix Admin',
            'accion'  => 'tarifas',
            'tarifas' => $this->tarifaRepo->todas(),
            'okMsg'   => isset($_GET['ok'])    ? 'Tarifa actualizada correctamente.' : null,
            'errMsg'  => isset($_GET['error']) ? 'El precio base debe ser mayor a 0.' : null,
        ], 'admin');
    }

    private function editarTarifa(int $id): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        if ($id <= 0) $this->redirect('admin/tarifas');

        $pb = (float)str_replace(',', '.', $_POST['precio_base']           ?? '0');
        $pe = (float)str_replace(',', '.', $_POST['precio_pasajero_extra'] ?? '0');
        $cf = (float)str_replace(',', '.', $_POST['comision_fija']          ?? '0');

        if ($pb <= 0) $this->redirect('admin/tarifas?error=precio');

        $this->tarifaRepo->actualizar($id, $pb, $pe, $cf);
        $this->redirect('admin/tarifas?ok=1');
    }

    // ── Viajes ───────────────────────────────────────────────

    private function viajesPage(): void
    {
        $estado = $_GET['estado'] ?? '';
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $lim    = 20;
        $off    = ($pagina - 1) * $lim;
        $total  = $this->viajeRepo->totalAdmin($estado);

        $this->view('admin/viajes', [
            'title'        => 'Viajes — Movix Admin',
            'accion'       => 'viajes',
            'lista'        => $this->viajeRepo->listarAdmin($lim, $off, $estado),
            'total'        => $total,
            'pagina'       => $pagina,
            'totalPags'    => (int)ceil($total / $lim),
            'filtroEstado' => $estado,
        ], 'admin');
    }

    // ── Clientes ─────────────────────────────────────────────

    private function clientesPage(): void
    {
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $lim    = 20;
        $off    = ($pagina - 1) * $lim;
        $total  = $this->adminRepo->totalClientes();

        $this->view('admin/clientes', [
            'title'     => 'Clientes — Movix Admin',
            'accion'    => 'clientes',
            'lista'     => $this->adminRepo->clientesLista($lim, $off),
            'total'     => $total,
            'pagina'    => $pagina,
            'totalPags' => (int)ceil($total / $lim),
        ], 'admin');
    }

    // ── Cupones ──────────────────────────────────────────────

    private function cuponesPage(array $params): void
    {
        $sub = $params[1] ?? '';

        if ($sub === 'crear') {
            $this->crearCupon();
            return;
        }

        if ($sub === 'toggle') {
            $this->toggleCupon((int)($params[2] ?? 0));
            return;
        }

        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $lim    = 20;
        $off    = ($pagina - 1) * $lim;
        $total  = $this->cuponRepo->total();

        $this->view('admin/cupones', [
            'title'     => 'Cupones — Movix Admin',
            'accion'    => 'cupones',
            'lista'     => $this->cuponRepo->listar($lim, $off),
            'total'     => $total,
            'pagina'    => $pagina,
            'totalPags' => (int)ceil($total / $lim),
            'okMsg'     => isset($_GET['ok'])    ? 'Operación realizada correctamente.' : null,
            'errMsg'    => isset($_GET['error']) ? 'El código de cupón ya existe.'      : null,
        ], 'admin');
    }

    private function crearCupon(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        $codigo  = strtoupper(trim($_POST['codigo']        ?? ''));
        $pct     = max(1, min(100, (int)($_POST['descuento_pct'] ?? 0)));
        $max     = max(1, (int)($_POST['usos_max']         ?? 1));
        $vence   = trim($_POST['vence_at'] ?? '') ?: null;

        if ($codigo === '' || $pct === 0) {
            $this->redirect('admin/cupones?error=datos');
        }

        if ($this->cuponRepo->findByCodigo($codigo)) {
            $this->redirect('admin/cupones?error=duplicado');
        }

        $this->cuponRepo->crear([
            'codigo'       => $codigo,
            'descuento_pct'=> $pct,
            'usos_max'     => $max,
            'vence_at'     => $vence,
        ]);

        $this->redirect('admin/cupones?ok=1');
    }

    private function toggleCupon(int $id): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        if ($id <= 0) $this->redirect('admin/cupones');

        $activo = (int)(bool)($_POST['activo'] ?? 0);
        $this->cuponRepo->toggleActivo($id, $activo);
        $this->redirect('admin/cupones?ok=1');
    }

    // ─────────────────────────────────────────────────────────

    private function noEncontrado(): void
    {
        http_response_code(404);
        require APP_PATH . 'views/errors/404.php';
        exit;
    }
}
