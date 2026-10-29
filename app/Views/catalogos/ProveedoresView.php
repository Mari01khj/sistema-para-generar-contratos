<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" id="btnNuevo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Proveedor
    </button>
</div>

<p class="text-muted">Haz clic en una fila para ver, editar, desactivar o eliminar ese proveedor.</p>

<!-- Tabla de Proveedores -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaProveedores" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Representante</th>
                        <th>RFC</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyProveedores">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: alta y edición comparten el mismo formulario -->
<div class="modal fade" id="modalProveedor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalProveedor">Proveedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProveedor">
                    <input type="hidden" id="campo_id">
                    <div class="form-group">
                        <label>Nombre del Proveedor</label>
                        <input type="text" class="form-control" id="campo_razon_social" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipo de persona</label>
                            <select class="form-control" id="campo_tipo_persona" required>
                                <option value="">Selecciona...</option>
                                <option value="fisica">Física</option>
                                <option value="moral">Moral</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>RFC</label>
                            <input type="text" class="form-control" id="campo_rfc" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Representante Legal</label>
                            <input type="text" class="form-control" id="campo_representante_legal" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Folio INE del representante</label>
                            <input type="text" class="form-control" id="campo_folio_ine">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>NCI</label>
                        <input type="text" class="form-control" id="campo_nci">
                    </div>
                    <div class="form-group">
                        <label>Domicilio Fiscal</label>
                        <input type="text" class="form-control" id="campo_domicilio_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label>Actividad Económica</label>
                        <input type="text" class="form-control" id="campo_actividad_economica" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Correo Electrónico</label>
                            <input type="email" class="form-control" id="campo_correo" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" id="campo_telefono" required>
                        </div>
                    </div>
                </form>
                <div id="alertaErrorModal" class="alert alert-danger d-none mt-3"></div>
            </div>
            <div class="modal-footer flex-column align-items-stretch">
                <div class="d-flex justify-content-between mb-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
                <button type="button" class="btn btn-outline-warning d-none" id="btnDesactivar">
                    <i class="fas fa-ban"></i> <span id="textoDesactivar">Desactivar</span>
                </button>
                <button type="button" class="btn btn-link text-danger d-none" id="btnEliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let proveedorActual = null;

document.addEventListener("DOMContentLoaded", function()
{
    cargarProveedores();

    document.getElementById('btnNuevo').addEventListener('click', () => abrirModal(null));
    document.getElementById('tbodyProveedores').addEventListener('click', function (e) {
        const fila = e.target.closest('tr[data-item]');
        if (!fila) return;
        abrirModal(JSON.parse(fila.dataset.item));
    });

    document.getElementById('btnGuardar').addEventListener('click', guardarProveedor);
    document.getElementById('btnDesactivar').addEventListener('click', cambiarEstado);
    document.getElementById('btnEliminar').addEventListener('click', eliminarProveedor);
});

