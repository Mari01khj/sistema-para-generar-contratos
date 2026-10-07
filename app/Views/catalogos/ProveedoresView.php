<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevoProveedor">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Proveedor
    </button>
</div>

<!-- Tabla de Proveedores -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaProveedores" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Representante</th>
                        <th>RFC</th>
                        <th>Actividad Económica</th>
                        <th>Domicilio</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyProveedores">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalNuevoProveedor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nuevo Proveedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProveedor">
                    <div class="form-group">
                        <label>Nombre del Proveedor</label>
                        <input type="text" class="form-control" id="razon_social" required>
                    </div>
                    <div class="form-group">
                        <label>RFC</label>
                        <input type="text" class="form-control" id="rfc" required>
                    </div>
                    <div class="form-group">
                        <label>Representante Legal</label>
                        <input type="text" class="form-control" id="representante_legal" required>
                    </div>
                    <div class="form-group">
                        <label>Domicilio Fiscal</label>
                        <input type="text" class="form-control" id="domicilio_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label>Actividad Económica</label>
                        <input type="text" class="form-control" id="actividad_economica" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <!-- CORREGIDO: id="correo" para que coincida con el script -->
                        <input type="email" class="form-control" id="correo" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="telefono" required>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    cargarProveedores();

    document.getElementById('btnGuardar').addEventListener('click', function() {
        guardarProveedor();
    });
});

function cargarProveedores() {
    fetch('<?= base_url('proveedores/list') ?>', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        // Limpiar tabla si ya es un datatable
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaProveedores')) {
            $('#tablaProveedores').DataTable().clear().destroy();
        }
        
        const tbody = document.getElementById('tbodyProveedores');
        tbody.innerHTML = ''; 

        data.forEach(proveedor => {
            let estado = proveedor.activo == 1 
                ? '<span class="badge badge-success">Activo</span>' 
                : '<span class="badge badge-danger">Inactivo</span>';
                
            // CORREGIDO: Orden idéntico al <thead> y etiquetas </td> bien cerradas
            tbody.innerHTML += `
                <tr>
                    <td>${proveedor.id}</td>
                    <td>${proveedor.razon_social}</td>
                    <td>${proveedor.representante_legal}</td>
                    <td>${proveedor.rfc}</td>
                    <td>${proveedor.actividad_economica}</td>
                    <td>${proveedor.domicilio_fiscal}</td>
                    <td>${proveedor.telefono}</td>
                    <td>${proveedor.correo}</td>
                    <td>${estado}</td>
                    <td>
                        <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });

        // Inicializar DataTable
        setTimeout(() => {
            if ($.fn.DataTable) {
                $('#tablaProveedores').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            }
        }, 100);
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function guardarProveedor() {
    const datosProveedor = {
        razon_social: document.getElementById('razon_social').value,
        rfc: document.getElementById('rfc').value,
        representante_legal: document.getElementById('representante_legal').value,
        domicilio_fiscal: document.getElementById('domicilio_fiscal').value,
        actividad_economica: document.getElementById('actividad_economica').value,
        correo: document.getElementById('correo').value,
        telefono: document.getElementById('telefono').value
    };

    let btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;

    fetch('<?= base_url('proveedores/crear') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosProveedor)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201) {
            $('#modalNuevoProveedor').modal('hide');
            document.getElementById('formProveedor').reset();
            document.getElementById('alertaError').classList.add('d-none');
            
            cargarProveedores(); 
            alert(data.message); 
        } else {
            let divError = document.getElementById('alertaError');
            divError.innerHTML = data.message;
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error))
    .finally(() => {
        btnGuardar.disabled = false;
    });
}
</script>
<?= $this->endSection() ?>