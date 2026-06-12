<?php declare(strict_types=1);

class ClienteController extends Controller
{
    private Viaje   $viajes;
    private Tarifa  $tarifas;
    private Usuario $usuarios;

    public function __construct()
    {
        $this->viajes   = new Viaje();
        $this->tarifas  = new Tarifa();
        $this->usuarios = new Usuario();
    }

    public function dispatch(array $params = []): void
    {
        Auth::requireCliente();

        $accion = $params[0] ?? '';

        match ($accion) {
            '', 'mapa'      => $this->mapa(),
            'solicitar'     => $this->solicitar(),
            'viaje'         => $this->verViaje((int)($params[1] ?? 0)),
            'cancelar'      => $this->cancelar((int)($params[1] ?? 0)),
            'historial'     => $this->historial(),
            'perfil'        => $this->perfil(),
            default         => $this->noEncontrado(),
        };
    }

    // ── Mapa / inicio ────────────────────────────────────────

    private function mapa(): void
    {
        $viajeActivo = $this->viajes->activoCliente(Auth::userId());
        $this->view('cliente/inicio', [
            'title'       => 'Movix — Inicio',
            'accion'      => 'mapa',
            'viajeActivo' => $viajeActivo,
        ], 'cliente');
    }

    // ── Solicitar viaje (POST JSON) ──────────────────────────

    private function solicitar(): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        $clienteId = Auth::userId();

        // Bloquear si ya tiene viaje activo
        if ($this->viajes->activoCliente($clienteId)) {
            $this->json(['error' => 'Ya tienes un viaje activo'], 409);
        }

        $tipo       = $_POST['tipo_servicio']    ?? '';
        $latOrig    = (float)($_POST['lat_origen']  ?? 0);
        $lngOrig    = (float)($_POST['lng_origen']  ?? 0);
        $latDest    = (float)($_POST['lat_destino'] ?? 0);
        $lngDest    = (float)($_POST['lng_destino'] ?? 0);
        $distancia  = (float)($_POST['distancia_km'] ?? 0);
        $duracion   = (int)($_POST['duracion_min']   ?? 0);
        $pasajeros  = max(1, min(4, (int)($_POST['num_pasajeros'] ?? 1)));
        $idaVuelta  = (int)(bool)($_POST['ida_y_regreso'] ?? 0);
        $dirOrig    = trim(htmlspecialchars($_POST['direccion_origen']  ?? '', ENT_QUOTES));
        $dirDest    = trim(htmlspecialchars($_POST['direccion_destino'] ?? '', ENT_QUOTES));

        if (!in_array($tipo, ['convencional', 'vip'], true)) {
            $this->json(['error' => 'Tipo de servicio inválido'], 422);
        }
        if ($latDest === 0.0 || $lngDest === 0.0) {
            $this->json(['error' => 'Destino requerido'], 422);
        }
        if ($distancia <= 0) {
            $this->json(['error' => 'Distancia inválida'], 422);
        }

        $cuponId   = max(0, (int)($_POST['cupon_id']     ?? 0));
        $descPct   = max(0, min(100, (int)($_POST['descuento_pct'] ?? 0)));

        $precio = $this->tarifas->calcular($tipo, $distancia, $pasajeros);

        if (isset($precio['error'])) {
            $this->json(['error' => 'No hay tarifa disponible para este trayecto'], 422);
        }

        // Validar cupón si viene
        if ($cuponId > 0 && $descPct > 0) {
            $cuponRepo = new Cupon();
            $cupon     = $cuponRepo->findById($cuponId);
            // Re-validar en servidor (no confiar solo en el cliente)
            $cuponOk   = $cupon && $cuponRepo->validar($cupon['codigo'], $clienteId);
            if ($cuponOk) {
                $factor             = 1 - ($cupon['descuento_pct'] / 100);
                $precio['precio_total'] = round($precio['precio_total'] * $factor, 2);
            } else {
                $cuponId = 0; // invalidar si la re-validación falla
            }
        }

        $latOrigFinal = $latOrig ?: 14.0818;
        $lngOrigFinal = $lngOrig ?: -87.2068;

        $id = $this->viajes->crear([
            ':cliente_id'        => $clienteId,
            ':tipo_servicio'     => $tipo,
            ':num_pasajeros'     => $pasajeros,
            ':ida_y_regreso'     => $idaVuelta,
            ':lat_origen'        => $latOrigFinal,
            ':lng_origen'        => $lngOrigFinal,
            ':direccion_origen'  => $dirOrig,
            ':lat_destino'       => $latDest,
            ':lng_destino'       => $lngDest,
            ':direccion_destino' => $dirDest,
            ':distancia_km'      => $distancia,
            ':duracion_min'      => $duracion,
            ':horario'           => $precio['horario'],
            ':precio_base'       => $precio['precio_base'],
            ':comision_app'      => $precio['comision_app'],
            ':precio_total'      => $precio['precio_total'],
        ]);

