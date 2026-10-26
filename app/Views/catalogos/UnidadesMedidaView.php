<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
</div>

<p class="text-muted">
    Se eligen de esta lista al capturar un bien/servicio, para que nadie las escriba
    de varias formas distintas (ej. "PIEZA", "PZA", "PZ" como si fueran 3 unidades
    diferentes).
</p>

<!-- Agregar un  input + botón arriba -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="form-row align-items-end">
            <div class="col-md-8">
                <label for="nombreNuevaUnidad">Nueva unidad</label>
                <input type="text" class="form-control" id="nombreNuevaUnidad"
                    style="text-transform: uppercase;" placeholder="Ej. PIEZA, CAJA, LITRO">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-block" id="btnAgregarUnidad">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </div>
        </div>
        <div id="alertaError" class="alert alert-danger d-none mt-3"></div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="tablaUnidades" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 10%;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUnidades"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    cargarUnidades();
    document.getElementById('btnAgregarUnidad').addEventListener('click', guardarUnidad);
    document.getElementById('nombreNuevaUnidad').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            guardarUnidad();
        }
    });
});

function cargarUnidades() {
    fetch('<?= base_url('unidades-medida/list') ?>', { headers: { 'Accept': 'application/json' } })
        .then(response => response.json())
        .then(data => {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaUnidades')) {
                $('#tablaUnidades').DataTable().clear().destroy();
            }

            const tbody = document.getElementById('tbodyUnidades');
            tbody.innerHTML = '';

            data.forEach(unidad => {
                tbody.innerHTML += `
                    <tr>
                        <td>${unidad.nombre}</td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm" onclick="eliminarUnidad(${unidad.id})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            });

            setTimeout(() => {
                if ($.fn.DataTable) {
                    $('#tablaUnidades').DataTable({
                        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                        "order": [[0, 'asc']]
                    });
                }
            }, 100);
        })
        .catch(error => console.error("Error cargando unidades:", error));
}

function guardarUnidad() {
    const campo  = document.getElementById('nombreNuevaUnidad');
    const nombre = campo.value.trim();
    const divError = document.getElementById('alertaError');
    divError.classList.add('d-none');

    if (nombre === '') {
        divError.innerHTML = 'Escribe el nombre de la unidad.';
        divError.classList.remove('d-none');
        return;
    }

    fetch('<?= base_url('unidades-medida/crear') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201) {
            campo.value = '';
            cargarUnidades();
            mostrarToast(data.message, 'success');
        } else {
            divError.innerHTML = (data.errors ? Object.values(data.errors).join('<br>') : data.message)
                || 'Revisa los datos capturados.';
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error));
}

function eliminarUnidad(id) {
    if (! confirm("¿Eliminar esta unidad de medida? Los bienes/servicios que ya la usan se quedan sin unidad.")) {
        return;
    }

    fetch(`<?= base_url('unidades-medida/eliminar/') ?>${id}`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            mostrarToast(data.message, 'success');
            cargarUnidades();
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error al eliminar:", error));
}
</script>
<?= $this->endSection() ?>
