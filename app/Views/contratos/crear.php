<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= site_url('contratos') ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> Contratos</a>
        <h1 class="h3 mb-0 text-gray-800">Nuevo contrato</h1>
    </div>
</div>

<?php if (session()->getFlashdata('errores_validacion')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errores_validacion') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

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

<?php if($tipoContrato): ?>

<?php
    ?>
    <?= view('contratos/_formulario', [
        'accionUrl'          => site_url('contratos/crear'),
        'tipoContrato'       => $tipoContrato,
        'proveedores'        => $proveedores,
        'areas'              => $areas,
        'camposDinamicos'    => $camposDinamicos,
        'opcionesListas'     => $opcionesListas,
        'filasBienesPrevias' => $filasBienesPrevias,
        'textoBoton'         => 'Guardar contrato',
    ]) ?>
<?php endif; ?>

<?= $this->endSection() ?>
