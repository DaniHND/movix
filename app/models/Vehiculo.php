<?php declare(strict_types=1);

class Vehiculo extends Model
{
    public function crear(int $conductorId, array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO vehiculos
             (conductor_id, tipo, marca, modelo, anio, placa, color, numero_taxi,
              foto_frente, foto_atras)
             VALUES (:conductor_id, :tipo, :marca, :modelo, :anio, :placa, :color, :numero_taxi,
                     :foto_frente, :foto_atras)"
        );
        $stmt->execute([
            ':conductor_id' => $conductorId,
            ':tipo'         => $data['tipo'],
            ':marca'        => $data['marca'],
            ':modelo'       => $data['modelo'],
            ':anio'         => $data['anio'],
            ':placa'        => $data['placa'],
            ':color'        => $data['color'],
            ':numero_taxi'  => ($data['numero_taxi'] ?? '') !== '' ? $data['numero_taxi'] : null,
            ':foto_frente'  => $data['foto_frente'] ?? null,
            ':foto_atras'   => $data['foto_atras']  ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByPlaca(string $placa): array|false
    {
        $stmt = $this->db->prepare("SELECT id FROM vehiculos WHERE placa = ?");
        $stmt->execute([$placa]);
        return $stmt->fetch();
    }
}
