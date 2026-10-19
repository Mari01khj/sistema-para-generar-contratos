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

<!-- A diferencia de crear.php, aquí NO hay un selector de "Paso 1: elegir -->
<!-- tipo", porque el tipo de un contrato ya existente no se puede cambiar. -->
<!-- Se muestra solo como texto informativo. Si el tipo de verdad estuviera -->
<!-- mal, lo correcto es eliminar (soft-delete) este contrato y crear uno -->
<!-- nuevo del tipo correcto, no "migrar" uno existente: sus campos -->
<!-- dinámicos están ligados al tipo original y cambiarlo dejaría huérfanos -->
<!-- los valores guardados en contrato_valores. -->
<div class="alert alert-secondary">
    Tipo de contrato: <strong><?= esc($tipoContrato['nombre']) ?></strong>
    <?php if ($fuente): ?>
        &nbsp;·&nbsp; Fuente de financiamiento: <strong><?= esc($fuente['nombre']) ?></strong>
    <?php endif; ?>
    <span class="text-muted">(no se puede cambiar; para eso hay que crear un contrato nuevo)</span>
</div>

<?php
    // $valoresFijos trae los datos ya guardados del propio contrato
    // (proveedor_id, descripcion_corta, fecha_entrega, etc.) y $valoresCampos
    // trae un mapa [campo_id => valor] con sus campos dinámicos ya guardados.
    // Dentro del partial, old() sigue teniendo prioridad sobre estos dos, por
    // si el usuario ya intentó guardar y hubo un error de validación.
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
