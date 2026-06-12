<div class="page-header">
    <h2>Viajes</h2>
    <p><?= $total ?> en total</p>
</div>

<div class="filter-tabs">
    <?php
    $filtros = [
        ''           => 'Todos',
        'pendiente'  => 'Pendientes',
        'asignado'   => 'Asignados',
        'en_curso'   => 'En curso',
        'completado' => 'Completados',
        'cancelado'  => 'Cancelados',
    ];
    foreach ($filtros as $val => $label):
        $qs = $val !== '' ? '?estado=' . $val : '';
    ?>
    <a href="<?= BASE_URL ?>/admin/viajes<?= $qs ?>"
       class="filter-tab <?= $filtroEstado === $val ? 'active' : '' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th class="hide-mobile">Conductor</th>
                <th class="hide-mobile">Tipo</th>
                <th>Estado</th>
                <th>Total</th>
                <th class="hide-mobile">Comisión</th>
                <th class="hide-mobile">Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($lista)): ?>
            <tr><td colspan="8" class="empty-state">No hay viajes con este filtro.</td></tr>
        <?php else: ?>
            <?php foreach ($lista as $v): ?>
            <tr>
                <td class="td-muted td-mono">#<?= (int)$v['id'] ?></td>
                <td><?= htmlspecialchars($v['cliente_nombre'] ?? '—') ?></td>
                <td class="hide-mobile"><?= htmlspecialchars($v['conductor_nombre'] ?? '—') ?></td>
                <td class="hide-mobile">
                    <span class="badge <?= $v['tipo_servicio'] === 'vip' ? 'badge--vip' : 'badge--conv' ?>">
                        <?= ucfirst($v['tipo_servicio']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge--<?= $v['estado'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $v['estado'])) ?>
                    </span>
                </td>
                <td><strong>L. <?= number_format((float)$v['precio_total'], 2) ?></strong></td>
                <td class="hide-mobile td-muted">L. <?= number_format((float)$v['comision_app'], 2) ?></td>
                <td class="hide-mobile td-muted">
                    <?= date('d/m/Y H:i', strtotime($v['fecha_solicitud'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPags > 1): ?>
    <div class="pagination">
        <span>Página <?= $pagina ?> de <?= $totalPags ?></span>
        <div class="pagination-links">
            <?php for ($i = 1; $i <= $totalPags; $i++): ?>
            <a href="?estado=<?= htmlspecialchars($filtroEstado) ?>&pagina=<?= $i ?>"
               class="pagination-link <?= $i === $pagina ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
