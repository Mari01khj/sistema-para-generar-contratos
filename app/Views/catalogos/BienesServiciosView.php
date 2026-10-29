<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
    <button class="btn btn-primary shadow-sm" id="btnNuevo">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo bien/servicio
    </button>
</div>

<p class="text-muted">Haz clic en una fila para ver, editar, desactivar o eliminar ese registro.</p>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="form-row align-items-end">
            <div class="col-md-6">
                <label for="filtroTexto">Buscar</label>
                <input type="text" class="form-control" id="filtroTexto" placeholder="Nombre del bien o servicio...">
            </div>
            <div class="col-md-4">
                <label for="filtroCategoria">Categoría</label>
                <select class="form-control" id="filtroCategoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= esc($categoria) ?>"><?= esc($categoria) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-block" id="btnBuscar">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div id="resumenResultados" class="text-muted small mb-2"></div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 15%;">Categoría</th>
                        <th style="width: 10%;">Unidad</th>
                        <th style="width: 12%;">Precio</th>
                        <th style="width: 8%;">Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyCatalogo"></tbody>
            </table>
        </div>
        <nav>
            <ul class="pagination justify-content-center" id="paginador"></ul>
        </nav>
    </div>
</div>

<!-- Modal: alta y edición comparten el mismo formulario, igual que el
     modelo de referencia (clic en fila -> ventana con los campos). -->
<div class="modal fade" id="modalBienServicio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Bien o servicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formBienServicio">
                    <input type="hidden" id="campo_id">
                    <div class="form-group">
                        <label>Nombre</label>
                        <textarea class="form-control" id="campo_nombre" rows="2" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Categoría</label>
                            <select class="form-control" id="campo_categoria">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= esc($categoria) ?>"><?= esc($categoria) ?></option>
                                <?php endforeach; ?>
                                <option value="__nueva__">+ Nueva categoría...</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" id="campo_categoria_nueva" placeholder="Nombre de la categoría">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Partida</label>
                            <input type="text" class="form-control" id="campo_partida" placeholder="Ej. 2111">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Unidad de medida</label>
                            <select class="form-control" id="campo_unidad">
                                <option value="">Sin unidad</option>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= esc($unidad['id']) ?>"><?= esc($unidad['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Precio de referencia</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="campo_precio">
                        </div>
                    </div>
                    <small class="form-text text-muted">El precio es el del contrato; si cambia, este queda solo como referencia.</small>
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
let paginaActual = 1;
let registroActual = null;

document.addEventListener("DOMContentLoaded", function () {
    buscarCatalogo(1);
    document.getElementById('btnBuscar').addEventListener('click', () => buscarCatalogo(1));
    document.getElementById('filtroTexto').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarCatalogo(1); }
    });
    document.getElementById('filtroCategoria').addEventListener('change', () => buscarCatalogo(1));

    document.getElementById('btnNuevo').addEventListener('click', () => abrirModal(null));
    document.getElementById('tbodyCatalogo').addEventListener('click', function (e) {
        const fila = e.target.closest('tr[data-item]');
        if (!fila) return;
        abrirModal(JSON.parse(fila.dataset.item));
    });

    document.getElementById('campo_categoria').addEventListener('change', function () {
        document.getElementById('campo_categoria_nueva').classList.toggle('d-none', this.value !== '__nueva__');
    });

    document.getElementById('btnGuardar').addEventListener('click', guardar);
    document.getElementById('btnDesactivar').addEventListener('click', cambiarEstado);
    document.getElementById('btnEliminar').addEventListener('click', eliminar);
});

