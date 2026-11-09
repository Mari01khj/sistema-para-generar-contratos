<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<?php
$estados   = \App\Models\ContratosModel::ESTADOS;
$estado    = $contrato['estado'];
[$etiquetaEstado, $colorEstado] = $estados[$estado] ?? [$estado, 'secondary'];
$esAdmin   = (int) session()->get('rol_id') === 1;
$bloqueado = $estado === 'finalizado';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= site_url('contratos') ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> Contratos</a>
        <h1 class="h3 mb-0 text-gray-800">
            <?= esc($contrato['folio']) ?>
            <span class="badge badge-<?= $colorEstado ?> align-middle" style="font-size: .5em;"><?= esc($etiquetaEstado) ?></span>
        </h1>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="<?= site_url('contratos/' . $contrato['id'] . '/documento') ?>" class="btn btn-success">
            <i class="fas fa-file-word"></i> Descargar documento
        </a>

        <?php if ($estado === 'borrador'): ?>
            <form method="POST" action="<?= site_url('contratos/' . $contrato['id'] . '/correccion') ?>" class="d-inline">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-pencil-alt"></i> Marcar en corrección
                </button>
            </form>
        <?php endif; ?>

        <?php if (in_array($estado, ['borrador', 'en_correccion'], true)): ?>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalFinalizar">
                <i class="fas fa-check-circle"></i> Subir contrato final
            </button>
        <?php endif; ?>

        <?php if ($bloqueado): ?>
            <?php if (! empty($contrato['archivo_final'])): ?>
                <a href="<?= site_url('contratos/' . $contrato['id'] . '/archivo-final') ?>" class="btn btn-info">
                    <i class="fas fa-file-download"></i> Contrato final
                </a>
            <?php endif; ?>
            <?php if ($esAdmin): ?>
                <form method="POST" action="<?= site_url('contratos/' . $contrato['id'] . '/reabrir') ?>" class="d-inline"
                    data-confirmar="El contrato volverá a quedar en corrección y se podrá editar de nuevo."
                    data-confirmar-titulo="¿Reabrir contrato?" data-confirmar-boton="Reabrir">
                    <button type="submit" class="btn btn-outline-warning">
                        <i class="fas fa-lock-open"></i> Reabrir
                    </button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= site_url('contratos/' . $contrato['id'] . '/editar') ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form method="POST" action="<?= site_url('contratos/' . $contrato['id'] . '/eliminar') ?>" class="d-inline"
                data-confirmar="¿Eliminar el contrato <?= esc($contrato['folio'], 'attr') ?>? Esta acción no se puede deshacer desde la pantalla.">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($bloqueado): ?>
    <div class="alert alert-success">
        <i class="fas fa-lock"></i>
        Contrato finalizado el <?= esc(substr((string) $contrato['fecha_finalizacion'], 0, 16)) ?>.
        Ya no se puede editar ni eliminar.
    </div>
<?php endif; ?>

<!-- Subir el archivo final para finalizar el contrato -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" enctype="multipart/form-data"
            action="<?= site_url('contratos/' . $contrato['id'] . '/finalizar') ?>">
            <div class="modal-header">
                <h5 class="modal-title">Subir contrato final</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    Sube el contrato ya corregido (PDF o Word, máximo 10 MB). Al guardarlo, el contrato
                    pasa a <strong>Finalizado</strong> y deja de poder editarse.
                </p>
                <input type="file" name="archivo_final" class="form-control-file" accept=".pdf,.docx" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Finalizar contrato</button>
            </div>
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
                <strong>Área usuaria / requirente</strong><br>
                <?= esc($contrato['nombre_area']) ?>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Área técnica</strong><br>
                <?= esc($contrato['nombre_area_tecnica'] ?? $contrato['nombre_area']) ?>
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

        <!-- Estos totales SÍ son los oficiales -->

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