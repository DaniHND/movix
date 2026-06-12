<?php
$portales = [
    'cliente'   => 'Cliente',
    'conductor' => 'Conductor',
    'admin'     => 'Admin',
];
$portalActivo = $_GET['portal'] ?? 'cliente';
if (!array_key_exists($portalActivo, $portales)) $portalActivo = 'cliente';
?>
<div class="auth-container">
    <div class="auth-card">

        <div class="auth-logo">
            <div class="logo-icon">M</div>
            <h1 class="logo-text">Movix</h1>
            <p class="logo-tagline">Tu transporte, ahora</p>
        </div>

        <!-- Selector de portal -->
        <div class="portal-tabs" role="tablist" aria-label="Tipo de usuario">
            <?php foreach ($portales as $key => $label): ?>
            <button
                type="button"
                class="portal-tab <?= $key === $portalActivo ? 'active' : '' ?>"
                data-portal="<?= $key ?>"
                role="tab"
                aria-selected="<?= $key === $portalActivo ? 'true' : 'false' ?>">
                <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="portal" id="portal-input" value="<?= htmlspecialchars($portalActivo) ?>">

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="input-field"
                    placeholder="tucorreo@ejemplo.com"
                    autocomplete="email"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input-field"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="btn-toggle-pass" aria-label="Mostrar contraseña" onclick="togglePassword(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/recuperar" class="forgot-link">¿Olvidaste tu contraseña?</a>

            <button type="submit" class="btn-primary btn-full">Iniciar sesión</button>
        </form>

        <div class="auth-footer" id="footer-cliente" <?= $portalActivo !== 'cliente' ? 'style="display:none"' : '' ?>>
            ¿No tienes cuenta?&nbsp;<a href="<?= BASE_URL ?>/registro" class="link-accent">Regístrate gratis</a>
        </div>

        <div class="auth-footer" id="footer-conductor" <?= $portalActivo !== 'conductor' ? 'style="display:none"' : '' ?>>
            ¿Quieres conducir?&nbsp;<a href="<?= BASE_URL ?>/registro-conductor" class="link-accent">Únete como conductor</a>
        </div>

    </div>
</div>

<?php if (APP_ENV === 'development'): ?>
<?php
/* ── CREDENCIALES DE PRUEBA — solo aparecen en APP_ENV=development ── */
$devCreds = [
    'cliente'   => ['email' => 'test@movix.com',       'pass' => 'Movix1234'],
    'conductor' => ['email' => 'conductor@movix.com',  'pass' => 'Conductor1234'],
    'admin'     => ['email' => 'admin@movix.com',      'pass' => 'Admin1234'],
];
?>
<div id="dev-panel" style="
    position:fixed;bottom:16px;right:16px;z-index:9999;
    background:#1e1e2e;color:#cdd6f4;border-radius:12px;
    padding:12px 14px;font-size:12px;font-family:monospace;
    box-shadow:0 4px 20px rgba(0,0,0,.5);min-width:210px;
    border:1px solid #45475a;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <span style="color:#a6e3a1;font-weight:700;letter-spacing:.5px">⚙ DEV — Acceso rápido</span>
        <button onclick="document.getElementById('dev-panel').style.display='none'"
                style="background:none;border:none;color:#6c7086;cursor:pointer;font-size:16px;line-height:1">×</button>
    </div>
    <?php foreach ($devCreds as $portal => $cred): ?>
    <div style="display:flex;align-items:center;gap:6px;margin-bottom:5px">
        <span style="width:62px;color:#89b4fa;text-transform:capitalize"><?= $portal ?></span>
        <span style="flex:1;color:#a6adc8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
              title="<?= htmlspecialchars($cred['email']) ?>"><?= htmlspecialchars($cred['email']) ?></span>
        <button
            onclick="devLogin('<?= htmlspecialchars($cred['email'], ENT_QUOTES) ?>','<?= htmlspecialchars($cred['pass'], ENT_QUOTES) ?>','<?= $portal ?>')"
            style="background:#313244;border:1px solid #45475a;color:#cdd6f4;
                   border-radius:6px;padding:3px 8px;cursor:pointer;font-size:11px;
                   white-space:nowrap">
            Usar →
        </button>
    </div>
    <?php endforeach; ?>
</div>
<script>
function devLogin(email, pass, portal) {
    document.getElementById('email').value    = email;
    document.getElementById('password').value = pass;
    // Activar la pestaña correcta
    document.querySelectorAll('.portal-tab').forEach(function(btn) {
        var active = btn.dataset.portal === portal;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.getElementById('portal-input').value = portal;
    var fc = document.getElementById('footer-cliente');
    var fd = document.getElementById('footer-conductor');
    if (fc) fc.style.display = portal === 'cliente'   ? '' : 'none';
    if (fd) fd.style.display = portal === 'conductor' ? '' : 'none';
}
</script>
<?php endif; ?>


<script>
document.querySelectorAll('.portal-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.portal-tab').forEach(function(b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        document.getElementById('portal-input').value = btn.dataset.portal;

        var fc = document.getElementById('footer-cliente');
        var fd = document.getElementById('footer-conductor');
        if (fc) fc.style.display = btn.dataset.portal === 'cliente'   ? '' : 'none';
        if (fd) fd.style.display = btn.dataset.portal === 'conductor' ? '' : 'none';
    });
});

function togglePassword(btn) {
    var input = btn.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
