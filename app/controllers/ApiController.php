<?php declare(strict_types=1);

class ApiController extends Controller
{
    public function dispatch(array $params = []): void
    {
        // Todos los endpoints requieren sesión activa
        if (!Auth::check()) {
            $this->json(['error' => 'No autenticado'], 401);
        }

        $endpoint = $params[0] ?? '';

        match ($endpoint) {
            'conductores' => $this->conductores(),
            'precio'      => $this->precio(),
            'viaje'       => $this->estadoViaje((int)($params[1] ?? 0)),
            'conductor'   => $this->conductorApi($params),
            'chat'        => $this->chatApi($params),
            'cupon'       => $this->cuponApi(),
            default       => $this->json(['error' => "/{$endpoint} no implementado"], 501),
        };
    }

    // GET /api/conductores?tipo=convencional
    private function conductores(): void
    {
        $tipo      = $_GET['tipo'] ?? '';
        $conductor = new Conductor();
        $lista     = $conductor->activosConPosicion($tipo);

        $this->json(['conductores' => $lista]);
    }

    // GET /api/precio?tipo=convencional&km=4.5&pasajeros=1
    private function precio(): void
    {
        $tipo      = $_GET['tipo']      ?? 'convencional';
        $km        = (float)($_GET['km'] ?? 0);
        $pasajeros = max(1, min(4, (int)($_GET['pasajeros'] ?? 1)));

        if (!in_array($tipo, ['convencional', 'vip'], true) || $km <= 0) {
            $this->json(['error' => 'Parámetros inválidos'], 422);
        }

        $tarifa  = new Tarifa();
        $result  = $tarifa->calcular($tipo, $km, $pasajeros);

        $this->json($result);
    }

    // /api/conductor/{estado|posicion}
    private function conductorApi(array $params): void
    {
        if (!Auth::isConductor()) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }

        $sub  = $params[1] ?? '';
        $body = (array)(json_decode(file_get_contents('php://input'), true) ?? []);

