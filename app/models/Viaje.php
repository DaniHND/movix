<?php declare(strict_types=1);

class Viaje extends Model
{
    public function crear(array $d): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO viajes
             (cliente_id, tipo_servicio, num_pasajeros, ida_y_regreso,
              lat_origen, lng_origen, direccion_origen,
              lat_destino, lng_destino, direccion_destino,
              distancia_km, duracion_min, horario,
              precio_base, comision_app, precio_total, estado)
             VALUES
             (:cliente_id, :tipo_servicio, :num_pasajeros, :ida_y_regreso,
              :lat_origen, :lng_origen, :direccion_origen,
              :lat_destino, :lng_destino, :direccion_destino,
              :distancia_km, :duracion_min, :horario,
              :precio_base, :comision_app, :precio_total, 'pendiente')"
        );
        $stmt->execute($d);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT v.*,
                    c.nombre        AS conductor_nombre,
                    c.foto_perfil,
                    c.telefono      AS conductor_tel,
                    c.lat_actual    AS conductor_lat,
                    c.lng_actual    AS conductor_lng,
                    vh.marca, vh.modelo, vh.color, vh.anio, vh.numero_taxi
             FROM viajes v
             LEFT JOIN conductores c  ON c.id  = v.conductor_id
             LEFT JOIN vehiculos   vh ON vh.conductor_id = v.conductor_id
             WHERE v.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function activoCliente(int $clienteId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT v.*,
                    c.nombre    AS conductor_nombre,
                    c.foto_perfil,
                    c.telefono  AS conductor_tel,
                    vh.marca, vh.modelo, vh.color, vh.numero_taxi
             FROM viajes v
             LEFT JOIN conductores c  ON c.id  = v.conductor_id
             LEFT JOIN vehiculos   vh ON vh.conductor_id = v.conductor_id
             WHERE v.cliente_id = :cid
               AND v.estado IN ('pendiente','asignado','en_curso')
             ORDER BY v.fecha_solicitud DESC LIMIT 1"
        );
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetch();
    }

    public function historial(int $clienteId, int $lim = 20, int $off = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, c.nombre AS conductor_nombre
             FROM viajes v
             LEFT JOIN conductores c ON c.id = v.conductor_id
             WHERE v.cliente_id = :cid
               AND v.estado IN ('completado','cancelado')
             ORDER BY v.fecha_solicitud DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':cid', $clienteId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $lim,       \PDO::PARAM_INT);
        $stmt->bindValue(':off', $off,       \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalHistorial(int $clienteId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM viajes
             WHERE cliente_id = :cid AND estado IN ('completado','cancelado')"
        );
        $stmt->execute([':cid' => $clienteId]);
        return (int)$stmt->fetchColumn();
    }

    public function activoConductor(int $conductorId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nombre AS cliente_nombre, u.telefono AS cliente_tel
             FROM viajes v
             LEFT JOIN usuarios u ON u.id = v.cliente_id
             WHERE v.conductor_id = :cid
               AND v.estado IN ('asignado','en_curso')
             ORDER BY v.fecha_asignacion DESC LIMIT 1"
        );
        $stmt->execute([':cid' => $conductorId]);
        return $stmt->fetch();
    }

    public function pendientes(int $lim = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nombre AS cliente_nombre
             FROM viajes v
             LEFT JOIN usuarios u ON u.id = v.cliente_id
             WHERE v.estado = 'pendiente'
             ORDER BY v.fecha_solicitud ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $lim, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function tomarViaje(int $id, int $conductorId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE viajes
             SET estado = 'asignado', conductor_id = :cid, fecha_asignacion = NOW()
             WHERE id = :id AND estado = 'pendiente'"
        );
        $stmt->execute([':cid' => $conductorId, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function iniciar(int $id, int $conductorId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE viajes
             SET estado = 'en_curso', fecha_inicio = NOW()
             WHERE id = :id AND conductor_id = :cid AND estado = 'asignado'"
        );
        $stmt->execute([':id' => $id, ':cid' => $conductorId]);
        return $stmt->rowCount() > 0;
    }

    public function completar(int $id, int $conductorId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE viajes
             SET estado = 'completado', fecha_fin = NOW()
             WHERE id = :id AND conductor_id = :cid AND estado = 'en_curso'"
        );
        $stmt->execute([':id' => $id, ':cid' => $conductorId]);
        return $stmt->rowCount() > 0;
    }

    public function historialConductor(int $conductorId, int $lim = 20, int $off = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nombre AS cliente_nombre
             FROM viajes v
             LEFT JOIN usuarios u ON u.id = v.cliente_id
             WHERE v.conductor_id = :cid
               AND v.estado IN ('completado','cancelado')
             ORDER BY v.fecha_solicitud DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':cid', $conductorId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $lim,         PDO::PARAM_INT);
        $stmt->bindValue(':off', $off,          PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalHistorialConductor(int $conductorId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM viajes
             WHERE conductor_id = :cid AND estado IN ('completado','cancelado')"
        );
        $stmt->execute([':cid' => $conductorId]);
        return (int)$stmt->fetchColumn();
    }

    public function listarAdmin(int $lim = 20, int $off = 0, string $estado = ''): array
    {
        $where = $estado !== '' ? 'WHERE v.estado = :estado' : '';

        $stmt = $this->db->prepare(
            "SELECT v.id, v.tipo_servicio, v.estado, v.precio_total, v.comision_app,
                    v.fecha_solicitud, v.distancia_km,
                    v.direccion_origen, v.direccion_destino,
                    u.nombre AS cliente_nombre,
                    c.nombre AS conductor_nombre
             FROM viajes v
             LEFT JOIN usuarios    u ON u.id = v.cliente_id
             LEFT JOIN conductores c ON c.id = v.conductor_id
             $where
             ORDER BY v.fecha_solicitud DESC
             LIMIT :lim OFFSET :off"
        );
        if ($estado !== '') $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':lim', $lim, PDO::PARAM_INT);
        $stmt->bindValue(':off',  $off, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalAdmin(string $estado = ''): int
    {
        if ($estado !== '') {
            $s = $this->db->prepare("SELECT COUNT(*) FROM viajes WHERE estado = :e");
            $s->execute([':e' => $estado]);
        } else {
            $s = $this->db->query("SELECT COUNT(*) FROM viajes");
        }
        return (int)$s->fetchColumn();
    }

    public function cancelar(int $id, int $clienteId, string $motivo = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE viajes SET estado = 'cancelado', motivo_cancelacion = :motivo
             WHERE id = :id AND cliente_id = :cid
               AND estado IN ('pendiente','asignado')"
        );
        $stmt->execute([':id' => $id, ':cid' => $clienteId, ':motivo' => $motivo]);
        return $stmt->rowCount() > 0;
    }
}
