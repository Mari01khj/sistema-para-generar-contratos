<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tipos de Contrato</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevoTipo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo tipo de contrato
    </button>
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
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tipos registrados</h6>
    </div>
    <div class="card-body">
        <?php if (empty($tipos)): ?>
            <p class="text-muted mb-0">Todavía no hay tipos de contrato. Crea el primero con el botón de arriba.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Plantilla</th>
                            <th>Estado</th>
                            <th>Campos llenables</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tipos as $tipo): ?>
                            <tr>
                                <td><?= esc($tipo['nombre']) ?></td>
                                <td>
                                    <?php if (! empty($tipo['plantilla'])): ?>
                                        <i class="fas fa-file-word text-primary"></i> <?= esc(basename($tipo['plantilla'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">— sin plantilla —</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tipo['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= (int) ($conteoCampos[$tipo['id']] ?? 0) ?> campo(s)
                                </td>
                                <td>
                                    <a href="<?= route_to('camposFormularioGestion', $tipo['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-list-ul"></i> Gestionar campos
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: nuevo tipo de contrato -->
<div class="modal fade" id="modalNuevoTipo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('tipos-contrato/crear') ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo tipo de contrato</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Arrendamiento" required>
                    </div>
                    <div class="form-group">
                        <label>Plantilla del machote (.docx)</label>
                        <input type="file" name="plantilla" class="form-control-file" accept=".docx,.doc,.xlsx,.xls" required>
                        <small class="form-text text-muted">
                            Este archivo es  el que el sistema va a rellenar. 
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <script>document.addEventListener('DOMContentLoaded', () => $('#modalNuevoTipo').modal('show'));</script>
<?php endif; ?>

<?= $this->endSection() ?>