        match ($sub) {
            'estado'    => $this->conductorEstado($body),
            'posicion'  => $this->conductorPosicion($body),
            'fcm-token' => $this->conductorFcmToken($body),
            default     => $this->json(['error' => 'Endpoint no encontrado'], 404),
        };
    }

    private function conductorEstado(array $body): void
    {
        $activo = (int)(bool)($body['activo'] ?? 0);
        (new Conductor())->toggleActivo(Auth::conductorId(), $activo);
        $this->json(['ok' => true, 'activo' => $activo]);
    }

    private function conductorPosicion(array $body): void
    {
        $lat = (float)($body['lat'] ?? 0);
        $lng = (float)($body['lng'] ?? 0);

        if ($lat === 0.0 || $lng === 0.0) {
            $this->json(['error' => 'Coordenadas inválidas'], 422);
        }

        (new Conductor())->updatePosicion(Auth::conductorId(), $lat, $lng);
        $this->json(['ok' => true]);
    }

    private function conductorFcmToken(array $body): void
    {
        $token = trim((string)($body['token'] ?? ''));
        if ($token === '') $this->json(['error' => 'Token requerido'], 422);

        (new Conductor())->guardarFcmToken(Auth::conductorId(), $token);
        $this->json(['ok' => true]);
    }

    // GET /api/viaje/{id}  — polling de estado + posición
    private function estadoViaje(int $id): void
    {
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 422);
        }

        $viaje = (new Viaje())->findById($id);

        if (!$viaje) {
            $this->json(['error' => 'Viaje no encontrado'], 404);
        }

        if (Auth::isCliente() && (int)$viaje['cliente_id'] !== Auth::userId()) {
            $this->json(['error' => 'No autorizado'], 403);
        }

        $cLat = $viaje['conductor_lat'] !== null ? (float)$viaje['conductor_lat'] : null;
        $cLng = $viaje['conductor_lng'] !== null ? (float)$viaje['conductor_lng'] : null;

        // ETA: distancia del conductor al origen del viaje ÷ velocidad media urbana (30 km/h)
        $etaMin = null;
        if ($cLat !== null && $cLng !== null && $viaje['estado'] === 'asignado') {
            $dist   = $this->haversine($cLat, $cLng, (float)$viaje['lat_origen'], (float)$viaje['lng_origen']);
            $etaMin = max(1, (int)ceil($dist / 0.5)); // 0.5 km/min ≈ 30 km/h
        }

        $this->json([
            'id'               => $viaje['id'],
            'estado'           => $viaje['estado'],
            'conductor_id'     => $viaje['conductor_id'],
            'conductor_nombre' => $viaje['conductor_nombre'],
            'conductor_tel'    => $viaje['conductor_tel'],
            'conductor_lat'    => $cLat,
            'conductor_lng'    => $cLng,
            'lat_origen'       => (float)$viaje['lat_origen'],
            'lng_origen'       => (float)$viaje['lng_origen'],
            'marca'            => $viaje['marca'],
            'modelo'           => $viaje['modelo'],
            'color'            => $viaje['color'],
            'numero_taxi'      => $viaje['numero_taxi'],
            'eta_min'          => $etaMin,
        ]);
    }

    // ── Chat ─────────────────────────────────────────────────

    private function chatApi(array $params): void
    {
        $viajeId = (int)($params[1] ?? 0);
        if ($viajeId <= 0) $this->json(['error' => 'ID inválido'], 422);

        $viaje = (new Viaje())->findById($viajeId);
        if (!$viaje) $this->json(['error' => 'Viaje no encontrado'], 404);

        // Verificar que el usuario es participante del viaje
        $esCliente   = Auth::isCliente()   && (int)$viaje['cliente_id']   === Auth::userId();
        $esConductor = Auth::isConductor() && (int)$viaje['conductor_id'] === Auth::conductorId();

        if (!$esCliente && !$esConductor) {
            $this->json(['error' => 'No autorizado'], 403);
        }

        $chat = new Chat();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body  = (array)(json_decode(file_get_contents('php://input'), true) ?? []);
            $texto = trim((string)($body['mensaje'] ?? ''));

            if ($texto === '' || mb_strlen($texto) > 500) {
                $this->json(['error' => 'Mensaje inválido'], 422);
            }

            if ($esCliente) {
                $deTipo = 'cliente'; $deId = Auth::userId();
                $paTipo = 'conductor'; $paId = (int)$viaje['conductor_id'];
            } else {
                $deTipo = 'conductor'; $deId = Auth::conductorId();
                $paTipo = 'cliente'; $paId = (int)$viaje['cliente_id'];
            }

            $id = $chat->enviar($viajeId, $deTipo, $deId, $paTipo, $paId, $texto);
            $this->json(['ok' => true, 'id' => $id]);
        }

        // GET — mensajes desde un ID
        $desde  = max(0, (int)($_GET['desde'] ?? 0));
        $paraTipo = $esCliente ? 'cliente' : 'conductor';
        $paraId   = $esCliente ? Auth::userId() : Auth::conductorId();

        $mensajes = $desde > 0
            ? $chat->desde($viajeId, $desde)
            : $chat->mensajesViaje($viajeId);

        $chat->marcarLeidos($viajeId, $paraTipo, $paraId);
        $sinLeer = 0;

        $this->json([
            'mensajes' => $mensajes,
            'sin_leer' => $sinLeer,
            'ultimo_id' => $chat->ultimoId($viajeId),
        ]);
    }

    // ── Cupón ─────────────────────────────────────────────────

    private function cuponApi(): void
    {
        if (!Auth::isCliente()) $this->json(['error' => 'Solo clientes'], 403);

        $codigo = strtoupper(trim($_GET['codigo'] ?? ''));
        if ($codigo === '') $this->json(['error' => 'Código requerido'], 422);

        $cupon = (new Cupon())->validar($codigo, Auth::userId());

        if (!$cupon) {
            $this->json(['error' => 'Cupón inválido, expirado o ya utilizado'], 422);
        }

        $this->json([
            'ok'           => true,
            'cupon_id'     => $cupon['id'],
            'descuento_pct'=> $cupon['descuento_pct'],
        ]);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
