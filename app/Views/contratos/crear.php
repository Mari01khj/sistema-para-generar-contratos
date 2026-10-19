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

<!-- Paso 1: elegir el tipo de contrato. Esta parte SOLO existe en crear.php: -->
<!-- en editar.php el tipo ya quedó fijo desde que se creó el contrato y no se -->
<!-- puede cambiar (ver el comentario en ContratosController::editar()), así -->
<!-- que no tiene sentido mostrar este selector ahí. -->
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
    <!-- El resto del formulario (datos generales, campos dinámicos del tipo y -->
    <!-- la tabla de Partidas) está en un partial compartido con editar.php, -->
    <!-- para no mantener dos copias del mismo HTML/JS. Aquí no se manda -->
    <!-- $valoresFijos ni $valoresCampos porque, al CREAR, todavía no hay nada -->
    <!-- guardado que precargar: el partial simplemente los toma como [] por -->
    <!-- defecto y deja todos los campos vacíos (salvo que vengan de old(), si -->
    <!-- el usuario ya había enviado el formulario y hubo un error). -->
    <?php
        // Se usa la función view() (y NO $this->include()) a propósito:
        // $this->include($vista, $opciones) existe, pero su segundo
        // parámetro no sirve para mandar variables al partial (solo es para
        // opciones de caché) — cualquier dato ahí se ignora en silencio y el
        // partial se queda sin $accionUrl, $tipoContrato, etc. view() sí
        // combina los datos que se le pasan con los que ya tenía esta vista
        // (proveedores, areas... ya puestos por el controlador), así que el
        // partial recibe todo lo que necesita.
    ?>
    <?= view('contratos/_formulario', [
        'accionUrl'          => site_url('contratos/crear'),
        'tipoContrato'       => $tipoContrato,
        'proveedores'        => $proveedores,
        'areas'              => $areas,
        'fuentes'            => $fuentes,
        'camposDinamicos'    => $camposDinamicos,
        'opcionesListas'     => $opcionesListas,
        'filasBienesPrevias' => $filasBienesPrevias,
        'textoBoton'         => 'Guardar contrato',
    ]) ?>
<?php endif; ?>

<?= $this->endSection() ?>
