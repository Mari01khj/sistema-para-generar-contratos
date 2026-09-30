<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Contratos</h1>
    <a href="<?= site_url('contratos/nuevo') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo contrato
    </a>
</div>

<?php if (session()->getFlashdata('mensaje')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('mensaje')) ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Contratos registrados</h6>
    </div>
    <div class="card-body">
        <?php if (empty($contratos)): ?>
            <p class="text-muted mb-0">Todavía no hay contratos. Crea el primero con el botón de arriba.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tipo</th>
                            <th>Proveedor</th>
                            <th>Área</th>
                            <th>Estado</th>
                            <th>Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos as $contrato): ?>
                            <tr>
                                <td><code><?= esc($contrato['folio']) ?></code></td>
                                <td><?= esc($contrato['tipo_nombre']) ?></td>
                                <td><?= esc($contrato['razon_social']) ?></td>
                                <td><?= esc($contrato['nombre_area']) ?></td>
                                <td><span class="badge badge-secondary"><?= esc($contrato['estado']) ?></span></td>
                                <td><?= esc($contrato['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2">
                Ver detalle, editar y eliminar contratos se agregan en el siguiente paso.
            </p>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
