<div class="page-header">
    <h2>Dashboard</h2>
    <p><?= (new DateTime('now', new DateTimeZone('America/Tegucigalpa')))->format('l, d \d\e F Y') ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-card__label">Viajes hoy</span>
        <span class="stat-card__value"><?= $stats['viajes_hoy'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Viajes este mes</span>
        <span class="stat-card__value"><?= $stats['viajes_mes'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Ingresos hoy</span>
        <span class="stat-card__value stat-card__value--success">
            L. <?= number_format($stats['ingresos_hoy'], 2) ?>
        </span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Ingresos este mes</span>
        <span class="stat-card__value stat-card__value--success">
            L. <?= number_format($stats['ingresos_mes'], 2) ?>
        </span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Conductores activos</span>
        <span class="stat-card__value stat-card__value--accent"><?= $stats['conductores_activos'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Conductores pendientes</span>
        <span class="stat-card__value <?= $stats['conductores_pendientes'] > 0 ? 'stat-card__value--warning' : '' ?>">
            <?= $stats['conductores_pendientes'] ?>
        </span>
        <?php if ($stats['conductores_pendientes'] > 0): ?>
        <a href="<?= BASE_URL ?>/admin/conductores?estado=pendiente" class="stat-card__link">
            Revisar →
        </a>
        <?php endif; ?>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Clientes registrados</span>
        <span class="stat-card__value"><?= $stats['total_clientes'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Viajes sin conductor</span>
        <span class="stat-card__value <?= $stats['viajes_pendientes'] > 0 ? 'stat-card__value--warning' : '' ?>">
            <?= $stats['viajes_pendientes'] ?>
        </span>
        <?php if ($stats['viajes_pendientes'] > 0): ?>
        <a href="<?= BASE_URL ?>/admin/viajes?estado=pendiente" class="stat-card__link">
            Ver viajes →
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Gráficas 30 días ─────────────────────────── -->
<div class="charts-section">
    <div class="chart-card">
        <h3 class="chart-title">Viajes — últimos 30 días</h3>
        <canvas id="chart-viajes" height="120"></canvas>
    </div>
    <div class="chart-card">
        <h3 class="chart-title">Ingresos (L.) — últimos 30 días</h3>
        <canvas id="chart-ingresos" height="120"></canvas>
    </div>
</div>

<script>
window.MOVIX_STATS = <?= json_encode(array_map(fn($r) => [
    'fecha'        => substr($r['fecha'], 5),
    'total_viajes' => (int)$r['total_viajes'],
    'ingresos'     => round((float)$r['ingresos'], 2),
], $dias30)) ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/charts.js"></script>
