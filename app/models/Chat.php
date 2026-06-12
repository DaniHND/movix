<?php declare(strict_types=1);

class Chat extends Model
{
    public function mensajesViaje(int $viajeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, de_tipo, de_id, mensaje, leido,
                    DATE_FORMAT(created_at, '%H:%i') AS hora
             FROM mensajes_chat
             WHERE viaje_id = :vid
             ORDER BY id ASC
             LIMIT 200"
        );
        $stmt->execute([':vid' => $viajeId]);
        return $stmt->fetchAll();
    }

    public function enviar(int $viajeId, string $deTipo, int $deId, string $paraTipo, int $paraId, string $texto): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mensajes_chat
             (viaje_id, de_tipo, de_id, para_tipo, para_id, mensaje)
             VALUES (:vid, :dt, :did, :pt, :pid, :msg)"
        );
        $stmt->execute([
            ':vid' => $viajeId,
            ':dt'  => $deTipo,
            ':did' => $deId,
            ':pt'  => $paraTipo,
            ':pid' => $paraId,
            ':msg' => $texto,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function marcarLeidos(int $viajeId, string $paraTipo, int $paraId): void
    {
        $this->db->prepare(
            "UPDATE mensajes_chat
             SET leido = 1
             WHERE viaje_id = :vid AND para_tipo = :pt AND para_id = :pid AND leido = 0"
        )->execute([':vid' => $viajeId, ':pt' => $paraTipo, ':pid' => $paraId]);
    }

    public function sinLeer(int $viajeId, string $paraTipo, int $paraId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM mensajes_chat
             WHERE viaje_id = :vid AND para_tipo = :pt AND para_id = :pid AND leido = 0"
        );
        $stmt->execute([':vid' => $viajeId, ':pt' => $paraTipo, ':pid' => $paraId]);
        return (int)$stmt->fetchColumn();
    }

    /** Último ID de mensaje en el viaje (para polling incremental) */
    public function ultimoId(int $viajeId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(id), 0) FROM mensajes_chat WHERE viaje_id = :vid"
        );
        $stmt->execute([':vid' => $viajeId]);
        return (int)$stmt->fetchColumn();
    }

    /** Mensajes nuevos desde un ID dado */
    public function desde(int $viajeId, int $desdeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, de_tipo, de_id, mensaje, leido,
                    DATE_FORMAT(created_at, '%H:%i') AS hora
             FROM mensajes_chat
             WHERE viaje_id = :vid AND id > :desde
             ORDER BY id ASC
             LIMIT 50"
        );
        $stmt->execute([':vid' => $viajeId, ':desde' => $desdeId]);
        return $stmt->fetchAll();
    }
}
