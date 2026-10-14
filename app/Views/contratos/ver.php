<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= site_url('contratos') ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> Contratos</a>
        <h1 class="h3 mb-0 text-gray-800"><?= esc($contrato['folio']) ?></h1>
    </div>
    <div>
        <a href="<?= site_url('contratos/' . $contrato['id'] . '/editar') ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <form method="POST" action="<?= site_url('contratos/' . $contrato['id'] . '/eliminar') ?>" class="d-inline"
            onsubmit="return confirm('¿Eliminar el contrato <?= esc($contrato['folio'], 'js') ?>? Esta acción no se puede deshacer desde la pantalla.');">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos generales</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>Tipo de contrato</strong><br>
                <?= esc($contrato['tipo_nombre']) ?>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Proveedor</strong><br>
                <?= esc($contrato['razon_social']) ?>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Área solicitante</strong><br>
                <?= esc($contrato['nombre_area']) ?>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Descripción</strong><br>
                <?= esc($contrato['descripcion_corta']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Fecha de entrega</strong><br>
                <?= esc($contrato['fecha_entrega'] ?? '—') ?>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Lugar de entrega</strong><br>
                <?= esc($contrato['lugar_entrega'] ?? '—') ?>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Plazo de pago</strong><br>
                <?= esc($contrato['plazo_pago'] ?? '—') ?>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($valores)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos específicos de "<?= esc($contrato['tipo_nombre']) ?>"</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($valores as $valor): ?>
                    <div class="col-md-6 mb-3">
                        <strong><?= esc($valor['etiqueta']) ?></strong><br>
                        <?= esc($valor['valor'] ?? '—') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Partidas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Marca</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partidas as $partida): ?>
                        <tr>
                            <td><?= esc($partida['num_partida']) ?></td>
                            <td><?= esc($partida['descripcion']) ?></td>
                            <td><?= esc($partida['unidad_medida']) ?></td>
                            <td><?= esc($partida['marca'] ?? '—') ?></td>
                            <td class="text-right"><?= esc($partida['cantidad']) ?></td>
                            <td class="text-right">$<?= number_format((float) $partida['precio_unitario'], 2) ?></td>
                            <td class="text-right">$<?= number_format((float) $partida['importe'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- totales  oficiales: vienen de la columna subtotal/iva/total. -->
        <table class="table table-sm mt-3" style="max-width: 320px; margin-left: auto;">
            <tr>
                <th>Subtotal</th>
                <td class="text-right">$<?= number_format((float) $contrato['subtotal'], 2) ?></td>
            </tr>
            <tr>
                <th>IVA (16%)</th>
                <td class="text-right">$<?= number_format((float) $contrato['iva'], 2) ?></td>
            </tr>
            <tr>
                <th>Total</th>
                <td class="text-right"><strong>$<?= number_format((float) $contrato['total'], 2) ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
