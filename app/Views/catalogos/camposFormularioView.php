<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="<?= route_to('tiposContratoGestion') ?>" class="text-muted small"><i class="fas fa-arrow-left"></i> Tipos de contrato</a>
        <h1 class="h3 mb-0 text-gray-800">Campos de: <?= esc($tipoContrato['nombre']) ?></h1>
    </div>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevoCampo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo campo
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
        <h6 class="m-0 font-weight-bold text-primary">Campos llenables de este tipo de contrato</h6>
    </div>
    <div class="card-body">
        <?php if (empty($campos)): ?>
            <p class="text-muted mb-0">
                Este tipo de contrato todavía no tiene campos definidos.
                Agrega el primero con el botón de arriba — por ejemplo "Fecha de entrega" o "Proveedor".
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Orden</th>
                            <th>Etiqueta</th>
                            <th>Nombre técnico</th>
                            <th>Tipo de dato</th>
                            <th>Obligatorio</th>
                            <th>Estado</th>
                            <th style="width: 160px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campos as $campo): ?>
                            <tr class="<?= $campo['deleted_at'] ? 'table-light text-muted' : '' ?>">
                                <td><?= (int) $campo['orden'] ?></td>
                                <td><?= esc($campo['etiqueta']) ?></td>
                                <td><code><?= esc($campo['nombre_campo']) ?></code></td>
                                <td>
                                    <?= esc($tiposDato[$campo['tipo_dato']] ?? $campo['tipo_dato']) ?>
                                    <?php if ($campo['tipo_dato'] === 'lista' && $campo['origen_lista']): ?>
                                        <br><small class="text-muted">de: <?= esc($catalogosLista[$campo['origen_lista']] ?? $campo['origen_lista']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $campo['obligatorio'] ? 'Sí' : 'No' ?></td>
                                <td>
                                    <?php if ($campo['deleted_at']): ?>
                                        <span class="badge badge-secondary">Eliminado</span>
                                    <?php elseif ($campo['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($campo['deleted_at']): ?>
                                        <form action="<?= site_url('campos/' . $campo['id'] . '/activar') ?>" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-undo"></i> Reactivar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-toggle="modal" data-target="#modalEditarCampo"
                                            data-id="<?= $campo['id'] ?>"
                                            data-etiqueta="<?= esc($campo['etiqueta'], 'attr') ?>"
                                            data-nombre-campo="<?= esc($campo['nombre_campo'], 'attr') ?>"
                                            data-tipo-dato="<?= esc($campo['tipo_dato'], 'attr') ?>"
                                            data-origen-lista="<?= esc($campo['origen_lista'], 'attr') ?>"
                                            data-obligatorio="<?= $campo['obligatorio'] ?>"
                                            data-orden="<?= $campo['orden'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="<?= site_url('campos/' . $campo['id'] . '/eliminar') ?>" method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Eliminar el campo \"<?= esc($campo['etiqueta'], 'js') ?>\"? Los contratos que ya lo usan no se ven afectados.');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
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
        <?php endif; ?>
    </div>
</div>

<!-- Modal: nuevo campo -->
<div class="modal fade" id="modalNuevoCampo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('tipos-contrato/' . $tipoContrato['id'] . '/campos/crear') ?>" method="POST" id="formNuevoCampo">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo campo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <?= view('catalogos/_formCampo', [
                        'tiposDato'      => $tiposDato,
                        'catalogosLista' => $catalogosLista,
                        'orden'          => $siguienteOrden,
                        'prefijoId'      => 'nuevo',
                    ]) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: editar campo (un solo modal, se rellena por JS al abrirlo) -->
<div class="modal fade" id="modalEditarCampo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('campos') ?>/0/editar" method="POST" id="formEditarCampo">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar campo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <?= view('catalogos/_formCampo', [
                        'tiposDato'      => $tiposDato,
                        'catalogosLista' => $catalogosLista,
                        'orden'          => 0,
                        'prefijoId'      => 'editar',
                    ]) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Solo comodidades de interfaz: mostrar/ocultar el select de catálogo
// según el tipo de dato, y sugerir un nombre técnico a partir de la
// etiqueta. La validación real (la que de verdad protege los datos)
// vive en el servidor, en CamposFormularioModel — esto es nada más
// para que el formulario sea más agradable de usar.

function quitarAcentos(texto) {
    return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function actualizarVisibilidadLista(prefijo) {
    const tipoDato = document.getElementById(prefijo + '_tipo_dato').value;
    const grupoLista = document.getElementById(prefijo + '_grupo_origen_lista');
    grupoLista.style.display = (tipoDato === 'lista') ? '' : 'none';
}

['nuevo', 'editar'].forEach(function (prefijo) {
    const etiqueta = document.getElementById(prefijo + '_etiqueta');
    const nombreCampo = document.getElementById(prefijo + '_nombre_campo');
    const tipoDato = document.getElementById(prefijo + '_tipo_dato');

    // Autogenerar el nombre técnico mientras el usuario escribe la etiqueta,
    // SOLO si el usuario no lo ha tocado a mano todavía.
    let nombreCampoTocadoManualmente = false;
    nombreCampo.addEventListener('input', () => { nombreCampoTocadoManualmente = true; });
    etiqueta.addEventListener('input', () => {
        if (nombreCampoTocadoManualmente) return;
        nombreCampo.value = quitarAcentos(etiqueta.value)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    });

    tipoDato.addEventListener('change', () => actualizarVisibilidadLista(prefijo));
});

// Al abrir "editar", rellenar el formulario con los data-* del botón que se presionó.
$('#modalEditarCampo').on('show.bs.modal', function (evento) {
    const boton = $(evento.relatedTarget);
    const form = document.getElementById('formEditarCampo');

    form.action = '<?= site_url('campos') ?>/' + boton.data('id') + '/editar';
    document.getElementById('editar_etiqueta').value = boton.data('etiqueta');
    document.getElementById('editar_nombre_campo').value = boton.data('nombre-campo');
    document.getElementById('editar_tipo_dato').value = boton.data('tipo-dato');
    document.getElementById('editar_origen_lista').value = boton.data('origen-lista') || '';
    document.getElementById('editar_obligatorio').checked = boton.data('obligatorio') == 1;
    document.getElementById('editar_orden').value = boton.data('orden');

    actualizarVisibilidadLista('editar');
});
</script>

<?= $this->endSection() ?>
