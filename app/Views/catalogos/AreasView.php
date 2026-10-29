<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" id="btnNuevo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Área
    </button>
</div>

<p class="text-muted">Haz clic en una fila para ver, editar, desactivar o eliminar esa área.</p>

<!-- Tabla de áreas -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaAreas" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre del Área</th>
                        <th>Titular</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyAreas">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: alta y edición comparten el mismo formulario -->
<div class="modal fade" id="modalArea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalArea">Área</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formArea">
                    <input type="hidden" id="campo_id">
                    <div class="form-group">
                        <label>Nombre del Área</label>
                        <input type="text" class="form-control" id="campo_nombre_area" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Titular</label>
                        <input type="text" class="form-control" id="campo_titular_area" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo del Titular</label>
                        <input type="text" class="form-control" id="campo_cargo_titular" placeholder="Director, Secretario" required>
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
let areaActual = null;

document.addEventListener("DOMContentLoaded", function() {
    cargarAreas();

    document.getElementById('btnNuevo').addEventListener('click', () => abrirModal(null));
    document.getElementById('tbodyAreas').addEventListener('click', function (e) {
        const fila = e.target.closest('tr[data-item]');
        if (!fila) return;
        abrirModal(JSON.parse(fila.dataset.item));
    });

    document.getElementById('btnGuardar').addEventListener('click', guardarArea);
    document.getElementById('btnDesactivar').addEventListener('click', cambiarEstado);
    document.getElementById('btnEliminar').addEventListener('click', eliminarArea);
});

function cargarAreas()
{
    fetch('<?= base_url('areas/list') ?>',
    {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('tbodyAreas');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin áreas.</td></tr>';
        }

        data.forEach(area => {
            const estado = area.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            const fila = document.createElement('tr');
            fila.style.cursor = 'pointer';
            fila.dataset.item = JSON.stringify(area);
            fila.innerHTML = `
                <td>${area.nombre_area}</td>
                <td>${area.titular_area}</td>
                <td>${area.cargo_titular}</td>
                <td>${estado}</td>
            `;
            tbody.appendChild(fila);
        });
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function abrirModal(area)
{
    areaActual = area;

    document.getElementById('formArea').reset();
    document.getElementById('alertaErrorModal').classList.add('d-none');

    if (area) {
        document.getElementById('tituloModalArea').textContent = 'Editar Área';
        document.getElementById('campo_id').value = area.id;
        document.getElementById('campo_nombre_area').value = area.nombre_area;
        document.getElementById('campo_titular_area').value = area.titular_area;
        document.getElementById('campo_cargo_titular').value = area.cargo_titular;

        document.getElementById('btnDesactivar').classList.remove('d-none');
        document.getElementById('btnEliminar').classList.remove('d-none');
        document.getElementById('textoDesactivar').textContent = area.activo == 1 ? 'Desactivar' : 'Activar';
    } else {
        document.getElementById('tituloModalArea').textContent = 'Registrar Nueva Área';
        document.getElementById('campo_id').value = '';
        document.getElementById('btnDesactivar').classList.add('d-none');
        document.getElementById('btnEliminar').classList.add('d-none');
    }

    $('#modalArea').modal('show');
}

function datosFormulario()
{
    return {
        nombre_area:   document.getElementById('campo_nombre_area').value,
        titular_area:  document.getElementById('campo_titular_area').value,
        cargo_titular: document.getElementById('campo_cargo_titular').value
    };
}

function guardarArea()
{
    const id  = document.getElementById('campo_id').value;
    const url = id
        ? `<?= base_url('areas/actualizar/') ?>${id}`
        : `<?= base_url('areas/crear') ?>`;

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
    .then(data => {
        if (data.status === 200 || data.status === 201) {
            $('#modalArea').modal('hide');
            cargarAreas();
            mostrarToast(data.message, 'success');
        } else {
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
    if (!areaActual) return;

    fetch(`<?= base_url('areas/estado/') ?>${areaActual.id}`,
    {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalArea').modal('hide');
            cargarAreas();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error cambiando estado:", error));
}

function eliminarArea()
{
    if (!areaActual) return;
    if (!confirm("¿Estás seguro de eliminar esta área?")) return;

    fetch(`<?= base_url('areas/eliminar/') ?>${areaActual.id}`,
    {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalArea').modal('hide');
            cargarAreas();
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error al eliminar:", error));
}
</script>
<?= $this->endSection() ?>