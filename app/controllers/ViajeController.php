<?php declare(strict_types=1);

class ViajeController extends Controller
{
    public function dispatch(array $params = []): void
    {
        // Fase 5: motor de asignación, GPS en tiempo real, seguimiento end-to-end
        $this->json(['error' => 'Módulo de viajes — Fase 5'], 501);
    }
}