function cargarProveedores()
{
    fetch('<?= base_url('proveedores/list') ?>',
    {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data =>
    {
        const tbody = document.getElementById('tbodyProveedores');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin proveedores.</td></tr>';
        }

        data.forEach(proveedor =>
        {
            const estado = proveedor.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            const fila = document.createElement('tr');
            fila.style.cursor = 'pointer';
            fila.dataset.item = JSON.stringify(proveedor);
            fila.innerHTML = `
                <td>${proveedor.razon_social}</td>
                <td>${proveedor.representante_legal ?? '—'}</td>
                <td>${proveedor.rfc}</td>
                <td>${proveedor.telefono ?? '—'}</td>
                <td>${proveedor.correo ?? '—'}</td>
                <td>${estado}</td>
            `;
            tbody.appendChild(fila);
        });
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function abrirModal(proveedor)
{
    proveedorActual = proveedor;

    document.getElementById('formProveedor').reset();
    document.getElementById('alertaErrorModal').classList.add('d-none');

    if (proveedor) {
        document.getElementById('tituloModalProveedor').textContent = 'Editar Proveedor';
        document.getElementById('campo_id').value = proveedor.id;
        document.getElementById('campo_razon_social').value = proveedor.razon_social ?? '';
        document.getElementById('campo_tipo_persona').value = proveedor.tipo_persona ?? '';
        document.getElementById('campo_rfc').value = proveedor.rfc ?? '';
        document.getElementById('campo_representante_legal').value = proveedor.representante_legal ?? '';
        document.getElementById('campo_folio_ine').value = proveedor.folio_ine ?? '';
        document.getElementById('campo_nci').value = proveedor.nci ?? '';
        document.getElementById('campo_domicilio_fiscal').value = proveedor.domicilio_fiscal ?? '';
        document.getElementById('campo_actividad_economica').value = proveedor.actividad_economica ?? '';
        document.getElementById('campo_correo').value = proveedor.correo ?? '';
        document.getElementById('campo_telefono').value = proveedor.telefono ?? '';

        document.getElementById('btnDesactivar').classList.remove('d-none');
        document.getElementById('btnEliminar').classList.remove('d-none');
        document.getElementById('textoDesactivar').textContent = proveedor.activo == 1 ? 'Desactivar' : 'Activar';
    } else {
        document.getElementById('tituloModalProveedor').textContent = 'Registrar Nuevo Proveedor';
        document.getElementById('campo_id').value = '';
        document.getElementById('btnDesactivar').classList.add('d-none');
        document.getElementById('btnEliminar').classList.add('d-none');
    }

    $('#modalProveedor').modal('show');
}

function datosFormulario()
{
    return {
        razon_social:        document.getElementById('campo_razon_social').value,
        tipo_persona:        document.getElementById('campo_tipo_persona').value,
        rfc:                 document.getElementById('campo_rfc').value,
        representante_legal: document.getElementById('campo_representante_legal').value,
        folio_ine:           document.getElementById('campo_folio_ine').value,
        nci:                 document.getElementById('campo_nci').value,
        domicilio_fiscal:    document.getElementById('campo_domicilio_fiscal').value,
        actividad_economica: document.getElementById('campo_actividad_economica').value,
        correo:              document.getElementById('campo_correo').value,
        telefono:            document.getElementById('campo_telefono').value
    };
}

function guardarProveedor()
{
    const id  = document.getElementById('campo_id').value;
    const url = id
        ? `<?= base_url('proveedores/actualizar/') ?>${id}`
        : `<?= base_url('proveedores/crear') ?>`;

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;

    fetch(url,
    {
        method: 'POST',
        headers:
        {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosFormulario())
    })
    .then(response => response.json())
    .then(data =>
    {
        if (data.status === 200 || data.status === 201)
        {
            $('#modalProveedor').modal('hide');
            cargarProveedores();
            mostrarToast(data.message, 'success');
        }
        else
        {
            const div = document.getElementById('alertaErrorModal');
            div.innerHTML = data.message || 'Error de validación.';
            div.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error))
    .finally(() => { btn.disabled = false; });
}

function cambiarEstado()
{
    if (!proveedorActual) return;

    fetch(`<?= base_url('proveedores/estado/') ?>${proveedorActual.id}`,
    {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data =>
    {
        if (data.status === 200) {
            $('#modalProveedor').modal('hide');
            cargarProveedores();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error cambiando estado:", error));
}

function eliminarProveedor()
{
    if (!proveedorActual) return;
    if (!confirm("¿Estás seguro de eliminar este proveedor?")) return;

    fetch(`<?= base_url('proveedores/eliminar/') ?>${proveedorActual.id}`,
    {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data =>
    {
        if (data.status === 200) {
            $('#modalProveedor').modal('hide');
            cargarProveedores();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error al eliminar:", error));
}
</script>
<?= $this->endSection() ?>