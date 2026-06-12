<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página no encontrada | Movix</title>
    <style>
        body { font-family: Arial, sans-serif; background: #F8F9FA; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { text-align: center; padding: 40px 24px; }
        h1 { font-size: 72px; color: #0F3460; margin: 0; }
        p  { color: #6B7280; font-size: 16px; }
        a  { display: inline-block; margin-top: 20px; padding: 14px 32px; background: #0F3460; color: #fff; border-radius: 12px; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="box">
        <h1>404</h1>
        <p>Página no encontrada</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>">Volver al inicio</a>
    </div>
</body>
</html>
