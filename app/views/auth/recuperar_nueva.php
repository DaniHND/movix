<div class="auth-container">
    <div class="auth-card">

        <h2 class="auth-title">Nueva contraseña</h2>
        <p class="auth-subtitle">Elige una contraseña segura de al menos 8 caracteres.</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/recuperar" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

            <div class="form-group">
                <label class="form-label" for="nueva_password">Nueva contraseña</label>
                <div class="input-wrapper">
                    <input type="password" id="nueva_password" name="nueva_password" class="input-field"
                        placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                    <button type="button" class="btn-toggle-pass" aria-label="Mostrar contraseña" onclick="togglePassword(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirmar_password">Confirmar contraseña</label>
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

            <button type="submit" class="btn-primary btn-full">Guardar contraseña</button>
        </form>

    </div>
</div>

<script>
function togglePassword(btn) {
    var input = btn.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
