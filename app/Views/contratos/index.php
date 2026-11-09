<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Contratos</h1>
    <a href="<?= site_url('contratos/nuevo') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo contrato
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Contratos registrados</h6>
    </div>
    <div class="card-body">
        <?php if (empty($contratos)): ?>
            <p class="text-muted mb-0">Todavía no hay contratos. Crea el primero con el botón de arriba.</p>
        <?php else: ?>
            <?php
            $estados = \App\Models\ContratosModel::ESTADOS;
            $tipos   = array_values(array_unique(array_column($contratos, 'tipo_nombre')));
            $areas   = array_values(array_unique(array_column($contratos, 'nombre_area')));
            sort($tipos);
            sort($areas);
            ?>
            <div class="form-row mb-3">
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm filtro-contratos" data-columna="5">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as [$etiqueta]): ?>
                            <option value="<?= esc($etiqueta, 'attr') ?>"><?= esc($etiqueta) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-control form-control-sm filtro-contratos" data-columna="1">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= esc($tipo, 'attr') ?>"><?= esc($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <select class="form-control form-control-sm filtro-contratos" data-columna="3">
                        <option value="">Todas las áreas</option>
                        <?php foreach ($areas as $areaNombre): ?>
                            <option value="<?= esc($areaNombre, 'attr') ?>"><?= esc($areaNombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle tabla-datos" id="tablaContratos">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tipo</th>
                            <th>Proveedor</th>
                            <th>Área</th>
                            <th class="text-right">Total</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="no-orden">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos as $contrato): ?>
                            <tr>
                                <td><code><?= esc($contrato['folio']) ?></code></td>
                                <td><?= esc($contrato['tipo_nombre']) ?></td>
                                <td><?= esc($contrato['razon_social']) ?></td>
                                <td><?= esc($contrato['nombre_area']) ?></td>
                                <td class="text-right">$<?= number_format((float) $contrato['total'], 2) ?></td>
                                <?php [$etiquetaEstado, $colorEstado] = $estados[$contrato['estado']] ?? [$contrato['estado'], 'secondary']; ?>
                                <td><span class="badge badge-<?= $colorEstado ?>"><?= esc($etiquetaEstado) ?></span></td>
                                <td><?= esc(substr($contrato['created_at'], 0, 10)) ?></td>
                                <td class="text-nowrao"> 
                                    <a href="<?= url_to('contratoVer', $contrato['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($contrato['estado'] !== 'finalizado'): ?>
                                    <a href="<?= url_to('contratoEditar', $contrato['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?= site_url('contratos/' . $contrato['id'] . '/eliminar') ?>" class="d-inline"
                                        data-confirmar="¿Eliminar el contrato <?= esc($contrato['folio'], 'attr') ?>?">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2">
                
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    const selectores = document.querySelectorAll('.filtro-contratos');
    if (!selectores.length || !$.fn.DataTable.isDataTable('#tablaContratos')) return;
    const tabla = $('#tablaContratos').DataTable();

    selectores.forEach(function (sel) {
        sel.addEventListener('change', function () {
            const texto = sel.value ? '^' + $.fn.dataTable.util.escapeRegex(sel.value) + '$' : '';
            tabla.column(parseInt(sel.dataset.columna, 10)).search(texto, true, false).draw();
        });
    });
});
</script>

<?= $this->endSection() ?>
