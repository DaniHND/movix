<?php
/** @var array $viajes */
/** @var int $total */
/** @var int $pagina */
/** @var int $totalPags */

$etiquetas = [
    'completado' => ['label' => 'Completado', 'cls' => 'badge--success'],
    'cancelado'  => ['label' => 'Cancelado',  'cls' => 'badge--danger'],
];
?>

<div class="page-header">
    <h1>Historial de viajes</h1>
</div>

<div class="history-list">
    <?php if (empty($viajes)): ?>
    <div class="empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        <p>Aún no tienes viajes registrados.</p>
        <a href="<?= BASE_URL ?>/cliente" class="btn-primary" style="margin-top:var(--space-2)">
            Solicitar mi primer viaje
        </a>
    </div>
    <?php else: ?>
    <?php foreach ($viajes as $v):
        $badge = $etiquetas[$v['estado']] ?? ['label' => $v['estado'], 'cls' => 'badge--warning'];
        $fecha = new \DateTime($v['fecha_solicitud']);
    ?>
    <div class="history-card">
        <div class="history-card-header">
            <time class="history-date" datetime="<?= $v['fecha_solicitud'] ?>">
                <?= $fecha->format('d/m/Y H:i') ?>
            </time>
            <span class="estado-badge <?= $badge['cls'] ?>"><?= $badge['label'] ?></span>
        </div>

        <div class="history-route">
            <div class="history-route-row">
                <span class="route-dot route-dot--origen"></span>
                <span><?= htmlspecialchars($v['direccion_origen'] ?: 'Origen') ?></span>
            </div>
            <div class="history-route-row history-route-row--dest">
                <span class="route-dot route-dot--destino"></span>
                <span><?= htmlspecialchars($v['direccion_destino'] ?: 'Destino') ?></span>
            </div>
        </div>

        <div class="history-card-footer">
            <div class="history-meta">
                <span class="history-tipo"><?= ucfirst($v['tipo_servicio']) ?></span>
                <?php if (!empty($v['conductor_nombre'])): ?>
                <span class="history-conductor">· <?= htmlspecialchars($v['conductor_nombre']) ?></span>
                <?php endif; ?>
                <?php if ($v['distancia_km']): ?>
                <span class="history-dist">· <?= number_format((float)$v['distancia_km'], 1) ?> km</span>
                <?php endif; ?>
            </div>
            <span class="history-price">L. <?= number_format((float)$v['precio_total'], 2) ?></span>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Paginación -->
    <?php if ($totalPags > 1): ?>
    <div class="pagination">
        <?php if ($pagina > 1): ?>
        <a href="<?= BASE_URL ?>/cliente/historial?pagina=<?= $pagina - 1 ?>" class="page-btn">← Anterior</a>
        <?php endif; ?>
        <span class="page-info"><?= $pagina ?> / <?= $totalPags ?></span>
        <?php if ($pagina < $totalPags): ?>
        <a href="<?= BASE_URL ?>/cliente/historial?pagina=<?= $pagina + 1 ?>" class="page-btn">Siguiente →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
