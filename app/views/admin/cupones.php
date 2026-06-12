<div class="page-header">
    <div>
        <h2>Cupones de descuento</h2>
        <p><?= $total ?> cupón<?= $total !== 1 ? 'es' : '' ?> en total</p>
    </div>
    <button class="btn-admin btn-admin--primary" onclick="document.getElementById('form-nuevo').classList.toggle('hidden')">
        + Nuevo cupón
    </button>
</div>

<?php if ($okMsg): ?>
<div class="alert alert-success"><?= htmlspecialchars($okMsg) ?></div>
<?php endif; ?>
<?php if ($errMsg): ?>
<div class="alert alert-danger"><?= htmlspecialchars($errMsg) ?></div>
<?php endif; ?>

<!-- Formulario nuevo cupón -->
<div id="form-nuevo" class="cupon-form-panel hidden">
    <form method="POST" action="<?= BASE_URL ?>/admin/cupones/crear">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="cupon-form-grid">
            <div class="form-group">
                <label class="form-label">Código</label>
                <input type="text" name="codigo" class="input-field" placeholder="VERANO2025"
                       required maxlength="30" style="text-transform:uppercase">
            </div>
            <div class="form-group">
                <label class="form-label">Descuento %</label>
                <input type="number" name="descuento_pct" class="input-field"
                       min="1" max="100" placeholder="20" required>
            </div>
            <div class="form-group">
                <label class="form-label">Usos máximos</label>
                <input type="number" name="usos_max" class="input-field"
                       min="1" placeholder="100" required>
            </div>
            <div class="form-group">
                <label class="form-label">Vence el (opcional)</label>
                <input type="datetime-local" name="vence_at" class="input-field">
            </div>
        </div>
        <button type="submit" class="btn-admin btn-admin--primary">Crear cupón</button>
    </form>
</div>

<!-- Tabla cupones -->
<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descuento</th>
                <th>Usos</th>
                <th>Vence</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($lista)): ?>
        <tr><td colspan="6" class="text-center text-muted">No hay cupones aún.</td></tr>
        <?php endif; ?>
        <?php foreach ($lista as $c): ?>
        <tr>
            <td><code class="cupon-code"><?= htmlspecialchars($c['codigo']) ?></code></td>
            <td><?= (int)$c['descuento_pct'] ?>%</td>
            <td><?= (int)$c['total_usos'] ?> / <?= (int)$c['usos_max'] ?></td>
            <td><?= $c['vence_at'] ? date('d/m/Y', strtotime($c['vence_at'])) : '—' ?></td>
            <td>
                <span class="badge <?= $c['activo'] ? 'badge--aprobado' : 'badge--suspendido' ?>">
                    <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
            </td>
            <td>
                <form method="POST" action="<?= BASE_URL ?>/admin/cupones/toggle/<?= (int)$c['id'] ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="activo" value="<?= $c['activo'] ? '0' : '1' ?>">
                    <button type="submit" class="btn-table-action">
                        <?= $c['activo'] ? 'Desactivar' : 'Activar' ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPags > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPags; $i++): ?>
    <a href="?pagina=<?= $i ?>"
       class="page-link <?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<style>
.cupon-form-panel { background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:var(--space-5); margin-bottom:var(--space-5); }
.cupon-form-panel.hidden { display:none; }
.cupon-form-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:var(--space-3); margin-bottom:var(--space-4); }
.cupon-code { background:var(--color-bg); padding:2px 8px; border-radius:4px; font-size:var(--text-sm); font-family:monospace; }
</style>
