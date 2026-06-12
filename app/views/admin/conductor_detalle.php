<?php
$estadoClase = [
    'pendiente'  => 'badge--pendiente',
    'aprobado'   => 'badge--aprobado',
    'rechazado'  => 'badge--rechazado',
    'suspendido' => 'badge--suspendido',
][$conductor['estado']] ?? 'badge--pendiente';
?>

<div class="actions-bar">
    <a href="<?= BASE_URL ?>/admin/conductores" class="back-link">← Volver a conductores</a>
</div>

<?php if (!empty($okMsg)): ?>
<div class="alert alert-success"><?= htmlspecialchars($okMsg) ?></div>
<?php endif; ?>

<!-- Información personal -->
<div class="detail-card">
    <h3>
        <?= htmlspecialchars($conductor['nombre']) ?>
        <span class="badge <?= $estadoClase ?>"><?= ucfirst($conductor['estado']) ?></span>
    </h3>
    <div class="detail-grid">
        <div class="detail-field">
            <label>Email</label>
            <span><?= htmlspecialchars($conductor['email']) ?></span>
        </div>
        <div class="detail-field">
            <label>Teléfono</label>
            <span><?= htmlspecialchars($conductor['telefono']) ?></span>
        </div>
        <div class="detail-field">
            <label>Identidad</label>
            <span><?= htmlspecialchars($conductor['identidad']) ?></span>
        </div>
        <div class="detail-field">
            <label>Fecha de nacimiento</label>
            <span><?= htmlspecialchars($conductor['fecha_nacimiento']) ?></span>
        </div>
        <div class="detail-field">
            <label>Tipo de servicio</label>
            <span>
                <span class="badge <?= $conductor['tipo'] === 'vip' ? 'badge--vip' : 'badge--conv' ?>">
                    <?= ucfirst($conductor['tipo']) ?>
                </span>
            </span>
        </div>
        <div class="detail-field">
            <label>Activo en app</label>
            <span><?= $conductor['activo'] ? 'Sí' : 'No' ?></span>
        </div>
        <div class="detail-field">
            <label>Email verificado</label>
            <span><?= $conductor['email_verificado'] ? 'Sí' : 'No' ?></span>
        </div>
        <div class="detail-field">
            <label>Registro</label>
            <span><?= date('d/m/Y H:i', strtotime($conductor['created_at'])) ?></span>
        </div>
    </div>
</div>

<!-- Vehículo -->
<?php if (!empty($conductor['marca'])): ?>
<div class="detail-card">
    <h3>Vehículo</h3>
    <div class="detail-grid">
        <div class="detail-field">
            <label>Marca / Modelo</label>
            <span><?= htmlspecialchars($conductor['marca'] . ' ' . $conductor['modelo']) ?></span>
        </div>
        <div class="detail-field">
            <label>Año</label>
            <span><?= htmlspecialchars((string)($conductor['anio'] ?? '—')) ?></span>
        </div>
        <div class="detail-field">
            <label>Placa</label>
            <span><?= htmlspecialchars($conductor['placa'] ?? '—') ?></span>
        </div>
        <div class="detail-field">
            <label>Color</label>
            <span><?= htmlspecialchars($conductor['color'] ?? '—') ?></span>
        </div>
        <?php if (!empty($conductor['numero_taxi'])): ?>
        <div class="detail-field">
            <label>Número de taxi</label>
            <span>#<?= htmlspecialchars($conductor['numero_taxi']) ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-field">
            <label>Polarizado</label>
            <span><?= !empty($conductor['polarizado']) ? 'Sí' : 'No' ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Documentos del conductor -->
<?php
$docs = [
    'Foto de perfil'         => $conductor['foto_perfil']            ?? null,
    'Identidad — frente'     => $conductor['foto_identidad_frente']  ?? null,
    'Identidad — reverso'    => $conductor['foto_identidad_reverso'] ?? null,
    'Licencia — frente'      => $conductor['foto_licencia_frente']   ?? null,
    'Licencia — reverso'     => $conductor['foto_licencia_reverso']  ?? null,
];
$fotosVeh = [
    'Vehículo — frente' => $conductor['veh_foto_frente'] ?? null,
    'Vehículo — atrás'  => $conductor['veh_foto_atras']  ?? null,
];
$tieneAlgunDoc = array_filter(array_merge($docs, $fotosVeh));
?>
<div class="detail-card">
    <h3>Documentos</h3>
    <?php if (!$tieneAlgunDoc): ?>
        <p style="color:var(--color-muted);font-size:.875rem">Sin documentos subidos.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-top:12px">
            <?php foreach (array_merge($docs, $fotosVeh) as $label => $ruta): ?>
                <?php if (!$ruta) continue; ?>
                <?php
                    $url = UPLOAD_URL . '/' . $ruta;
                    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                ?>
                <div style="text-align:center">
                    <?php if ($ext === 'pdf'): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener"
                           style="display:flex;flex-direction:column;align-items:center;gap:6px;
                                  padding:12px 8px;border:1px solid #e5e7eb;border-radius:10px;
                                  color:var(--color-primary);text-decoration:none;font-size:.75rem;
                                  background:#f8f9fa">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener"
                           title="<?= htmlspecialchars($label) ?>">
                            <img src="<?= htmlspecialchars($url) ?>"
                                 alt="<?= htmlspecialchars($label) ?>"
                                 style="width:100%;height:100px;object-fit:cover;
                                        border-radius:10px;border:1px solid #e5e7eb">
                        </a>
                        <p style="font-size:.7rem;color:var(--color-muted);margin-top:4px">
                            <?= htmlspecialchars($label) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Acciones -->
<div class="detail-card">
    <h3>Acciones</h3>
    <div class="conductor-actions">

        <?php if ($conductor['estado'] !== 'aprobado'): ?>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/conductores/accion/<?= (int)$conductor['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="accion" value="aprobar">
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('¿Aprobar a este conductor?')">
                Aprobar
            </button>
        </form>
        <?php endif; ?>

        <?php if ($conductor['estado'] !== 'rechazado'): ?>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/conductores/accion/<?= (int)$conductor['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="accion" value="rechazar">
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('¿Rechazar a este conductor?')">
                Rechazar
            </button>
        </form>
        <?php endif; ?>

        <?php if ($conductor['estado'] === 'aprobado'): ?>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/conductores/accion/<?= (int)$conductor['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="accion" value="suspender">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('¿Suspender a este conductor?')">
                Suspender
            </button>
        </form>
        <?php endif; ?>

    </div>
</div>
