<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" id="btnNuevo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Fuente
    </button>
</div>

<p class="text-muted">Haz clic en una fila para editar, desactivar o eliminar esa fuente.</p>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaFuentes" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyFuentes"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: alta y edición comparten el mismo formulario -->
<div class="modal fade" id="modalFuente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalFuente">Fuente de financiamiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formFuente">
                    <input type="hidden" id="campo_id">
                    <div class="form-group">
                        <label>Clave (va en el folio, ej. FORTAMUN)</label>
                        <input type="text" class="form-control" id="campo_clave" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" class="form-control" id="campo_nombre" required>
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
let fuenteActual = null;

document.addEventListener("DOMContentLoaded", function () {
    cargarFuentes();

    document.getElementById('btnNuevo').addEventListener('click', () => abrirModal(null));
    document.getElementById('tbodyFuentes').addEventListener('click', function (e) {
        const fila = e.target.closest('tr[data-item]');
        if (!fila) return;
        abrirModal(JSON.parse(fila.dataset.item));
    });

    document.getElementById('btnGuardar').addEventListener('click', guardarFuente);
    document.getElementById('btnDesactivar').addEventListener('click', cambiarEstado);
    document.getElementById('btnEliminar').addEventListener('click', eliminarFuente);
});

function cargarFuentes() {
    fetch('<?= base_url('fuentes-financiamiento/list') ?>', { headers: { 'Accept': 'application/json' } })
    .then(response => response.json())
    .then(data => {
        TablaDatos.limpiar('#tablaFuentes');
        const tbody = document.getElementById('tbodyFuentes');
        tbody.innerHTML = '';

        data.forEach(fuente => {
            const estado = fuente.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            const fila = document.createElement('tr');
            fila.dataset.item = JSON.stringify(fuente);
            fila.innerHTML = `
                <td>${fuente.clave}</td>
                <td>${fuente.nombre}</td>
                <td>${estado}</td>
            `;
            tbody.appendChild(fila);
        });

        TablaDatos.activar('#tablaFuentes');
    })
    .catch(error => console.error('Error cargando la tabla:', error));
}

function abrirModal(fuente) {
    fuenteActual = fuente;

    document.getElementById('formFuente').reset();
    document.getElementById('alertaErrorModal').classList.add('d-none');

    if (fuente) {
        document.getElementById('tituloModalFuente').textContent = 'Editar Fuente de Financiamiento';
        document.getElementById('campo_id').value = fuente.id;
        document.getElementById('campo_clave').value = fuente.clave;
        document.getElementById('campo_nombre').value = fuente.nombre;

        document.getElementById('btnDesactivar').classList.remove('d-none');
        document.getElementById('btnEliminar').classList.remove('d-none');
        document.getElementById('textoDesactivar').textContent = fuente.activo == 1 ? 'Desactivar' : 'Activar';
    } else {
        document.getElementById('tituloModalFuente').textContent = 'Registrar Nueva Fuente de Financiamiento';
        document.getElementById('campo_id').value = '';
        document.getElementById('btnDesactivar').classList.add('d-none');
        document.getElementById('btnEliminar').classList.add('d-none');
    }

    $('#modalFuente').modal('show');
}

function guardarFuente() {
    const id  = document.getElementById('campo_id').value;
    const url = id
        ? `<?= base_url('fuentes-financiamiento/actualizar/') ?>${id}`
        : `<?= base_url('fuentes-financiamiento/crear') ?>`;

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
            clave:  document.getElementById('campo_clave').value.trim().toUpperCase(),
            nombre: document.getElementById('campo_nombre').value.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200 || data.status === 201) {
            $('#modalFuente').modal('hide');
            cargarFuentes();
            mostrarToast(data.message, 'success');
        } else {
            const errores = data.errors ? Object.values(data.errors).join('<br>') : null;
            const div = document.getElementById('alertaErrorModal');
            div.innerHTML = errores || data.message || 'Error de validación.';
            div.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error))
    .finally(() => { btn.disabled = false; });
}

function cambiarEstado() {
    if (!fuenteActual) return;

    fetch(`<?= base_url('fuentes-financiamiento/estado/') ?>${fuenteActual.id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalFuente').modal('hide');
            cargarFuentes();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error('Error cambiando estado:', error));
}

async function eliminarFuente() {
    if (!fuenteActual) return;
    if (!(await confirmar('Se eliminará esta fuente de financiamiento. Esta acción no se puede deshacer.'))) return;

    fetch(`<?= base_url('fuentes-financiamiento/eliminar/') ?>${fuenteActual.id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalFuente').modal('hide');
            cargarFuentes();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error('Error al eliminar:', error));
}
</script>
<?= $this->endSection() ?>
