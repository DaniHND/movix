<div class="page-header-conductor">
    <h2>Historial de viajes</h2>
    <p><?= $total ?> viaje<?= $total !== 1 ? 's' : '' ?> completados</p>
</div>

<?php if (empty($viajes)): ?>
<div class="empty-conductor">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-muted)" stroke-width="1.2">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
    </svg>
    <p>No tienes viajes en el historial aún.</p>
</div>
<?php else: ?>

<div class="historial-list">
    <?php foreach ($viajes as $v): ?>
    <div class="historial-item">
        <div class="historial-item-header">
            <span class="hi-estado <?= $v['estado'] === 'completado' ? 'hi-estado--ok' : 'hi-estado--cancel' ?>">
                <?= ucfirst($v['estado']) ?>
            </span>
            <span class="hi-precio">L. <?= number_format((float)$v['precio_total'], 2) ?></span>
        </div>

        <div class="hi-route">
            <div class="route-row">
                <span class="route-dot route-dot--origen"></span>
                <span class="route-text">
                    <?= htmlspecialchars($v['direccion_origen'] ?: 'Origen') ?>
                </span>
            </div>
            <div class="route-connector-small"></div>
            <div class="route-row">
                <span class="route-dot route-dot--destino"></span>
                <span class="route-text">
                    <?= htmlspecialchars($v['direccion_destino'] ?: 'Destino') ?>
                </span>
            </div>
        </div>

        <div class="hi-meta">
            <span><?= date('d/m/Y H:i', strtotime($v['fecha_solicitud'])) ?></span>
            <span>·</span>
            <span><?= number_format((float)$v['distancia_km'], 1) ?> km</span>
            <span>·</span>
            <span><?= ucfirst($v['tipo_servicio']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPags > 1): ?>
<div class="paginacion-conductor">
    <?php for ($i = 1; $i <= $totalPags; $i++): ?>
    <a href="?pagina=<?= $i ?>"
       class="pag-link <?= $i === $pagina ? 'active' : '' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>
