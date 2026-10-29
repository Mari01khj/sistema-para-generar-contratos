<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= site_url('contratos/' . $contrato['id']) ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> <?= esc($contrato['folio']) ?></a>
        <h1 class="h3 mb-0 text-gray-800">Editar contrato</h1>
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


<div class="alert alert-secondary">
    Tipo de contrato: <strong><?= esc($tipoContrato['nombre']) ?></strong>
    <?php if ($fuente): ?>
        &nbsp;·&nbsp; Fuente de financiamiento: <strong><?= esc($fuente['nombre']) ?></strong>
    <?php endif; ?>
    <span class="text-muted">(no se puede cambiar pues afecta directo en el folio del contrato)</span>
</div>

<?php
?>
<?= view('contratos/_formulario', [
    'accionUrl'          => site_url('contratos/' . $contrato['id'] . '/actualizar'),
    'tipoContrato'       => $tipoContrato,
    'proveedores'        => $proveedores,
    'areas'              => $areas,
    'camposDinamicos'    => $camposDinamicos,
    'opcionesListas'     => $opcionesListas,
    'filasBienesPrevias' => $filasBienesPrevias,
    'valoresFijos'       => $contrato,
    'valoresCampos'      => $valoresCampos,
    'textoBoton'         => 'Guardar cambios',
]) ?>

<?= $this->endSection() ?>