function formatoMoneda(valor) {
    if (valor === null || valor === undefined || valor === '') return '—';
    return '$' + parseFloat(valor).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function buscarCatalogo(pagina) {
    paginaActual = pagina;

    const q          = document.getElementById('filtroTexto').value.trim();
    const categoria  = document.getElementById('filtroCategoria').value;
    const parametros = new URLSearchParams({ pagina: pagina });
    if (q !== '') parametros.set('q', q);
    if (categoria !== '') parametros.set('categoria', categoria);

    fetch(`<?= base_url('bienes-servicios/list') ?>?${parametros.toString()}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('tbodyCatalogo');
        tbody.innerHTML = '';

        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin resultados.</td></tr>';
        }

        data.data.forEach(item => {
            const estado = item.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            const fila = document.createElement('tr');
            fila.style.cursor = 'pointer';
            fila.dataset.item = JSON.stringify(item);
            fila.innerHTML = `
                <td>${item.nombre}</td>
                <td>${item.categoria ?? '—'}</td>
                <td>${item.unidad_medida ?? '—'}</td>
                <td class="text-right">${formatoMoneda(item.precio)}</td>
                <td>${estado}</td>
            `;
            tbody.appendChild(fila);
        });

        document.getElementById('resumenResultados').textContent =
            `${data.total.toLocaleString('es-MX')} resultado(s) · página ${data.pagina} de ${data.totalPaginas || 1}`;

        dibujarPaginador(data.pagina, data.totalPaginas);
    })
    .catch(error => console.error("Error buscando el catálogo:", error));
}

function dibujarPaginador(pagina, totalPaginas) {
    const ul = document.getElementById('paginador');
    ul.innerHTML = '';
    if (totalPaginas <= 1) return;

    const desde = Math.max(1, pagina - 2);
    const hasta = Math.min(totalPaginas, pagina + 2);

    function agregarBoton(numero, etiqueta, deshabilitado, activo) {
        const li = document.createElement('li');
        li.className = 'page-item' + (activo ? ' active' : '') + (deshabilitado ? ' disabled' : '');
        li.innerHTML = `<a class="page-link" href="#">${etiqueta}</a>`;
        li.addEventListener('click', function (e) {
            e.preventDefault();
            if (!deshabilitado) buscarCatalogo(numero);
        });
        ul.appendChild(li);
    }

    agregarBoton(pagina - 1, '«', pagina <= 1, false);
    for (let i = desde; i <= hasta; i++) {
        agregarBoton(i, i, false, i === pagina);
    }
    agregarBoton(pagina + 1, '»', pagina >= totalPaginas, false);
}

// item viene de la fila de la tabla (listar(), no trae unidad_medida_id,
// solo el nombre) -- por eso al editar se busca el id de la unidad por
// nombre en el <select> ya cargado en el modal.
function abrirModal(item) {
    registroActual = item;

    const form = document.getElementById('formBienServicio');
    form.reset();
    document.getElementById('campo_categoria_nueva').classList.add('d-none');
    document.getElementById('alertaErrorModal').classList.add('d-none');

    if (item) {
        document.getElementById('tituloModal').textContent = 'Editar bien o servicio';
        document.getElementById('campo_id').value = item.id;
        document.getElementById('campo_nombre').value = item.nombre;
        document.getElementById('campo_categoria').value = item.categoria ?? '';
        document.getElementById('campo_partida').value = item.partida_presupuestal ?? '';
        document.getElementById('campo_precio').value = item.precio ?? '';

        const selectUnidad = document.getElementById('campo_unidad');
        const opcion = Array.from(selectUnidad.options).find(o => o.text === item.unidad_medida);
        selectUnidad.value = opcion ? opcion.value : '';

        document.getElementById('btnDesactivar').classList.remove('d-none');
        document.getElementById('btnEliminar').classList.remove('d-none');
        document.getElementById('textoDesactivar').textContent = item.activo == 1 ? 'Desactivar' : 'Activar';
    } else {
        document.getElementById('tituloModal').textContent = 'Nuevo bien o servicio';
        document.getElementById('campo_id').value = '';
        document.getElementById('campo_unidad').value = '';
        document.getElementById('btnDesactivar').classList.add('d-none');
        document.getElementById('btnEliminar').classList.add('d-none');
    }

    $('#modalBienServicio').modal('show');
}

function datosFormulario() {
    let categoria = document.getElementById('campo_categoria').value;
    if (categoria === '__nueva__') {
        categoria = document.getElementById('campo_categoria_nueva').value.trim();
    }

    return {
        nombre:                document.getElementById('campo_nombre').value,
        categoria:             categoria,
        partida_presupuestal:  document.getElementById('campo_partida').value || null,
        unidad_medida_id:      document.getElementById('campo_unidad').value || null,
        precio:                document.getElementById('campo_precio').value || null,
    };
}

function guardar() {
    const id  = document.getElementById('campo_id').value;
    const url = id
        ? `<?= base_url('bienes-servicios/actualizar/') ?>${id}`
        : `<?= base_url('bienes-servicios/crear') ?>`;

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(datosFormulario())
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200 || data.status === 201) {
            $('#modalBienServicio').modal('hide');
            buscarCatalogo(paginaActual);
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

function cambiarEstado() {
    if (!registroActual) return;

    fetch(`<?= base_url('bienes-servicios/estado/') ?>${registroActual.id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalBienServicio').modal('hide');
            buscarCatalogo(paginaActual);
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error cambiando estado:", error));
}

function eliminar() {
    if (!registroActual) return;
    if (!confirm('¿Eliminar este bien/servicio? Esta acción no se puede deshacer.')) return;

    fetch(`<?= base_url('bienes-servicios/eliminar/') ?>${registroActual.id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            $('#modalBienServicio').modal('hide');
            buscarCatalogo(paginaActual);
            mostrarToast(data.message, 'success');
        } else {
            mostrarToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => console.error("Error eliminando:", error));
}
</script>
<?= $this->endSection() ?>