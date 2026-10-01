<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevaArea">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Área
    </button>
</div>

<!-- Tabla de áreas -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaAreas" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Área</th>
                        <th>Titular</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyAreas">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalNuevaArea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nueva Área</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formArea">
                    <div class="form-group">
                        <label>Nombre del Área</label>
                        <input type="text" class="form-control" id="nombre_area" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Titular</label>
                        <input type="text" class="form-control" id="titular_area" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo del Titular</label>
                        <input type="text" class="form-control" id="cargo_titular" placeholder="Director, Secretario" required>
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
    cargarAreas();

    document.getElementById('btnGuardar').addEventListener('click', function() {
        guardarArea();
    });
});

function cargarAreas() 
{
    fetch('<?= base_url('areas/list') ?>', 
    {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
       
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaAreas')) {
            $('#tablaAreas').DataTable().clear().destroy();
        }
        
        const tbody = document.getElementById('tbodyAreas');
        tbody.innerHTML = ''; 

        data.forEach(area => {
            let estado = area.activo == 1 
                ? '<span class="badge badge-success">Activo</span>' 
                : '<span class="badge badge-danger">Inactivo</span>';
                
            tbody.innerHTML += `
                <tr>
                    <td>${area.id}</td>
                    <td>${area.nombre_area}</td>
                    <td>${area.titular_area}</td>
                    <td>${area.cargo_titular}</td>
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
                $('#tablaAreas').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            } else {
                console.error("DataTables no está cargado.");
            }
        }, 100);
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function guardarArea() {
    const datosArea = {
        nombre_area: document.getElementById('nombre_area').value,
        titular_area: document.getElementById('titular_area').value,
        cargo_titular: document.getElementById('cargo_titular').value
    };

    fetch('<?= base_url('areas/crear') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosArea)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201) {
            $('#modalNuevaArea').modal('hide');
            document.getElementById('formArea').reset();
            document.getElementById('alertaError').classList.add('d-none');
            
            cargarAreas(); 
            alert(data.message); 
        } else {
            let divError = document.getElementById('alertaError');
            divError.innerHTML = data.message;
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error));
}
</script>
<?= $this->endSection() ?>