<?php if ($success): ?>
<div class="alert-conductor alert-conductor--success">Perfil actualizado.</div>
<?php endif; ?>

<div class="perfil-header">
    <div class="perfil-avatar">
        <?= strtoupper(mb_substr($conductor['nombre'], 0, 1)) ?>
    </div>
    <div class="perfil-info">
        <h2><?= htmlspecialchars($conductor['nombre']) ?></h2>
        <span class="perfil-badge perfil-badge--<?= $conductor['estado'] ?>">
            <?= ucfirst($conductor['estado']) ?>
        </span>
        <span class="perfil-tipo"><?= ucfirst($conductor['tipo']) ?></span>
    </div>
</div>

<!-- Datos personales -->
<div class="perfil-section">
    <h3 class="perfil-section-title">Datos personales</h3>
    <div class="perfil-field">
        <label>Correo electrónico</label>
        <span><?= htmlspecialchars($conductor['email']) ?></span>
    </div>
    <div class="perfil-field">
        <label>Identidad</label>
        <span><?= htmlspecialchars($conductor['identidad']) ?></span>
    </div>
    <div class="perfil-field">
        <label>Fecha de nacimiento</label>
        <span><?= htmlspecialchars($conductor['fecha_nacimiento']) ?></span>
    </div>
</div>

<!-- Editar teléfono -->
<div class="perfil-section">
    <h3 class="perfil-section-title">Contacto</h3>
    <form method="POST" action="<?= BASE_URL ?>/conductor/perfil">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="perfil-edit-row">
            <div class="form-group" style="flex:1">
                <label class="form-label" for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" class="input-field"
                       value="<?= htmlspecialchars($conductor['telefono']) ?>">
            </div>
            <button type="submit" class="btn-conductor btn-conductor--primary">Guardar</button>
        </div>
    </form>
</div>

<!-- Vehículo -->
<?php if (!empty($conductor['marca'])): ?>
<div class="perfil-section">
    <h3 class="perfil-section-title">Vehículo</h3>
    <div class="perfil-field">
        <label>Marca / Modelo</label>
        <span><?= htmlspecialchars($conductor['marca'] . ' ' . $conductor['modelo']) ?></span>
    </div>
    <div class="perfil-field">
        <label>Año</label>
        <span><?= htmlspecialchars((string)($conductor['anio'] ?? '—')) ?></span>
    </div>
    <div class="perfil-field">
        <label>Placa</label>
        <span><?= htmlspecialchars($conductor['placa'] ?? '—') ?></span>
    </div>
    <div class="perfil-field">
        <label>Color</label>
        <span><?= htmlspecialchars($conductor['color'] ?? '—') ?></span>
    </div>
    <?php if (!empty($conductor['numero_taxi'])): ?>
    <div class="perfil-field">
        <label>Número de taxi</label>
        <span>#<?= htmlspecialchars($conductor['numero_taxi']) ?></span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
