<?php
$errorKey = $_GET['error'] ?? null;
$error = $errorKey === 'email_invalido' ? 'El formato del correo no es válido.' : null;
?>
<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <a href="<?= BASE_URL ?>/login" class="btn-back" aria-label="Volver">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
        </div>

        <div class="recover-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </div>

        <h2 class="auth-title">Recuperar contraseña</h2>
        <p class="auth-subtitle">Te enviaremos un enlace a tu correo para restablecer tu contraseña.</p>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/recuperar" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="input-field"
                    placeholder="tucorreo@ejemplo.com" autocomplete="email" required>
            </div>

            <button type="submit" class="btn-primary btn-full">Enviar enlace</button>
        </form>

    </div>
</div>
