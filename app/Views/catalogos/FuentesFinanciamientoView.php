<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevaFuente">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Fuente
    </button>
</div>

<!-- Tabla de fuentes -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaFuentes" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyFuentes">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear -->
<div class="modal fade" id="modalNuevaFuente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nueva Fuente de Financiamiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formFuente">
                    <div class="form-group">
                        <label>Clave (va en el folio, ej. FORTAMUN)</label>
                        <input type="text" class="form-control" id="clave" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                </form>
                <div id="alertaError" class="alert alert-danger d-none mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Editar -->
<div class="modal fade" id="modalEditarFuente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Fuente de Financiamiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarFuente">
                    <input type="hidden" id="edit_id">

                    <div class="form-group">
                        <label>Clave (va en el folio)</label>
                        <input type="text" class="form-control" id="edit_clave" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" class="form-control" id="edit_nombre" required>
                    </div>
                </form>
                <div id="alertaErrorEdit" class="alert alert-danger d-none mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizar">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    cargarFuentes();

    document.getElementById('btnGuardar').addEventListener('click', function() {
        guardarFuente();
    });
});

function cargarFuentes()
{
    fetch('<?= base_url('fuentes-financiamiento/list') ?>',
    {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {

        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaFuentes')) {
            $('#tablaFuentes').DataTable().clear().destroy();
        }

        const tbody = document.getElementById('tbodyFuentes');
        tbody.innerHTML = '';

        data.forEach(fuente => {
            let estado = fuente.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            tbody.innerHTML += `
                <tr>
                    <td>${fuente.id}</td>
                    <td>${fuente.clave}</td>
                    <td>${fuente.nombre}</td>
                    <td>${estado}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editarFuente(
                        ${fuente.id}, '${fuente.clave}', '${fuente.nombre}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" onclick="eliminarFuente(${fuente.id})" btn-sm"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });

        setTimeout(() => {
            if ($.fn.DataTable) {
                $('#tablaFuentes').DataTable({
                    "language": {
                        "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            } else {
                console.error("DataTables no está cargado.");
            }
        }, 100);
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function guardarFuente()
{
    const datosFuente =
    {
        clave: document.getElementById('clave').value.toUpperCase(),
        nombre: document.getElementById('nombre').value
    };

    fetch('<?= base_url('fuentes-financiamiento/crear') ?>',
    {
        method: 'POST',
        headers:
        {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosFuente)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201)
        {
            $('#modalNuevaFuente').modal('hide');
            document.getElementById('formFuente').reset();
            document.getElementById('alertaError').classList.add('d-none');

            cargarFuentes();
            mostrarToast(data.message, 'success');
        }
        else
        {
            let divError = document.getElementById('alertaError');
            divError.innerHTML = data.message || 'Revisa los datos capturados.';
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error));
}

function eliminarFuente(id)
{
    if (confirm("¿Estás seguro de eliminar esta fuente de financiamiento?"))
    {
        fetch(`<?= base_url('fuentes-financiamiento/eliminar/') ?>${id}`,
        {
            method: 'POST',
            headers:
            {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200)
            {
                mostrarToast(data.message, 'success');
                cargarFuentes();
            }
            else
            {
                mostrarToast('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => console.error("Error al eliminar:", error));
    }
}
function editarFuente(id, clave, nombre) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_clave').value = clave;
    document.getElementById('edit_nombre').value = nombre;

    document.getElementById('alertaErrorEdit').classList.add('d-none');

    $('#modalEditarFuente').modal('show');
}

document.getElementById('btnActualizar').addEventListener('click', function()
{
    const id = document.getElementById('edit_id').value;

    const datosActualizados =
    {
        clave: document.getElementById('edit_clave').value.toUpperCase(),
        nombre: document.getElementById('edit_nombre').value
    };

    let btnActualizar = document.getElementById('btnActualizar');
    btnActualizar.disabled = true;
    btnActualizar.innerText = "Actualizando...";

    fetch(`<?= base_url('fuentes-financiamiento/actualizar/') ?>${id}`,
    {
        method: 'POST',
        headers:
        {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosActualizados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200)
        {
            $('#modalEditarFuente').modal('hide');
            cargarFuentes();
            mostrarToast(data.message, 'success');
        }
        else
        {
            let divError = document.getElementById('alertaErrorEdit');
            divError.innerHTML = data.message || "Error de validación.";
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error))
    .finally(() =>
    {
        btnActualizar.disabled = false;
        btnActualizar.innerText = "Actualizar";
    });
});
</script>
<?= $this->endSection() ?>
