<?php declare(strict_types=1);

class Admin extends Model
{
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE email = :e");
        $stmt->execute([':e' => $email]);
        return $stmt->fetch();
    }

    public function statsGlobales(): array
    {
        $hoy = date('Y-m-d');
        $mes  = date('Y-m');

        $fetch = function (string $sql, array $params = []): mixed {
            $s = $this->db->prepare($sql);
            $s->execute($params);
            return $s->fetchColumn();
        };

        return [
            'viajes_hoy'             => (int)   $fetch("SELECT COUNT(*) FROM viajes WHERE DATE(fecha_solicitud) = ?", [$hoy]),
            'viajes_mes'             => (int)   $fetch("SELECT COUNT(*) FROM viajes WHERE DATE_FORMAT(fecha_solicitud,'%Y-%m') = ?", [$mes]),
            'ingresos_hoy'           => (float) $fetch("SELECT COALESCE(SUM(comision_app),0) FROM viajes WHERE estado = 'completado' AND DATE(fecha_solicitud) = ?", [$hoy]),
            'ingresos_mes'           => (float) $fetch("SELECT COALESCE(SUM(comision_app),0) FROM viajes WHERE estado = 'completado' AND DATE_FORMAT(fecha_solicitud,'%Y-%m') = ?", [$mes]),
            'conductores_activos'    => (int)   $fetch("SELECT COUNT(*) FROM conductores WHERE estado = 'aprobado' AND activo = 1"),
            'conductores_pendientes' => (int)   $fetch("SELECT COUNT(*) FROM conductores WHERE estado = 'pendiente'"),
            'total_clientes'         => (int)   $fetch("SELECT COUNT(*) FROM usuarios"),
            'viajes_pendientes'      => (int)   $fetch("SELECT COUNT(*) FROM viajes WHERE estado = 'pendiente'"),
        ];
    }

    public function conductoresLista(string $estado = '', int $lim = 20, int $off = 0): array
    {
        $where = $estado !== '' ? 'WHERE c.estado = :estado' : '';

        $stmt = $this->db->prepare(
            "SELECT c.id, c.nombre, c.email, c.telefono, c.tipo,
                    c.estado, c.activo, c.created_at,
                    v.marca, v.modelo, v.placa, v.color
             FROM conductores c
             LEFT JOIN vehiculos v ON v.conductor_id = c.id
             $where
             ORDER BY c.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        if ($estado !== '') $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':lim', $lim, PDO::PARAM_INT);
        $stmt->bindValue(':off',  $off, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalConductores(string $estado = ''): int
    {
        if ($estado !== '') {
            $s = $this->db->prepare("SELECT COUNT(*) FROM conductores WHERE estado = :e");
            $s->execute([':e' => $estado]);
        } else {
            $s = $this->db->query("SELECT COUNT(*) FROM conductores");
        }
        return (int)$s->fetchColumn();
    }

    public function conductorPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    v.marca, v.modelo, v.anio, v.placa, v.color,
                    v.polarizado, v.numero_taxi,
                    v.foto_frente, v.foto_atras
             FROM conductores c
             LEFT JOIN vehiculos v ON v.conductor_id = c.id
             WHERE c.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function aprobar(int $id): void
    {
        $this->db->prepare("UPDATE conductores SET estado = 'aprobado' WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    public function rechazar(int $id): void
    {
        $this->db->prepare("UPDATE conductores SET estado = 'rechazado' WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    public function suspender(int $id): void
    {
        $this->db->prepare("UPDATE conductores SET estado = 'suspendido', activo = 0 WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    public function clientesLista(int $lim = 20, int $off = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre, u.email, u.telefono,
                    u.email_verificado, u.created_at,
                    COUNT(v.id) AS total_viajes
             FROM usuarios u
             LEFT JOIN viajes v ON v.cliente_id = u.id
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $lim, PDO::PARAM_INT);
        $stmt->bindValue(':off',  $off, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalClientes(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }

    /** Estadísticas diarias de los últimos N días para las gráficas del dashboard. */
    public function statsUltimos30Dias(int $dias = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                DATE(fecha_solicitud)                                    AS fecha,
                COUNT(*)                                                 AS total_viajes,
                COALESCE(SUM(CASE WHEN estado = 'completado' THEN comision_app ELSE 0 END), 0) AS ingresos
             FROM viajes
             WHERE fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
             GROUP BY DATE(fecha_solicitud)
             ORDER BY fecha ASC"
        );
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Rellenar días sin datos con ceros
        $mapa = [];
        foreach ($rows as $r) $mapa[$r['fecha']] = $r;

        $resultado = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $resultado[] = $mapa[$fecha] ?? ['fecha' => $fecha, 'total_viajes' => 0, 'ingresos' => 0.0];
        }
        return $resultado;
    }
}
