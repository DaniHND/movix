<?php
/** @var array|false $viajeActivo */
$tieneViaje = !empty($viajeActivo);
$mapsKey    = htmlspecialchars($_ENV['GOOGLE_MAPS_KEY'] ?? '', ENT_QUOTES);
?>

<!-- Contenedor del mapa -->
<div id="mapa-container" class="mapa-canvas"></div>

<!-- Placeholder sin API key -->
<div id="no-key-banner" class="mapa-placeholder" hidden>
    <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor" stroke="none"/>
    </svg>
    <p class="mapa-placeholder__title">Mapa en desarrollo</p>
    <p class="mapa-placeholder__sub">
        Configura <code>GOOGLE_MAPS_KEY</code> en <code>.env</code><br>para activar el mapa completo.
    </p>
    <?php if (!empty($mapsKey)): ?>
    <p class="mapa-placeholder__sub" style="color:#E94560">
        Clave configurada — recarga para intentar de nuevo.
    </p>
    <?php endif; ?>
</div>

<!-- Botón mi ubicación -->
<button id="btn-mi-ubicacion" class="location-fab" title="Mi ubicación" aria-label="Centrar en mi ubicación">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
    </svg>
</button>

<!-- Overlay de carga -->
<div id="loading-overlay" class="loading-overlay" hidden>
    <div class="spinner"></div>
    <p>Buscando conductor…</p>
</div>

<?php if ($tieneViaje): ?>
<!-- ── Viaje activo ───────────────────────────── -->
<div class="bottom-sheet bs-viaje-activo">
    <div class="bs-handle"></div>

    <?php
        $badgeMap = [
            'pendiente' => ['label' => 'Buscando conductor', 'cls' => 'badge--warning'],
            'asignado'  => ['label' => 'Conductor en camino', 'cls' => 'badge--info'],
            'en_curso'  => ['label' => 'Viaje en curso',      'cls' => 'badge--success'],
        ];
        $badge = $badgeMap[$viajeActivo['estado']] ?? ['label' => $viajeActivo['estado'], 'cls' => 'badge--warning'];
    ?>

    <div class="viaje-activo-header">
        <span class="estado-badge <?= $badge['cls'] ?>">
            <span class="dot-pulse"></span>
            <?= $badge['label'] ?>
        </span>
        <span class="viaje-precio">L. <?= number_format((float)$viajeActivo['precio_total'], 2) ?></span>
    </div>

    <?php if (!empty($viajeActivo['conductor_nombre'])): ?>
    <div class="conductor-row">
        <div class="conductor-avatar-wrap">
            <?php if (!empty($viajeActivo['foto_perfil'])): ?>
            <img src="<?= BASE_URL ?>/conductor-foto/<?= (int)$viajeActivo['conductor_id'] ?>"
                 alt="Foto" class="conductor-avatar">
            <?php else: ?>
            <div class="conductor-avatar conductor-avatar--initials">
                <?= strtoupper(mb_substr($viajeActivo['conductor_nombre'], 0, 1)) ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="conductor-info">
            <strong><?= htmlspecialchars($viajeActivo['conductor_nombre']) ?></strong>
            <span>
                <?= htmlspecialchars($viajeActivo['marca'] ?? '') ?>
                <?= htmlspecialchars($viajeActivo['color'] ?? '') ?>
                <?php if (!empty($viajeActivo['numero_taxi'])): ?>
                — Taxi #<?= htmlspecialchars($viajeActivo['numero_taxi']) ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <?php else: ?>
    <div class="no-conductor-msg">
        <div class="spinner spinner--sm"></div>
        <span>Asignando conductor…</span>
    </div>
    <?php endif; ?>

    <div class="viaje-actions">
        <a href="<?= BASE_URL ?>/cliente/viaje/<?= (int)$viajeActivo['id'] ?>"
           class="btn-primary btn-full">Ver detalles</a>
        <?php if (in_array($viajeActivo['estado'], ['pendiente', 'asignado'])): ?>
        <form method="POST" action="<?= BASE_URL ?>/cliente/cancelar/<?= (int)$viajeActivo['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn-secondary"
                    style="border-color:var(--color-danger);color:var(--color-danger)"
                    onclick="return confirm('¿Cancelar el viaje?')">Cancelar</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- ── Bottom sheet búsqueda ─────────────────── -->
