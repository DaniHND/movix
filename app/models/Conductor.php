<?php declare(strict_types=1);

class Conductor extends Model
{
    public function activosConPosicion(string $tipo = ''): array
    {
        $where  = "c.activo = 1 AND c.estado = 'aprobado' AND c.lat_actual IS NOT NULL";
        $params = [];

        if ($tipo !== '') {
            $where         .= " AND c.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $stmt = $this->db->prepare(
            "SELECT c.id, c.nombre, c.tipo, c.foto_perfil,
                    c.lat_actual, c.lng_actual,
                    v.marca, v.modelo, v.color, v.numero_taxi
             FROM conductores c
             LEFT JOIN vehiculos v ON v.conductor_id = c.id
             WHERE $where
             ORDER BY c.ultima_posicion DESC
             LIMIT 50"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM conductores WHERE email = :e");
        $stmt->execute([':e' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM conductores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updatePosicion(int $id, float $lat, float $lng): void
    {
        $stmt = $this->db->prepare(
            "UPDATE conductores
             SET lat_actual = :lat, lng_actual = :lng, ultima_posicion = NOW()
             WHERE id = :id"
        );
        $stmt->execute([':lat' => $lat, ':lng' => $lng, ':id' => $id]);
    }

    public function crear(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO conductores
             (nombre, fecha_nacimiento, identidad, telefono, email, password, tipo,
              foto_perfil, foto_identidad_frente, foto_identidad_reverso,
              foto_licencia_frente, foto_licencia_reverso,
              estado, email_verificado)
             VALUES (:nombre, :fecha_nacimiento, :identidad, :telefono, :email, :password, :tipo,
                     :foto_perfil, :foto_identidad_frente, :foto_identidad_reverso,
                     :foto_licencia_frente, :foto_licencia_reverso,
                     'pendiente', 1)"
        );
        $stmt->execute([
            ':nombre'                 => $data['nombre'],
            ':fecha_nacimiento'       => $data['fecha_nacimiento'],
            ':identidad'              => $data['identidad'],
            ':telefono'               => $data['telefono'],
            ':email'                  => $data['email'],
            ':password'               => $data['password'],
            ':tipo'                   => $data['tipo'],
            ':foto_perfil'            => $data['foto_perfil']            ?? null,
            ':foto_identidad_frente'  => $data['foto_identidad_frente']  ?? null,
            ':foto_identidad_reverso' => $data['foto_identidad_reverso'] ?? null,
            ':foto_licencia_frente'   => $data['foto_licencia_frente']   ?? null,
            ':foto_licencia_reverso'  => $data['foto_licencia_reverso']  ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByIdentidad(string $identidad): array|false
    {
        $stmt = $this->db->prepare("SELECT id FROM conductores WHERE identidad = ?");
        $stmt->execute([$identidad]);
        return $stmt->fetch();
    }

    public function findByIdWithVehicle(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    v.id AS vehiculo_id, v.marca, v.modelo, v.anio,
                    v.placa, v.color, v.numero_taxi,
                    v.foto_frente AS veh_foto_frente,
                    v.foto_atras  AS veh_foto_atras
             FROM conductores c
             LEFT JOIN vehiculos v ON v.conductor_id = c.id
             WHERE c.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function toggleActivo(int $id, int $activo): void
    {
        $this->db->prepare("UPDATE conductores SET activo = :a WHERE id = :id")
                 ->execute([':a' => $activo, ':id' => $id]);
    }

    public function actualizarTelefono(int $id, string $telefono): void
    {
        $this->db->prepare("UPDATE conductores SET telefono = ? WHERE id = ?")
                 ->execute([$telefono, $id]);
    }

    public function guardarFcmToken(int $id, string $token): void
    {
        $this->db->prepare("UPDATE conductores SET fcm_token = ? WHERE id = ?")
                 ->execute([$token, $id]);
    }

    /**
     * Encuentra el conductor aprobado, activo y sin viaje en curso más cercano
     * al punto dado, usando la fórmula de Haversine en SQL.
     * Retorna false si nadie está dentro del radio (km).
     */
    public function findNearestAvailable(float $lat, float $lng, string $tipo, float $radius = 10.0): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.id, c.nombre, c.telefono, c.foto_perfil,
                    c.lat_actual, c.lng_actual,
                    v.marca, v.modelo, v.color, v.numero_taxi,
                    (6371 * ACOS(LEAST(1.0, GREATEST(-1.0,
                        COS(RADIANS(:lat_a)) * COS(RADIANS(c.lat_actual))
                        * COS(RADIANS(c.lng_actual) - RADIANS(:lng))
                        + SIN(RADIANS(:lat_b)) * SIN(RADIANS(c.lat_actual))
                    )))) AS distancia_km
             FROM conductores c
             LEFT JOIN vehiculos v ON v.conductor_id = c.id
             WHERE c.activo    = 1
               AND c.estado    = 'aprobado'
               AND c.tipo      = :tipo
               AND c.lat_actual IS NOT NULL
               AND NOT EXISTS (
                   SELECT 1 FROM viajes
                   WHERE conductor_id = c.id
                     AND estado IN ('asignado','en_curso')
               )
             HAVING distancia_km <= :radius
             ORDER BY distancia_km ASC
             LIMIT 1"
        );
        $stmt->execute([
            ':lat_a'  => $lat,
            ':lat_b'  => $lat,
            ':lng'    => $lng,
            ':tipo'   => $tipo,
            ':radius' => $radius,
        ]);
        return $stmt->fetch();
    }
}
