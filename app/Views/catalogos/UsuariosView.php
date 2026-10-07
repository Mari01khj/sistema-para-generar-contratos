<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevoUsuario">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Usuario
    </button>
</div>

<!-- Tabla de Usuarios -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaUsuarios" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol asignado</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <div class="form-group">
                        <label>Rol asignado 1=Administrador, 2=Operador</label>
                        <input type="text" class="form-control" id="rol_id" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="text" class="form-control" id="correo" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" class="form-control" id="password_hash" required>
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
    cargarUsuarios();

    document.getElementById('btnGuardar').addEventListener('click', function() {
        guardarUsuario();
    });
});

function cargarUsuarios() {
    fetch('<?= base_url('usuarios/list') ?>', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
     
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaUsuarios')) {
            $('#tablaUsuarios').DataTable().clear().destroy();
        }
        {
            $('#tablaUsuarios').DataTable().clear().destroy();
        }
        
        const tbody = document.getElementById('tbodyUsuarios');
        tbody.innerHTML = ''; 

        data.forEach(usuario => {
            let estado = usuario.activo == 1 
                ? '<span class="badge badge-success">Activo</span>' 
                : '<span class="badge badge-danger">Inactivo</span>';
                
      
            tbody.innerHTML += `
                <tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.rol_id}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.correo}</td>
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
                $('#tablaUsuarios').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            }
        }, 100);
    })
    .catch(error => console.error("Error cargando la tabla:", error));
}

function guardarUsuario() {
    const datosUsuario = {
        rol_id: document.getElementById('rol_id').value,
        nombre: document.getElementById('nombre').value,
        correo: document.getElementById('correo').value,
        password_hash: document.getElementById('password_hash').value
    };

    let btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;

    fetch('<?= base_url('usuarios/crear') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datosUsuario)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 201) {
            $('#modalNuevoUsuario').modal('hide');
            document.getElementById('formUsuario').reset();
            document.getElementById('alertaError').classList.add('d-none');
            
            cargarUsuarios(); 
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