<div id="bs-buscar" class="bottom-sheet bs-buscar">
    <div class="bs-handle"></div>

    <div class="bs-buscar-content">
        <p class="bs-greeting">Hola, <?= htmlspecialchars(Auth::nombre()) ?> 👋</p>
        <div class="search-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="destino-input"
                   type="text"
                   class="search-input"
                   placeholder="¿A dónde quieres ir?"
                   autocomplete="off"
                   inputmode="search">
        </div>

        <!-- Servicios disponibles -->
        <div class="services-row">
            <div class="service-chip service-chip--conv">
                <span>🚕</span> Convencional
            </div>
            <div class="service-chip service-chip--vip">
                <span>🚘</span> VIP
            </div>
        </div>
    </div>
</div>

<!-- ── Bottom sheet ruta + precio ────────────── -->
<div id="bs-ruta" class="bottom-sheet bs-ruta" hidden>
    <div class="bs-handle"></div>

    <div class="bs-ruta-content">
        <!-- Origen / Destino -->
        <div class="route-stack">
            <div class="route-row">
                <span class="route-dot route-dot--origen"></span>
                <div class="route-field">
                    <input id="origen-input"
                           type="text"
                           class="route-input"
                           placeholder="Tu ubicación"
                           autocomplete="off">
                </div>
            </div>
            <div class="route-connector"></div>
            <div class="route-row">
                <span class="route-dot route-dot--destino"></span>
                <div class="route-field">
                    <span id="destino-label" class="route-input route-input--filled">—</span>
                </div>
            </div>
        </div>

        <!-- Distancia y duración -->
        <div class="route-meta">
            <span class="route-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span id="ruta-duracion">— min</span>
            </span>
            <span class="route-meta-sep">·</span>
            <span class="route-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="2" x2="12" y2="22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
                <span id="ruta-distancia">— km</span>
            </span>
        </div>

        <!-- Selector de servicio -->
        <div class="service-cards">
            <button class="service-card service-card--selected" data-tipo="convencional" type="button">
                <span class="service-card-emoji">🚕</span>
                <span class="service-card-name">Convencional</span>
                <span class="service-card-price" id="precio-convencional">L. —</span>
            </button>
            <button class="service-card" data-tipo="vip" type="button">
                <span class="service-card-emoji">🚘</span>
                <span class="service-card-name">VIP</span>
                <span class="service-card-price" id="precio-vip">L. —</span>
            </button>
        </div>

        <!-- Pasajeros -->
        <div class="options-row">
            <span class="options-label">Pasajeros</span>
            <div class="counter">
                <button id="btn-menos" class="counter-btn" type="button" aria-label="Reducir">−</button>
                <span id="num-pasajeros" class="counter-val">1</span>
                <button id="btn-mas"   class="counter-btn" type="button" aria-label="Aumentar">+</button>
            </div>
        </div>

        <!-- Ida y vuelta -->
        <div class="options-row">
            <span class="options-label">Ida y vuelta</span>
            <label class="toggle">
                <input type="checkbox" id="ida-vuelta">
                <span class="toggle-track"><span class="toggle-thumb"></span></span>
            </label>
        </div>

        <!-- Cupón de descuento -->
        <div>
            <div class="cupon-row">
                <input type="text" id="cupon-input" class="cupon-input"
                       placeholder="CÓDIGO CUPÓN (opcional)" maxlength="30" autocomplete="off">
                <button type="button" class="btn-cupon" id="btn-cupon">Aplicar</button>
            </div>
            <span id="cupon-msg"></span>
        </div>

        <!-- CSRF + solicitar -->
        <input type="hidden" id="csrf-token-val" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" id="cupon-id-val" value="">
        <input type="hidden" id="descuento-pct-val" value="0">
        <button id="btn-solicitar" class="btn-primary btn-full" type="button">
            Solicitar viaje
        </button>
    </div>
</div>
<?php endif; ?>
