<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= site_url('contratos') ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> Contratos</a>
        <h1 class="h3 mb-0 text-gray-800">Nuevo contrato</h1>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errores_validacion')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errores_validacion') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Paso 1: elegir el tipo de contrato. Un GET normal: al cambiar el
     select, la página se recarga con ?tipo_contrato_id=X en la URL.
     Así puedes refrescar o compartir el enlace sin perder el tipo elegido. -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="<?= site_url('contratos/nuevo') ?>">
            <div class="form-group">
                <label>Tipo de contrato</label>
                <select name="tipo_contrato_id" class="form-control" style="max-width: 400px;" onchange="this.form.submit()" required>
                    <option value="">Selecciona un tipo...</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= $tipo['id'] ?>" <?= ($tipoContrato && $tipoContrato['id'] == $tipo['id']) ? 'selected' : '' ?>>
                            <?= esc($tipo['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($tipoContrato): ?>
    <form method="POST" action="<?= site_url('contratos/crear') ?>">
        <input type="hidden" name="tipo_contrato_id" value="<?= $tipoContrato['id'] ?>">

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos generales</h6>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Proveedor <span class="text-danger">*</span></label>
                        <select name="proveedor_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= old('proveedor_id') == $p['id'] ? 'selected' : '' ?>>
                                    <?= esc($p['razon_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Área solicitante <span class="text-danger">*</span></label>
                        <select name="area_solicitante_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= old('area_solicitante_id') == $a['id'] ? 'selected' : '' ?>>
                                    <?= esc($a['nombre_area']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción corta <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion_corta" class="form-control" maxlength="255"
                        value="<?= old('descripcion_corta') ?>" placeholder="Para tablas y listados" required>
                </div>

                <div class="form-group">
                    <label>Descripción larga (objeto del contrato) <span class="text-danger">*</span></label>
                    <textarea name="descripcion_larga" class="form-control" rows="4" required><?= old('descripcion_larga') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Fecha de entrega</label>
                        <input type="date" name="fecha_entrega" class="form-control" value="<?= old('fecha_entrega') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Lugar de entrega</label>
                        <input type="text" name="lugar_entrega" class="form-control" value="<?= old('lugar_entrega') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Plazo de pago</label>
                        <input type="text" name="plazo_pago" class="form-control" value="<?= old('plazo_pago') ?>">
                    </div>
                </div>
            </div>
        </div>

        <?php if (! empty($camposDinamicos)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Datos específicos de "<?= esc($tipoContrato['nombre']) ?>"</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($camposDinamicos as $campo): ?>
                        <div class="form-group">
                            <label>
                                <?= esc($campo['etiqueta']) ?>
                                <?php if ($campo['obligatorio']): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>

                            <?php $valorPrevio = old('campos.' . $campo['id']); ?>

                            <?php if ($campo['tipo_dato'] === 'texto_largo'): ?>
                                <textarea name="campos[<?= $campo['id'] ?>]" class="form-control" rows="3"
                                    <?= $campo['obligatorio'] ? 'required' : '' ?>><?= $valorPrevio ?></textarea>

                            <?php elseif ($campo['tipo_dato'] === 'numero'): ?>
                                <input type="number" step="any" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                    value="<?= $valorPrevio ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>

                            <?php elseif ($campo['tipo_dato'] === 'fecha'): ?>
                                <input type="date" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                    value="<?= $valorPrevio ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>

                            <?php elseif ($campo['tipo_dato'] === 'lista'): ?>
                                <select name="campos[<?= $campo['id'] ?>]" class="form-control" <?= $campo['obligatorio'] ? 'required' : '' ?>>
                                    <option value="">Selecciona...</option>
                                    <?php foreach ($opcionesListas[$campo['origen_lista']] ?? [] as $opcion): ?>
                                        <?php $etiquetaOpcion = $opcion['razon_social'] ?? $opcion['nombre_area'] ?? ('#' . $opcion['id']); ?>
                                        <option value="<?= $opcion['id'] ?>" <?= $valorPrevio == $opcion['id'] ? 'selected' : '' ?>>
                                            <?= esc($etiquetaOpcion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php else: ?>
                                <input type="text" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                    value="<?= $valorPrevio ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar contrato
        </button>
    </form>
<?php endif; ?>

<?= $this->endSection() ?>