        // Registrar uso del cupón
        if ($cuponId > 0 && isset($cuponRepo) && isset($cuponOk) && $cuponOk) {
            $cuponRepo->aplicar($cuponId, $clienteId, $id);
        }

        // Motor de asignación automática: buscar conductor más cercano
        $conductorInfo = null;
        $conductorRepo = new Conductor();
        $nearest = $conductorRepo->findNearestAvailable($latOrigFinal, $lngOrigFinal, $tipo);
        if ($nearest) {
            $this->viajes->tomarViaje($id, (int)$nearest['id']);
            $conductorInfo = [
                'nombre'      => $nearest['nombre'],
                'marca'       => $nearest['marca']       ?? '',
                'modelo'      => $nearest['modelo']      ?? '',
                'color'       => $nearest['color']       ?? '',
                'numero_taxi' => $nearest['numero_taxi'] ?? '',
                'distancia'   => round((float)$nearest['distancia_km'], 1),
            ];

            // Notificación push al conductor
            $conductorFull = $conductorRepo->findById((int)$nearest['id']);
            if (!empty($conductorFull['fcm_token'])) {
                FcmService::notificarViajeAsignado(
                    $conductorFull['fcm_token'],
                    $id,
                    $dirOrig ?: 'Origen del cliente'
                );
            }
        }

        $this->json([
            'viaje_id'     => $id,
            'precio_total' => $precio['precio_total'],
            'conductor'    => $conductorInfo,
        ]);
    }

    // ── Ver viaje activo ─────────────────────────────────────

    private function verViaje(int $id): void
    {
        if ($id <= 0) {
            $this->redirect('cliente');
        }

        $viaje = $this->viajes->findById($id);

        if (!$viaje || (int)$viaje['cliente_id'] !== Auth::userId()) {
            $this->redirect('cliente');
        }

        $this->view('cliente/viaje_activo', [
            'title'  => 'Tu viaje — Movix',
            'accion' => 'viaje',
            'viaje'  => $viaje,
        ], 'cliente');
    }

    // ── Cancelar viaje (POST) ────────────────────────────────

    private function cancelar(int $id): void
    {
        $this->requireMethod('POST');
        $this->verifyCsrf();

        if ($id <= 0) {
            $this->redirect('cliente');
        }

        $this->viajes->cancelar($id, Auth::userId(), 'Cancelado por el cliente');
        $this->redirect('cliente');
    }

    // ── Historial ────────────────────────────────────────────

    private function historial(): void
    {
        $clienteId = Auth::userId();
        $pagina    = max(1, (int)($_GET['pagina'] ?? 1));
        $lim       = 15;
        $off       = ($pagina - 1) * $lim;
        $total     = $this->viajes->totalHistorial($clienteId);
        $viajes    = $this->viajes->historial($clienteId, $lim, $off);

        $this->view('cliente/historial', [
            'title'      => 'Historial — Movix',
            'accion'     => 'historial',
            'viajes'     => $viajes,
            'total'      => $total,
            'pagina'     => $pagina,
            'totalPags'  => (int)ceil($total / $lim),
        ], 'cliente');
    }

    // ── Perfil ───────────────────────────────────────────────

    private function perfil(): void
    {
        $clienteId = Auth::userId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarPerfil($clienteId);
            return;
        }

        $usuario    = $this->usuarios->findById($clienteId);
        $totalViajes = $this->viajes->totalHistorial($clienteId);

        $this->view('cliente/perfil', [
            'title'       => 'Mi perfil — Movix',
            'accion'      => 'perfil',
            'usuario'     => $usuario,
            'totalViajes' => $totalViajes,
            'success'     => $_GET['ok'] ?? null,
        ], 'cliente');
    }

    private function actualizarPerfil(int $clienteId): void
    {
        $this->verifyCsrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $tel    = trim($_POST['telefono'] ?? '');

        if (strlen($nombre) < 2) {
            $this->redirect('cliente/perfil?error=nombre');
        }

        $this->usuarios->actualizarDatos($clienteId, $nombre, $tel);
        $_SESSION['nombre'] = $nombre;
        $this->redirect('cliente/perfil?ok=1');
    }

    private function noEncontrado(): void
    {
        http_response_code(404);
        require APP_PATH . 'views/errors/404.php';
        exit;
    }
}
