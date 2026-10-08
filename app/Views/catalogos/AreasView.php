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

<!-- Modal para crear -->
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
<!-- Modal para Editar -->
<div class="modal fade" id="modalEditarArea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Área</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarArea">
                    <input type="hidden" id="edit_id">
                    
                    <div class="form-group">
                        <label>Nombre del Área</label>
                        <input type="text" class="form-control" id="edit_nombre_area" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Titular</label>
                        <input type="text" class="form-control" id="edit_titular_area" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo del Titular</label>
                        <input type="text" class="form-control" id="edit_cargo_titular" required>
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
                        <button class="btn btn-primary btn-sm" onclick="editarArea(
                        ${area.id}, '${area.nombre_area}', '${area.titular_area}', '${area.cargo_titular}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger" onclick="eliminarArea(${area.id})" btn-sm"><i class="fas fa-trash"></i></button>
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

function guardarArea() 
{
    const datosArea = 
    {
        nombre_area: document.getElementById('nombre_area').value,
        titular_area: document.getElementById('titular_area').value,
        cargo_titular: document.getElementById('cargo_titular').value
    };

    fetch('<?= base_url('areas/crear') ?>', 
    {
        method: 'POST',
        headers: 
        {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosArea)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201) 
        {
            $('#modalNuevaArea').modal('hide');
            document.getElementById('formArea').reset();
            document.getElementById('alertaError').classList.add('d-none');
            
            cargarAreas(); 
            alert(data.message); 
        } 
        else 
        {
            let divError = document.getElementById('alertaError');
            divError.innerHTML = data.message;
            divError.classList.remove('d-none');
        }
    })
    .catch(error => console.error('Error en fetch:', error));
}

function eliminarArea(id) 
{
    if (confirm("¿Estás seguro de eliminar esta área?")) 
    {
        fetch(`<?= base_url('areas/eliminar/') ?>${id}`, 
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
                alert(data.message);
                cargarAreas(); 
            } 
            else 
            {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error al eliminar:", error));
    }
}
function editarArea(id, nombre, titular, cargo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre_area').value = nombre;
    document.getElementById('edit_titular_area').value = titular;
    document.getElementById('edit_cargo_titular').value = cargo;
    
    document.getElementById('alertaErrorEdit').classList.add('d-none');

    $('#modalEditarArea').modal('show');
}

// fech
document.getElementById('btnActualizar').addEventListener('click', function() 
{
    const id = document.getElementById('edit_id').value;
    
    const datosActualizados = 
    {
        nombre_area: document.getElementById('edit_nombre_area').value,
        titular_area: document.getElementById('edit_titular_area').value,
        cargo_titular: document.getElementById('edit_cargo_titular').value
    };

    let btnActualizar = document.getElementById('btnActualizar');
    btnActualizar.disabled = true;
    btnActualizar.innerText = "Actualizando...";

    fetch(`<?= base_url('areas/actualizar/') ?>${id}`, 
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
            $('#modalEditarArea').modal('hide');
            cargarAreas(); 
            alert(data.message);
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