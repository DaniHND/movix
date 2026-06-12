<?php
$errorKey = $_GET['error'] ?? null;
$errores = [
    'campos_requeridos'      => 'Por favor completa todos los campos obligatorios.',
    'email_invalido'         => 'El formato del correo electrónico no es válido.',
    'password_corto'         => 'La contraseña debe tener al menos 8 caracteres.',
    'passwords_no_coinciden' => 'Las contraseñas no coinciden.',
    'email_existente'        => 'Ya existe una cuenta con ese correo electrónico.',
    'telefono_existente'     => 'Ya existe una cuenta con ese número de teléfono.',
    'error_servidor'         => 'Error interno. Intenta de nuevo.',
];
$error = $errorKey ? ($errores[$errorKey] ?? $errorKey) : null;
?>
<div class="auth-container auth-container--wide">
    <div class="auth-card">

        <div class="auth-header">
            <a href="<?= BASE_URL ?>/login" class="btn-back" aria-label="Volver">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
            <div class="auth-logo-small">
                <span class="logo-dot">M</span>
                <h1 class="logo-text-sm">Movix</h1>
            </div>
        </div>

        <h2 class="auth-title">Crear cuenta</h2>
        <p class="auth-subtitle">Regístrate para solicitar viajes</p>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/registro" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre completo <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" class="input-field"
                    placeholder="Juan Pérez" autocomplete="name" required
                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input-field"
                        value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" class="input-field">
                        <option value="">Prefiero no decir</option>
                        <option value="M" <?= ($_POST['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($_POST['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="otro" <?= ($_POST['sexo'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono <span class="required">*</span></label>
                <input type="tel" id="telefono" name="telefono" class="input-field"
                    placeholder="9999-0000" autocomplete="tel" required
                    value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="input-field"
                    placeholder="tucorreo@ejemplo.com" autocomplete="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="input-field"
                        placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                    <button type="button" class="btn-toggle-pass" aria-label="Mostrar contraseña" onclick="togglePassword(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirmar_password">Confirmar contraseña <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="password" id="confirmar_password" name="confirmar_password" class="input-field"
                        placeholder="Repite tu contraseña" autocomplete="new-password" required>
                    <button type="button" class="btn-toggle-pass" aria-label="Mostrar contraseña" onclick="togglePassword(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="promo-card">
                <div class="promo-icon">🎁</div>
                <div>
                    <p class="promo-title">50% de descuento en tu primer viaje</p>
                    <p class="promo-desc">Vincula tu cuenta de Facebook después de registrarte y obtenlo.</p>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-full">Crear cuenta</button>
        </form>

        <div class="auth-footer">
            ¿Ya tienes cuenta?&nbsp;<a href="<?= BASE_URL ?>/login" class="link-accent">Inicia sesión</a>
        </div>

    </div>
</div>

<script>
function togglePassword(btn) {
    var input = btn.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
