<div class="page-header">
    <h2>Clientes</h2>
    <p><?= $total ?> registrado<?= $total !== 1 ? 's' : '' ?></p>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th class="hide-mobile">Teléfono</th>
                <th class="hide-mobile">Verificado</th>
                <th>Viajes</th>
                <th class="hide-mobile">Registro</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($lista)): ?>
            <tr><td colspan="6" class="empty-state">No hay clientes registrados.</td></tr>
        <?php else: ?>
            <?php foreach ($lista as $u): ?>
            <tr>
                <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td class="hide-mobile td-muted"><?= htmlspecialchars($u['telefono']) ?></td>
                <td class="hide-mobile">
                    <?php if ($u['email_verificado']): ?>
                        <span class="badge badge--aprobado">Verificado</span>
                    <?php else: ?>
                        <span class="badge badge--pendiente">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td><?= (int)$u['total_viajes'] ?></td>
                <td class="hide-mobile td-muted">
                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
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
            <a href="?pagina=<?= $i ?>"
               class="pagination-link <?= $i === $pagina ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
