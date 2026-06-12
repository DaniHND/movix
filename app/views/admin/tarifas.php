<div class="page-header">
    <h2>Tarifas</h2>
    <p>Precios del servicio. Los rangos de kilometraje son fijos.</p>
</div>

<?php if (!empty($okMsg)):  ?><div class="alert alert-success"><?= htmlspecialchars($okMsg) ?></div><?php endif; ?>
<?php if (!empty($errMsg)): ?><div class="alert alert-danger"><?= htmlspecialchars($errMsg) ?></div><?php endif; ?>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Horario</th>
                <th>Distancia</th>
                <th>Precio base (L.)</th>
                <th>Por km</th>
                <th class="hide-mobile">Extra pasajero</th>
                <th class="hide-mobile">Comisión</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tarifas as $t): ?>
            <tr>
                <td>
                    <span class="badge <?= $t['tipo_servicio'] === 'vip' ? 'badge--vip' : 'badge--conv' ?>">
                        <?= ucfirst($t['tipo_servicio']) ?>
                    </span>
                </td>
                <td><?= ucfirst($t['horario']) ?></td>
                <td class="td-mono">
                    <?= number_format((float)$t['km_desde'], 1) ?> –
                    <?= (float)$t['km_hasta'] >= 9999 ? '∞' : number_format((float)$t['km_hasta'], 1) ?> km
                </td>
                <td><strong>L. <?= number_format((float)$t['precio_base'], 2) ?></strong></td>
                <td><?= $t['es_por_km'] ? 'Sí' : 'No' ?></td>
                <td class="hide-mobile">L. <?= number_format((float)$t['precio_pasajero_extra'], 2) ?></td>
                <td class="hide-mobile">L. <?= number_format((float)$t['comision_fija'], 2) ?></td>
                <td>
                    <button type="button" class="btn btn-ghost btn-sm"
                            data-tarifa-toggle="<?= (int)$t['id'] ?>">
                        Editar
                    </button>
                </td>
            </tr>
            <tr id="tarifa-form-<?= (int)$t['id'] ?>" class="tarifa-edit-row" style="display:none;">
                <td colspan="8">
                    <form method="POST"
                          action="<?= BASE_URL ?>/admin/tarifas/editar/<?= (int)$t['id'] ?>">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="tarifa-form-row">
                            <div class="tarifa-form-group">
                                <label>Precio base (L.)</label>
                                <input type="number" name="precio_base" step="0.01" min="0.01"
                                       class="tarifa-input"
                                       value="<?= number_format((float)$t['precio_base'], 2, '.', '') ?>"
                                       required>
                            </div>
                            <div class="tarifa-form-group">
                                <label>Extra pasajero (L.)</label>
                                <input type="number" name="precio_pasajero_extra" step="0.01" min="0"
                                       class="tarifa-input"
                                       value="<?= number_format((float)$t['precio_pasajero_extra'], 2, '.', '') ?>">
                            </div>
                            <div class="tarifa-form-group">
                                <label>Comisión fija (L.)</label>
                                <input type="number" name="comision_fija" step="0.01" min="0"
                                       class="tarifa-input"
                                       value="<?= number_format((float)$t['comision_fija'], 2, '.', '') ?>">
                            </div>
                            <div class="tarifa-form-group">
                                <label style="visibility:hidden">.</label>
                                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
