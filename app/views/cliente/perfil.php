<?php /** @var array $usuario */ ?>

<div class="page-header">
    <h1>Mi perfil</h1>
</div>

<div class="perfil-container">

    <?php if (!empty($success)): ?>
    <div class="alert alert-success">Perfil actualizado correctamente.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">El nombre debe tener al menos 2 caracteres.</div>
    <?php endif; ?>

    <!-- Avatar + nombre -->
    <div class="perfil-hero">
        <div class="perfil-avatar-wrap">
            <?php if (!empty($usuario['foto'])): ?>
            <img src="<?= BASE_URL ?>/cliente-foto/<?= (int)$usuario['id'] ?>"
                 alt="Foto" class="perfil-avatar">
            <?php else: ?>
            <div class="perfil-avatar perfil-avatar--initials">
                <?= strtoupper(mb_substr($usuario['nombre'], 0, 1)) ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="perfil-hero-info">
            <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
            <span><?= htmlspecialchars($usuario['email']) ?></span>
        </div>
    </div>

    <!-- Stats rápidas -->
    <div class="perfil-stats">
        <div class="perfil-stat">
            <span class="perfil-stat-value"><?= (int)$totalViajes ?></span>
            <span class="perfil-stat-label">Viajes</span>
        </div>
        <div class="perfil-stat">
            <span class="perfil-stat-value"><?= $usuario['descuento_primer_viaje'] ? '✓' : 'Pendiente' ?></span>
            <span class="perfil-stat-label">50% descuento</span>
        </div>
    </div>

    <!-- Editar datos -->
    <div class="perfil-section">
        <p class="perfil-section-title">Datos personales</p>
        <form method="POST" action="<?= BASE_URL ?>/cliente/perfil">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label class="form-label" for="p-nombre">Nombre completo</label>
                <input id="p-nombre" name="nombre" type="text" class="input-field"
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="p-telefono">Teléfono</label>
                <input id="p-telefono" name="telefono" type="tel" class="input-field"
                       value="<?= htmlspecialchars($usuario['telefono']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <input type="email" class="input-field" value="<?= htmlspecialchars($usuario['email']) ?>"
                       disabled style="opacity:.6">
            </div>

            <button type="submit" class="btn-primary btn-full">Guardar cambios</button>
        </form>
    </div>

    <!-- Acciones de cuenta -->
    <div class="perfil-section">
        <p class="perfil-section-title">Cuenta</p>
        <a href="<?= BASE_URL ?>/recuperar" class="perfil-menu-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            Cambiar contraseña
        </a>
        <a href="<?= BASE_URL ?>/logout" class="perfil-menu-link perfil-menu-link--danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Cerrar sesión
        </a>
    </div>

    <p class="perfil-member-since">
        Miembro desde <?= (new \DateTime($usuario['created_at']))->format('F Y') ?>
    </p>

</div>
