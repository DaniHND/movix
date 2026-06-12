<?php
/** @var array $viaje */
$estadoMap = [
    'pendiente' => ['label' => 'Buscando conductor',  'cls' => 'badge--warning', 'icon' => '🔍'],
    'asignado'  => ['label' => 'Conductor en camino', 'cls' => 'badge--info',    'icon' => '🚕'],
    'en_curso'  => ['label' => 'Viaje en curso',      'cls' => 'badge--success', 'icon' => '🏁'],
    'completado'=> ['label' => 'Viaje completado',    'cls' => 'badge--success', 'icon' => '✅'],
    'cancelado' => ['label' => 'Viaje cancelado',     'cls' => 'badge--danger',  'icon' => '❌'],
];
$info = $estadoMap[$viaje['estado']] ?? ['label' => $viaje['estado'], 'cls' => 'badge--warning', 'icon' => '•'];
?>

<!-- Mapa de seguimiento -->
<div id="mapa-container" class="mapa-canvas"></div>
<div id="no-key-banner" class="mapa-placeholder" hidden>
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor" stroke="none"/>
    </svg>
    <p class="mapa-placeholder__title">Seguimiento en mapa</p>
    <p class="mapa-placeholder__sub">Configura <code>GOOGLE_MAPS_KEY</code> para ver el mapa.</p>
</div>

<!-- Panel de detalles del viaje -->
<div class="bottom-sheet bs-viaje-detail"
     data-viaje-id="<?= (int)$viaje['id'] ?>"
     data-estado="<?= htmlspecialchars($viaje['estado']) ?>">
    <div class="bs-handle"></div>

    <div class="viaje-detail-content">
        <!-- Estado -->
        <div class="viaje-detail-header">
            <div>
                <span class="estado-badge <?= $info['cls'] ?>" id="viaje-estado-badge">
                    <?= $info['icon'] ?> <?= $info['label'] ?>
                </span>
                <p class="viaje-tipo-label">
                    <?= ucfirst($viaje['tipo_servicio']) ?>
                    · <?= (int)$viaje['num_pasajeros'] ?> pasajero<?= $viaje['num_pasajeros'] > 1 ? 's' : '' ?>
                    <?= $viaje['ida_y_regreso'] ? '· Ida y vuelta' : '' ?>
                </p>
            </div>
            <div class="viaje-precio-grande">
                L. <?= number_format((float)$viaje['precio_total'], 2) ?>
            </div>
        </div>

        <!-- Acciones principales — arriba para que siempre sean visibles en móvil -->
        <div class="viaje-actions viaje-actions--top">
            <a href="<?= BASE_URL ?>/cliente" class="btn-secondary">← Volver</a>
            <?php if (in_array($viaje['estado'], ['pendiente', 'asignado'])): ?>
            <form method="POST" action="<?= BASE_URL ?>/cliente/cancelar/<?= (int)$viaje['id'] ?>" style="flex:1">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" class="btn-secondary btn-full"
                        style="border-color:var(--color-danger);color:var(--color-danger)"
                        onclick="return confirm('¿Cancelar el viaje?')">
                    Cancelar viaje
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Conductor (visible si está asignado) -->
        <?php if (!empty($viaje['conductor_nombre'])): ?>
        <div class="conductor-card" id="conductor-card">
            <div class="conductor-avatar-wrap">
                <?php if (!empty($viaje['foto_perfil'])): ?>
                <img src="<?= BASE_URL ?>/conductor-foto/<?= (int)$viaje['conductor_id'] ?>"
                     alt="Conductor" class="conductor-avatar">
                <?php else: ?>
                <div class="conductor-avatar conductor-avatar--initials">
                    <?= strtoupper(mb_substr($viaje['conductor_nombre'], 0, 1)) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="conductor-info">
                <strong id="conductor-nombre"><?= htmlspecialchars($viaje['conductor_nombre']) ?></strong>
                <span>
                    <?= htmlspecialchars($viaje['marca'] ?? '') ?>
                    <?= htmlspecialchars($viaje['modelo'] ?? '') ?>
                    <?= !empty($viaje['color']) ? '· ' . htmlspecialchars($viaje['color']) : '' ?>
                    <?php if (!empty($viaje['numero_taxi'])): ?>
                    · Taxi #<?= htmlspecialchars($viaje['numero_taxi']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($viaje['conductor_tel'])): ?>
            <a href="tel:<?= htmlspecialchars($viaje['conductor_tel']) ?>"
               class="btn-icon-round" aria-label="Llamar conductor">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                </svg>
            </a>
            <?php endif; ?>
        </div>

        <?php if ($viaje['estado'] === 'asignado'): ?>
        <div class="eta-bar" id="eta-bar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span id="eta-display">Calculando tiempo de llegada…</span>
        </div>
        <?php endif; ?>

        <?php elseif (in_array($viaje['estado'], ['pendiente', 'asignado'])): ?>
        <div class="no-conductor-msg" id="buscando-msg">
            <div class="spinner spinner--sm"></div>
            <span>Buscando conductor disponible…</span>
        </div>
        <?php endif; ?>

        <!-- Ruta -->
        <div class="route-summary">
            <div class="route-summary-row">
                <span class="route-dot route-dot--origen"></span>
                <span><?= htmlspecialchars($viaje['direccion_origen'] ?: 'Origen del viaje') ?></span>
            </div>
            <div class="route-connector"></div>
            <div class="route-summary-row">
                <span class="route-dot route-dot--destino"></span>
                <span><?= htmlspecialchars($viaje['direccion_destino'] ?: 'Destino del viaje') ?></span>
            </div>
        </div>

        <!-- Meta (distancia/duración) -->
        <div class="route-meta">
            <span class="route-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <?= (int)$viaje['duracion_min'] ?> min aprox.
            </span>
            <span class="route-meta-sep">·</span>
            <span class="route-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="2" x2="12" y2="22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
                <?= number_format((float)$viaje['distancia_km'], 1) ?> km
            </span>
        </div>

        <!-- Chat con conductor -->
        <?php if (!empty($viaje['conductor_id']) && in_array($viaje['estado'], ['asignado','en_curso'])): ?>
        <div class="chat-section">
            <div class="chat-toggle" id="chat-toggle">
                <span class="chat-toggle-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Chat con el conductor
                    <span class="chat-badge" id="chat-badge" hidden></span>
                </span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
            <div class="chat-body" id="chat-body">
                <div class="chat-messages" id="chat-messages"></div>
                <div class="chat-input-row">
                    <input type="text" id="chat-input" class="chat-input"
                           placeholder="Escribe un mensaje…" maxlength="500" autocomplete="off">
                    <button id="chat-send" class="chat-send" aria-label="Enviar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- El polling y seguimiento GPS los maneja tracking.js (cargado en el layout) -->
