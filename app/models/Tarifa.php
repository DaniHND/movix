<?php declare(strict_types=1);

class Tarifa extends Model
{
    public function calcular(string $tipo, float $distanciaKm, int $pasajeros = 1): array
    {
        $horario = $this->horarioActual();

        $stmt = $this->db->prepare(
            "SELECT * FROM tarifas
             WHERE tipo_servicio = :tipo AND horario = :horario
               AND :km BETWEEN km_desde AND km_hasta
             LIMIT 1"
        );
        $stmt->execute([':tipo' => $tipo, ':horario' => $horario, ':km' => $distanciaKm]);
        $t = $stmt->fetch();

        if (!$t) {
            return ['error' => 'Tarifa no encontrada', 'precio_total' => 0.0];
        }

        $precio = $t['es_por_km']
            ? (float)$t['precio_base'] * $distanciaKm
            : (float)$t['precio_base'];

        if ($pasajeros > 1) {
            $precio += ($pasajeros - 1) * (float)$t['precio_pasajero_extra'];
        }

        $precio    = round($precio, 2);
        $comision  = round((float)$t['comision_fija'], 2);

        return [
            'tipo'         => $tipo,
            'horario'      => $horario,
            'distancia_km' => $distanciaKm,
            'pasajeros'    => $pasajeros,
            'precio_base'  => $precio,
            'comision_app' => $comision,
            'precio_total' => round($precio + $comision, 2),
        ];
    }

    public function horarioActual(): string
    {
        $hora = (int)(new \DateTime('now', new \DateTimeZone('America/Tegucigalpa')))->format('H');
        return ($hora >= 4 && $hora < 21) ? 'dia' : 'noche';
    }

    public function todas(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM tarifas ORDER BY tipo_servicio, horario, km_desde"
        );
        return $stmt->fetchAll();
    }

    public function actualizar(int $id, float $precioBase, float $pasajeroExtra, float $comisionFija): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tarifas
             SET precio_base = :pb, precio_pasajero_extra = :pe, comision_fija = :cf
             WHERE id = :id"
        );
        $stmt->execute([':pb' => $precioBase, ':pe' => $pasajeroExtra, ':cf' => $comisionFija, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
