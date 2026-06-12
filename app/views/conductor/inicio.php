<?php
$estado = $conductor['estado'] ?? 'pendiente';
$activo = (bool)($conductor['activo'] ?? false);
?>

<?php if ($estado === 'pendiente'): ?>
<!-- ── Estado: pendiente de aprobación ─────────────── -->
<div class="status-screen">
    <div class="status-icon status-icon--warning">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
    </div>
    <h2 class="status-title">Solicitud en revisión</h2>
    <p class="status-sub">
        Hemos recibido tu solicitud. El administrador revisará tus datos
        y recibirás acceso cuando sea aprobada.
    </p>
</div>

<?php elseif ($estado === 'rechazado'): ?>
<!-- ── Estado: rechazado ───────────────────────────── -->
<div class="status-screen">
    <div class="status-icon status-icon--danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
    </div>
    <h2 class="status-title">Solicitud rechazada</h2>
    <p class="status-sub">
        Tu solicitud no fue aprobada. Contacta al administrador para más información.
    </p>
</div>

<?php elseif ($estado === 'suspendido'): ?>
<!-- ── Estado: suspendido ──────────────────────────── -->
<div class="status-screen">
    <div class="status-icon status-icon--danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <h2 class="status-title">Cuenta suspendida</h2>
    <p class="status-sub">
        Tu cuenta ha sido suspendida temporalmente. Contacta al administrador.
    </p>
</div>

<?php else: ?>
<!-- ── Estado: aprobado — dashboard ───────────────── -->

<!-- Toggle en línea / fuera de línea -->
<div class="online-section">
    <div class="online-card <?= $activo ? 'online-card--active' : '' ?>" id="online-card">
        <div class="online-status-dot" id="status-dot"></div>
        <div class="online-info">
            <span class="online-label" id="online-label">
                <?= $activo ? 'En línea' : 'Fuera de línea' ?>
            </span>
            <span class="online-sub" id="online-sub">
                <?= $activo ? 'Recibirás solicitudes de viaje' : 'Actívate para recibir viajes' ?>
            </span>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" id="toggle-activo" <?= $activo ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
        </label>
    </div>
</div>

<?php if ($viajeActivo): ?>
<!-- ── Viaje activo ──────────────────────────────── -->
<div class="section-title">
    <?= $viajeActivo['estado'] === 'asignado' ? 'Viaje asignado' : 'Viaje en curso' ?>
</div>
<div class="viaje-card viaje-card--activo">
    <div class="viaje-card-header">
        <span class="vc-badge vc-badge--<?= $viajeActivo['estado'] ?>">
            <?= $viajeActivo['estado'] === 'asignado' ? 'Asignado' : 'En curso' ?>
        </span>
        <span class="vc-precio">L. <?= number_format((float)$viajeActivo['precio_total'], 2) ?></span>
    </div>

    <div class="viaje-route">
        <div class="route-row">
            <span class="route-dot route-dot--origen"></span>
            <span class="route-text">
                <?= htmlspecialchars($viajeActivo['direccion_origen'] ?: 'Origen del cliente') ?>
            </span>
        </div>
        <div class="route-connector-small"></div>
        <div class="route-row">
            <span class="route-dot route-dot--destino"></span>
            <span class="route-text">
                <?= htmlspecialchars($viajeActivo['direccion_destino'] ?: 'Destino') ?>
            </span>
        </div>
    </div>

    <div class="viaje-meta">
        <span><?= number_format((float)$viajeActivo['distancia_km'], 1) ?> km</span>
        <span>·</span>
        <span><?= ucfirst($viajeActivo['tipo_servicio']) ?></span>
        <?php if (!empty($viajeActivo['cliente_nombre'])): ?>
        <span>·</span>
        <span><?= htmlspecialchars($viajeActivo['cliente_nombre']) ?></span>
        <?php endif; ?>
    </div>

    <div class="viaje-actions">
        <?php if ($viajeActivo['estado'] === 'asignado'): ?>
        <form method="POST"
              action="<?= BASE_URL ?>/conductor/viaje/iniciar/<?= (int)$viajeActivo['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn-conductor btn-conductor--success btn-full">
                Iniciar viaje
            </button>
        </form>
        <?php elseif ($viajeActivo['estado'] === 'en_curso'): ?>
        <form method="POST"
              action="<?= BASE_URL ?>/conductor/viaje/completar/<?= (int)$viajeActivo['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn-conductor btn-conductor--primary btn-full"
                    onclick="return confirm('¿Confirmar viaje completado?')">
                Completar viaje
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Chat con el cliente -->
    <div class="chat-section-conductor">
        <div class="chat-toggle-conductor" id="chat-toggle">
            <span class="chat-toggle-label-c">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                </svg>
                Chat con el cliente
                <span class="chat-badge" id="chat-badge" hidden></span>
            </span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
        <div class="chat-body-conductor" id="chat-body">
            <div class="chat-messages-c" id="chat-messages"></div>
            <div class="chat-input-row">
                <input type="text" id="chat-input" class="chat-input-c"
                       placeholder="Escribe un mensaje…" maxlength="500" autocomplete="off">
                <button id="chat-send" class="chat-send-c" aria-label="Enviar">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activo): ?>
<!-- ── En línea, esperando viajes ───────────────── -->
<?php if (!empty($pendientes)): ?>
<div class="section-title">Viajes disponibles</div>
<?php foreach ($pendientes as $v): ?>
<div class="viaje-card">
    <div class="viaje-card-header">
        <span class="vc-badge vc-badge--pendiente">Pendiente</span>
        <span class="vc-precio">L. <?= number_format((float)$v['precio_total'], 2) ?></span>
    </div>

    <div class="viaje-route">
        <div class="route-row">
            <span class="route-dot route-dot--origen"></span>
            <span class="route-text">
                <?= htmlspecialchars($v['direccion_origen'] ?: 'Origen') ?>
            </span>
        </div>
        <div class="route-connector-small"></div>
        <div class="route-row">
            <span class="route-dot route-dot--destino"></span>
            <span class="route-text">
                <?= htmlspecialchars($v['direccion_destino'] ?: 'Destino') ?>
            </span>
        </div>
    </div>

    <div class="viaje-meta">
        <span><?= number_format((float)$v['distancia_km'], 1) ?> km</span>
        <span>·</span>
        <span><?= ucfirst($v['tipo_servicio']) ?></span>
    </div>

    <form method="POST"
          action="<?= BASE_URL ?>/conductor/viaje/tomar/<?= (int)$v['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button type="submit" class="btn-conductor btn-conductor--success btn-full">
            Tomar viaje
        </button>
    </form>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="waiting-screen">
    <div class="waiting-pulse">
        <span></span><span></span><span></span>
    </div>
    <p class="waiting-text">Esperando solicitudes de viaje…</p>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ── Fuera de línea ────────────────────────────── -->
<div class="offline-screen">
    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--color-muted)" stroke-width="1.2">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <line x1="23" y1="1" x2="1" y2="23"/>
    </svg>
    <p class="offline-text">Estás fuera de línea</p>
    <p class="offline-sub">Activa el interruptor para comenzar a recibir viajes.</p>
</div>
<?php endif; ?>

<?php endif; ?>
