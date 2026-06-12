<div class="auth-container">
    <div class="auth-card auth-card--wide">

        <div class="auth-logo">
            <div class="logo-icon">M</div>
            <h1 class="logo-text">Movix</h1>
            <p class="logo-tagline">Registro de Conductor</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/registro-conductor"
              class="auth-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <!-- Datos personales -->
            <p class="form-section-title">Datos personales</p>

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" class="input-field"
                       placeholder="Juan Pérez" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento *</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                           class="input-field" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="identidad">Número de identidad *</label>
                    <input type="text" id="identidad" name="identidad" class="input-field"
                           placeholder="0801-XXXX-XXXXX" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" class="input-field"
                           placeholder="9999-0000" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo de servicio *</label>
                    <select id="tipo" name="tipo" class="input-field" required>
                        <option value="">Selecciona...</option>
                        <option value="convencional">Convencional</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" class="input-field"
                       placeholder="tucorreo@ejemplo.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" class="input-field"
                           placeholder="Mín. 8 caracteres" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmar_password">Confirmar contraseña *</label>
                    <input type="password" id="confirmar_password" name="confirmar_password"
                           class="input-field" placeholder="Repite la contraseña" required>
                </div>
            </div>

            <!-- Datos del vehículo -->
            <p class="form-section-title" style="margin-top: var(--space-5)">Datos del vehículo</p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="marca">Marca *</label>
                    <input type="text" id="marca" name="marca" class="input-field"
                           placeholder="Toyota" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="modelo">Modelo *</label>
                    <input type="text" id="modelo" name="modelo" class="input-field"
                           placeholder="Corolla" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="anio">Año *</label>
                    <input type="number" id="anio" name="anio" class="input-field"
                           placeholder="2020" min="2000" max="2030" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="placa">Placa *</label>
                    <input type="text" id="placa" name="placa" class="input-field"
                           placeholder="ABC-1234" required
                           style="text-transform:uppercase">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="color">Color *</label>
                    <input type="text" id="color" name="color" class="input-field"
                           placeholder="Blanco" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="numero_taxi">Número de taxi</label>
                    <input type="text" id="numero_taxi" name="numero_taxi" class="input-field"
                           placeholder="123 (opcional)">
                </div>
            </div>

            <!-- Documentos del conductor -->
            <p class="form-section-title" style="margin-top: var(--space-5)">
                Documentos requeridos
            </p>
            <p style="font-size:var(--text-sm);color:var(--color-muted);margin-bottom:var(--space-4)">
                JPEG, PNG o PDF &mdash; máx. 8 MB por archivo.
                El equipo de Movix revisará tus documentos para aprobar tu cuenta.
            </p>

            <div class="form-group">
                <label class="form-label" for="foto_perfil">
                    Foto de perfil *
                </label>
                <div class="file-upload" id="fu-foto_perfil">
                    <input type="file" id="foto_perfil" name="foto_perfil"
                           class="file-upload__input" accept="image/jpeg,image/png" required>
                    <label class="file-upload__btn" for="foto_perfil">
                        <span class="file-upload__icon">🤳</span>
                        <span class="file-upload__name">Seleccionar imagen...</span>
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="foto_identidad_frente">
                        Identidad — frente *
                    </label>
                    <div class="file-upload" id="fu-foto_identidad_frente">
                        <input type="file" id="foto_identidad_frente" name="foto_identidad_frente"
                               class="file-upload__input" accept="image/jpeg,image/png,application/pdf" required>
                        <label class="file-upload__btn" for="foto_identidad_frente">
                            <span class="file-upload__icon">🪪</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="foto_identidad_reverso">
                        Identidad — reverso *
                    </label>
                    <div class="file-upload" id="fu-foto_identidad_reverso">
                        <input type="file" id="foto_identidad_reverso" name="foto_identidad_reverso"
                               class="file-upload__input" accept="image/jpeg,image/png,application/pdf" required>
                        <label class="file-upload__btn" for="foto_identidad_reverso">
                            <span class="file-upload__icon">🪪</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="foto_licencia_frente">
                        Licencia — frente *
                    </label>
                    <div class="file-upload" id="fu-foto_licencia_frente">
                        <input type="file" id="foto_licencia_frente" name="foto_licencia_frente"
                               class="file-upload__input" accept="image/jpeg,image/png,application/pdf" required>
                        <label class="file-upload__btn" for="foto_licencia_frente">
                            <span class="file-upload__icon">📄</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="foto_licencia_reverso">
                        Licencia — reverso *
                    </label>
                    <div class="file-upload" id="fu-foto_licencia_reverso">
                        <input type="file" id="foto_licencia_reverso" name="foto_licencia_reverso"
                               class="file-upload__input" accept="image/jpeg,image/png,application/pdf" required>
                        <label class="file-upload__btn" for="foto_licencia_reverso">
                            <span class="file-upload__icon">📄</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Foto del vehículo -->
            <p class="form-section-title" style="margin-top: var(--space-5)">
                Foto del vehículo
            </p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="foto_veh_frente">
                        Frente del vehículo *
                    </label>
                    <div class="file-upload" id="fu-foto_veh_frente">
                        <input type="file" id="foto_veh_frente" name="foto_veh_frente"
                               class="file-upload__input" accept="image/jpeg,image/png" required>
                        <label class="file-upload__btn" for="foto_veh_frente">
                            <span class="file-upload__icon">🚗</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="foto_veh_atras">
                        Atrás del vehículo
                        <span class="form-label-hint">Opcional</span>
                    </label>
                    <div class="file-upload" id="fu-foto_veh_atras">
                        <input type="file" id="foto_veh_atras" name="foto_veh_atras"
                               class="file-upload__input" accept="image/jpeg,image/png">
                        <label class="file-upload__btn" for="foto_veh_atras">
                            <span class="file-upload__icon">🚗</span>
                            <span class="file-upload__name">Seleccionar...</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-full" style="margin-top: var(--space-4)">
                Enviar solicitud
            </button>
        </form>

        <div class="auth-footer">
            ¿Ya tienes cuenta?&nbsp;
            <a href="<?= BASE_URL ?>/login?portal=conductor" class="link-accent">Inicia sesión</a>
        </div>

    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.file-upload__input').forEach(function (input) {
        input.addEventListener('change', function () {
            var wrapper = document.getElementById('fu-' + this.name);
            var nameEl  = wrapper ? wrapper.querySelector('.file-upload__name') : null;
            if (!nameEl) return;
            if (this.files && this.files[0]) {
                nameEl.textContent = this.files[0].name;
                wrapper.classList.add('file-upload--ok');
            } else {
                nameEl.textContent = 'Seleccionar...';
                wrapper.classList.remove('file-upload--ok');
            }
        });
    });
}());
</script>
