<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Verifica tu cuenta en Movix</title></head>
<body style="margin:0;padding:0;background:#F8F9FA;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F8F9FA;padding:40px 16px;">
    <tr><td align="center">
        <table width="100%" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">
            <!-- Header -->
            <tr><td style="background:#0F3460;padding:32px 40px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:28px;font-weight:700;letter-spacing:1px;">Movix</h1>
                <p style="color:rgba(255,255,255,.75);margin:6px 0 0;font-size:14px;">Tu transporte, ahora</p>
            </td></tr>
            <!-- Body -->
            <tr><td style="padding:40px;">
                <h2 style="color:#16213E;font-size:20px;margin:0 0 12px;">¡Hola, <?= htmlspecialchars($nombre ?? 'Usuario') ?>!</h2>
                <p style="color:#6B7280;font-size:15px;line-height:1.6;margin:0 0 24px;">
                    Gracias por registrarte en Movix. Para activar tu cuenta y empezar a solicitar viajes,
                    toca el botón de abajo para verificar tu correo electrónico.
                </p>
                <div style="text-align:center;margin:32px 0;">
                    <a href="<?= htmlspecialchars($url ?? '') ?>"
                       style="background:#E94560;color:#fff;text-decoration:none;padding:16px 36px;border-radius:14px;font-size:16px;font-weight:700;display:inline-block;">
                        Verificar mi correo
                    </a>
                </div>
                <p style="color:#6B7280;font-size:13px;line-height:1.6;margin:0;">
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    <a href="<?= htmlspecialchars($url ?? '') ?>" style="color:#0F3460;word-break:break-all;"><?= htmlspecialchars($url ?? '') ?></a>
                </p>
                <hr style="border:none;border-top:1px solid #E5E7EB;margin:28px 0;">
                <p style="color:#6B7280;font-size:12px;margin:0;">Si no creaste esta cuenta, puedes ignorar este correo.</p>
            </td></tr>
            <!-- Footer -->
            <tr><td style="background:#F8F9FA;padding:20px 40px;text-align:center;">
                <p style="color:#6B7280;font-size:12px;margin:0;">© <?= date('Y') ?> Movix — Todos los derechos reservados</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
