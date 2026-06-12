<div class="page-header">
    <h2>Conductores</h2>
    <p><?= $total ?> registrado<?= $total !== 1 ? 's' : '' ?></p>
</div>

<div class="filter-tabs">
    <?php
    $filtros = [
        ''           => 'Todos',
        'pendiente'  => 'Pendientes',
        'aprobado'   => 'Aprobados',
        'rechazado'  => 'Rechazados',
        'suspendido' => 'Suspendidos',
    ];
    foreach ($filtros as $val => $label):
        $qs = $val !== '' ? '?estado=' . $val : '';
    ?>
    <a href="<?= BASE_URL ?>/admin/conductores<?= $qs ?>"
       class="filter-tab <?= $filtroEstado === $val ? 'active' : '' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Contacto</th>
                <th class="hide-mobile">Tipo</th>
                <th>Estado</th>
                <th class="hide-mobile">Vehículo</th>
                <th class="hide-mobile">Registro</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($lista)): ?>
            <tr><td colspan="7" class="empty-state">No hay conductores con este filtro.</td></tr>
        <?php else: ?>
            <?php foreach ($lista as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                <td>
                    <div><?= htmlspecialchars($c['email']) ?></div>
                    <div class="td-muted"><?= htmlspecialchars($c['telefono']) ?></div>
                </td>
                <td class="hide-mobile">
                    <span class="badge <?= $c['tipo'] === 'vip' ? 'badge--vip' : 'badge--conv' ?>">
                        <?= ucfirst($c['tipo']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge--<?= $c['estado'] ?>">
                        <?= ucfirst($c['estado']) ?>
                    </span>
                </td>
                <td class="hide-mobile td-muted">
                    <?php if (!empty($c['marca'])): ?>
                        <?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?>
                        <?php if (!empty($c['placa'])): ?>
                            <br><span>(<?= htmlspecialchars($c['placa']) ?>)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td class="hide-mobile td-muted">
                    <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/conductores/ver/<?= (int)$c['id'] ?>"
                       class="btn btn-ghost btn-sm">
                        Ver
                    </a>
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
