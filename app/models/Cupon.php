<?php declare(strict_types=1);

class Cupon extends Model
{
    /**
     * Valida un cupón para un cliente dado.
     * Retorna el array del cupón si es válido, false si no.
     */
    public function validar(string $codigo, int $clienteId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.*
             FROM cupones c
             WHERE c.codigo  = :codigo
               AND c.activo  = 1
               AND (c.vence_at IS NULL OR c.vence_at > NOW())
               AND c.usos_actuales < c.usos_max
               AND NOT EXISTS (
                   SELECT 1 FROM cupones_uso cu
                   WHERE cu.cupon_id  = c.id
                     AND cu.cliente_id = :cid
               )
             LIMIT 1"
        );
        $stmt->execute([':codigo' => $codigo, ':cid' => $clienteId]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM cupones WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Registra el uso de un cupón por un cliente/viaje e incrementa contador.
     */
    public function aplicar(int $cuponId, int $clienteId, int $viajeId): void
    {
        $this->db->prepare(
            "INSERT INTO cupones_uso (cupon_id, cliente_id, viaje_id) VALUES (:cid, :uid, :vid)"
        )->execute([':cid' => $cuponId, ':uid' => $clienteId, ':vid' => $viajeId]);

        $this->db->prepare(
            "UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE id = :id"
        )->execute([':id' => $cuponId]);
    }

    public function listar(int $lim = 20, int $off = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM cupones_uso WHERE cupon_id = c.id) AS total_usos
             FROM cupones c
             ORDER BY c.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $lim, PDO::PARAM_INT);
        $stmt->bindValue(':off', $off, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function total(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM cupones")->fetchColumn();
    }

    public function crear(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cupones (codigo, descuento_pct, usos_max, vence_at, activo)
             VALUES (:codigo, :pct, :max, :vence, 1)"
        );
        $stmt->execute([
            ':codigo' => strtoupper(trim($data['codigo'])),
            ':pct'    => (int)$data['descuento_pct'],
            ':max'    => (int)$data['usos_max'],
            ':vence'  => $data['vence_at'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function toggleActivo(int $id, int $activo): void
    {
        $this->db->prepare("UPDATE cupones SET activo = :a WHERE id = :id")
                 ->execute([':a' => $activo, ':id' => $id]);
    }

    public function findByCodigo(string $codigo): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM cupones WHERE codigo = :c");
        $stmt->execute([':c' => strtoupper(trim($codigo))]);
        return $stmt->fetch();
    }
}
