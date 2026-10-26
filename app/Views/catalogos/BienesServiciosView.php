<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= esc($titulo) ?></h1>
</div>

<p class="text-muted">
    Solo consulta: para editar un bien/servicio, por ahora se sigue haciendo
    desde phpMyAdmin (tabla <code>bienes_servicios</code>).
</p>

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
                        <th style="width: 12%;">Categoría</th>
                        <th style="width: 8%;">Partida</th>
                        <th style="width: 10%;">Unidad</th>
                        <th style="width: 10%;">Precio</th>
                        <th style="width: 7%;">Estado</th>
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

<script>
let paginaActual = 1;

document.addEventListener("DOMContentLoaded", function () {
    buscarCatalogo(1);
    document.getElementById('btnBuscar').addEventListener('click', () => buscarCatalogo(1));
    document.getElementById('filtroTexto').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarCatalogo(1); }
    });
    document.getElementById('filtroCategoria').addEventListener('change', () => buscarCatalogo(1));
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
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin resultados.</td></tr>';
        }

        data.data.forEach(item => {
            const estado = item.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            tbody.innerHTML += `
                <tr>
                    <td>${item.nombre}</td>
                    <td>${item.categoria ?? '—'}</td>
                    <td>${item.partida_presupuestal ?? '—'}</td>
                    <td>${item.unidad_medida ?? '—'}</td>
                    <td class="text-right">${formatoMoneda(item.precio)}</td>
                    <td>${estado}</td>
                </tr>
            `;
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
</script>
<?= $this->endSection() ?>
