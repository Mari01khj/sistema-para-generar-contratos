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
                            <th>Folio (prefijo / siglas)</th>
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
                                    <form method="POST" action="<?= site_url('tipos-contrato/' . $tipo['id'] . '/folio') ?>" class="form-inline">
                                        <input type="text" name="prefijo_fijo" class="form-control form-control-sm mr-1 mb-1"
                                            style="width: 100px;" placeholder="TEM/CAS"
                                            value="<?= esc($tipo['prefijo_fijo'] ?? '') ?>">
                                        <input type="text" name="siglas_procedimiento" class="form-control form-control-sm mr-1 mb-1"
                                            style="width: 70px; text-transform: uppercase;" placeholder="CP"
                                            value="<?= esc($tipo['siglas_procedimiento'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary mb-1" title="Guardar">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <?php if (! empty($tipo['plantilla'])): ?>
                                        <i class="fas fa-file-word text-primary"></i> <?= esc(basename($tipo['plantilla'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">— sin plantilla —</span>
                                    <?php endif; ?>

                                   
                                    <form method="POST" action="<?= site_url('tipos-contrato/' . $tipo['id'] . '/plantilla') ?>"
                                        enctype="multipart/form-data" class="mt-1">
                                        <input type="file" name="plantilla" accept=".docx,.doc,.xlsx,.xls"
                                            class="form-control-file"
                                            style="font-size: 0.75rem;"
                                            onchange="if (this.files.length) { this.form.submit(); }"
                                            title="Reemplazar plantilla">
                                    </form>
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
                                    <a href="<?= url_to('camposFormularioGestion', $tipo['id']) ?>" class="btn btn-sm btn-outline-primary">
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

<!--nuevo tipo de contrato -->
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
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Prefijo fijo del folio</label>
                            <input type="text" name="prefijo_fijo" class="form-control" placeholder="Ej. TEM/CAS">
                        </div>
                        <div class="form-group col-6">
                            <label>Siglas del procedimiento</label>
                            <input type="text" name="siglas_procedimiento" class="form-control" style="text-transform: uppercase;" placeholder="Ej. CP">
                        </div>
                    </div>
                    <small class="form-text text-muted mb-3 d-block">
                        Estos dos campos se pueden dejar vacíos y llenar después desde la tabla.
                    </small>
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
