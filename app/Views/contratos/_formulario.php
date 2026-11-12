<?php
/**
 * Partial compartido por contratos/crear.php */
$valoresFijos  ??= [];
$valoresCampos ??= [];
$textoBoton    ??= 'Guardar contrato';
?>

<form method="POST" action="<?= $accionUrl ?>" id="formContrato">
    <input type="hidden" name="tipo_contrato_id" value="<?= $tipoContrato['id'] ?>">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos generales</h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Proveedor <span class="text-danger">*</span></label>
                    <?php $proveedorPrevio = old('proveedor_id') ?? ($valoresFijos['proveedor_id'] ?? null); ?>
                    <select name="proveedor_id" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $proveedorPrevio == $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['razon_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col-md-6">
                    <label>Área usuaria / requirente <span class="text-danger">*</span></label>
                    <?php $areaPrevia = old('area_solicitante_id') ?? ($valoresFijos['area_solicitante_id'] ?? null); ?>
                    <select name="area_solicitante_id" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $areaPrevia == $a['id'] ? 'selected' : '' ?>>
                                <?= esc($a['nombre_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Área técnica</label>
                    <?php $areaTecnicaPrevia = old('area_tecnica_id') ?? ($valoresFijos['area_tecnica_id'] ?? null); ?>
                    <select name="area_tecnica_id" class="form-control">
                        <option value="">Igual al área usuaria</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $areaTecnicaPrevia == $a['id'] ? 'selected' : '' ?>>
                                <?= esc($a['nombre_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Solo si el área técnica es distinta de la usuaria.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción <span class="text-danger">*</span></label>
                <input type="text" name="descripcion_corta" class="form-control" maxlength="255"
                    value="<?= esc(old('descripcion_corta') ?? ($valoresFijos['descripcion_corta'] ?? '')) ?>"
                    placeholder="Ej. Adquisición de uniformes del Ayuntamiento" required>
                <small class="form-text text-muted">Resumen del objeto del contrato</small>
            </div>

           
            <div class="form-group">
                <label>Nombre general del bien </label>
                <textarea name="nombre_bien" class="form-control" rows="3"
                ><?= esc(old('nombre_bien') ?? ($valoresFijos['nombre_bien'] ?? '')) ?></textarea>
            </div>
 
            <?php if (isset($fuentes)): ?>
                
                <div class="form-group">
                    <label>Fuente de financiamiento <span class="text-danger">*</span></label>
                    <?php $fuentePrevia = old('fuente_financiamiento_id'); ?>
                    <select name="fuente_financiamiento_id" class="form-control" style="max-width: 400px;" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($fuentes as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= $fuentePrevia == $f['id'] ? 'selected' : '' ?>>
                                <?= esc($f['nombre']) ?> (<?= esc($f['clave']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Fecha de entrega</label>
                    <input type="text" name="fecha_entrega" class="form-control"
                        placeholder="Ej. 15/12/2026 o &quot;Hasta 15 días hábiles después de la firma&quot;"
                        value="<?= esc(old('fecha_entrega') ?? ($valoresFijos['fecha_entrega'] ?? '')) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>Lugar de entrega</label>
                    <input type="text" name="lugar_entrega" class="form-control"
                        value="<?= esc(old('lugar_entrega') ?? ($valoresFijos['lugar_entrega'] ?? '')) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>Plazo de pago</label>
                    <input type="text" name="plazo_pago" class="form-control"
                        value="<?= esc(old('plazo_pago') ?? ($valoresFijos['plazo_pago'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Condiciones de entrega</label>
                <textarea name="condiciones_entrega" class="form-control" rows="2"><?= esc(old('condiciones_entrega') ?? ($valoresFijos['condiciones_entrega'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <?php if (! empty($camposDinamicos)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos específicos de "<?= esc($tipoContrato['nombre']) ?>"</h6>
            </div>
            <div class="card-body">
                <?php foreach ($camposDinamicos as $campo): ?>
                    <div class="form-group">
                        <label>
                            <?= esc($campo['etiqueta']) ?>
                            <?php if ($campo['obligatorio']): ?><span class="text-danger">*</span><?php endif; ?>
                        </label>

                        <?php
                            $valorPrevio = old('campos.' . $campo['id']) ?? ($valoresCampos[$campo['id']] ?? '');
                        ?>

                        <?php if ($campo['tipo_dato'] === 'texto_largo'): ?>
                            <textarea name="campos[<?= $campo['id'] ?>]" class="form-control" rows="3"
                                <?= $campo['obligatorio'] ? 'required' : '' ?>><?= esc($valorPrevio) ?></textarea>

                        <?php elseif ($campo['tipo_dato'] === 'numero'): ?>
                            <input type="number" step="any" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                value="<?= esc($valorPrevio) ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>

                        <?php elseif ($campo['tipo_dato'] === 'fecha'): ?>
                            <input type="date" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                value="<?= esc($valorPrevio) ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>

                        <?php elseif ($campo['tipo_dato'] === 'lista'): ?>
                            <select name="campos[<?= $campo['id'] ?>]" class="form-control" <?= $campo['obligatorio'] ? 'required' : '' ?>>
                                <option value="">Selecciona...</option>
                                <?php foreach ($opcionesListas[$campo['origen_lista']] ?? [] as $opcion): ?>
                                    <?php $etiquetaOpcion = $opcion['razon_social'] ?? $opcion['nombre_area'] ?? ('#' . $opcion['id']); ?>
                                    <option value="<?= $opcion['id'] ?>" <?= $valorPrevio == $opcion['id'] ? 'selected' : '' ?>>
                                        <?= esc($etiquetaOpcion) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php else: ?>
                            <input type="text" name="campos[<?= $campo['id'] ?>]" class="form-control"
                                value="<?= esc($valorPrevio) ?>" <?= $campo['obligatorio'] ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- aqui se hace el calculo que el usuario ve -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Partidas</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarFila">
                <i class="fas fa-plus"></i> Agregar partida
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tablaPartidas">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No.</th>
                            <th>Descripción</th>
                            <th style="width: 10%;">Unidad</th>
                            <th style="width: 12%;">Marca</th>
                            <th style="width: 9%;">Cantidad</th>
                            <th style="width: 12%;">Precio unitario</th>
                            <th style="width: 12%;">Importe</th>
                            <th style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoPartidas">
                        <?php foreach ($filasBienesPrevias as $i => $fila): ?>
                            <tr class="fila-partida">
                                <td class="text-center numero-fila"><?= $i + 1 ?></td>
                                <td>
                                    <input type="text" name="bienes[<?= $i ?>][descripcion]" class="form-control campo-descripcion"
                                        value="<?= esc($fila['descripcion'] ?? '') ?>" placeholder="Ej. Camisa tipo polo, talla M, bordado logo"
                                        autocomplete="off">
                                </td>
                                <td>
                                    <input type="text" name="bienes[<?= $i ?>][unidad_medida]" class="form-control campo-unidad"
                                        value="<?= esc($fila['unidad_medida'] ?? '') ?>" placeholder="Pieza" autocomplete="off">
                                </td>
                                <td>
                                    <input type="text" name="bienes[<?= $i ?>][marca]" class="form-control"
                                        value="<?= esc($fila['marca'] ?? '') ?>">
                                </td>
                                <td>
                                    <input type="number" step="any" min="0" name="bienes[<?= $i ?>][cantidad]"
                                        class="form-control campo-cantidad" value="<?= esc($fila['cantidad'] ?? '') ?>">
                                </td>
                                <td>
                                    <input type="number" step="any" min="0" name="bienes[<?= $i ?>][precio_unitario]"
                                        class="form-control campo-precio" value="<?= esc($fila['precio_unitario'] ?? '') ?>">
                                </td>
                                <td class="text-right importe-fila">$0.00</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btnQuitarFila"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <template id="plantillaFilaPartida">
                <tr class="fila-partida">
                    <td class="text-center numero-fila">0</td>
                    <td><input type="text" name="bienes[__i__][descripcion]" class="form-control campo-descripcion" placeholder="Ej. Camisa tipo polo, talla M, bordado logo" autocomplete="off"></td>
                    <td><input type="text" name="bienes[__i__][unidad_medida]" class="form-control campo-unidad" placeholder="Pieza" autocomplete="off"></td>
                    <td><input type="text" name="bienes[__i__][marca]" class="form-control"></td>
                    <td><input type="number" step="any" min="0" name="bienes[__i__][cantidad]" class="form-control campo-cantidad"></td>
                    <td><input type="number" step="any" min="0" name="bienes[__i__][precio_unitario]" class="form-control campo-precio"></td>
                    <td class="text-right importe-fila">$0.00</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnQuitarFila"><i class="fas fa-times"></i></button></td>
                </tr>
            </template>


            <table class="table table-sm mt-3" style="max-width: 320px; margin-left: auto;">
                <tr>
                    <th>Subtotal</th>
                    <td class="text-right" id="txtSubtotal">$0.00</td>
                </tr>
                <tr>
                    <th>IVA (16%)</th>
                    <td class="text-right" id="txtIva">$0.00</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td class="text-right"><strong id="txtTotal">$0.00</strong></td>
                </tr>
            </table>
            <p class="text-muted small mb-0">Estos montos son solo una vista previa; el sistema los vuelve a calcular al guardar.</p>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?= esc($textoBoton) ?>
    </button>
</form>

<script>
(function () {
    var cuerpo = document.getElementById('cuerpoPartidas');
    if (!cuerpo) return; // no hay tipo de contrato elegido todavía

    var plantilla = document.getElementById('plantillaFilaPartida');
    var contador  = cuerpo.querySelectorAll('.fila-partida').length;

    function formatoMoneda(valor) {
        return '$' + (isFinite(valor) ? valor : 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcular() {
        var subtotal = 0;

        cuerpo.querySelectorAll('.fila-partida').forEach(function (fila, idx) {
            fila.querySelector('.numero-fila').textContent = idx + 1;

            var cantidad = parseFloat(fila.querySelector('.campo-cantidad').value) || 0;
            var precio   = parseFloat(fila.querySelector('.campo-precio').value) || 0;
            var importe  = cantidad * precio;

            fila.querySelector('.importe-fila').textContent = formatoMoneda(importe);
            subtotal += importe;
        });

        var iva   = subtotal * 0.16;
        var total = subtotal + iva;

        document.getElementById('txtSubtotal').textContent = formatoMoneda(subtotal);
        document.getElementById('txtIva').textContent = formatoMoneda(iva);
        document.getElementById('txtTotal').textContent = formatoMoneda(total);
    }

    function agregarFila() {
        var html = plantilla.innerHTML.replace(/__i__/g, contador);
        var contenedorTemporal = document.createElement('tbody');
        contenedorTemporal.innerHTML = html.trim();
        var filaNueva = contenedorTemporal.firstElementChild;
        cuerpo.appendChild(filaNueva);
        prepararAutocompletarBien(filaNueva);
        contador++;
        recalcular();
    }

    cuerpo.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-cantidad') || e.target.classList.contains('campo-precio')) {
            recalcular();
        }
    });

    // Lista de sugerencias propia (no <datalist>, que depende del navegador).
    var caja = document.createElement('div');
    caja.style.cssText = 'position:fixed;z-index:2000;display:none;background:#fff;border:1px solid #d1d3e2;'
        + 'border-radius:4px;max-height:240px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.15);font-size:.9rem;';
    document.body.appendChild(caja);

    var campoActivo = null;

    function ocultarSugerencias() { caja.style.display = 'none'; campoActivo = null; }

    // Coloca la lista debajo del campo o encima .
    function posicionarSugerencias() {
        if (!campoActivo) return;
        var r = campoActivo.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) { ocultarSugerencias(); return; }

        var alto = Math.min(caja.scrollHeight, 240);
        var abajo = window.innerHeight - r.bottom;
        caja.style.left = r.left + 'px';
        caja.style.width = Math.max(r.width, 280) + 'px';
        caja.style.top = (abajo < alto && r.top > abajo) ? (r.top - alto) + 'px' : r.bottom + 'px';
    }

    function mostrarSugerencias(campo, items, alElegir) {
        caja.innerHTML = '';
        if (!items.length) { ocultarSugerencias(); return; }

        items.forEach(function (item) {
            var opcion = document.createElement('div');
            opcion.textContent = item.texto;
            opcion.style.cssText = 'padding:6px 10px;cursor:pointer;';
            opcion.addEventListener('mouseenter', function () { opcion.style.background = '#eaecf4'; });
            opcion.addEventListener('mouseleave', function () { opcion.style.background = ''; });
            // mousedown (no click): se dispara antes del blur del campo.
            opcion.addEventListener('mousedown', function (e) {
                e.preventDefault();
                campo.value = item.texto;
                ocultarSugerencias();
                alElegir(item);
            });
            caja.appendChild(opcion);
        });

        campoActivo = campo;
        caja.scrollTop = 0;
        caja.style.display = 'block';
        posicionarSugerencias();
    }

    // El scroll de la propia lista no la cierra; el de la página la reubica.
    window.addEventListener('scroll', function (e) {
        if (e.target === caja || caja.contains(e.target)) return;
        posicionarSugerencias();
    }, true);
    window.addEventListener('resize', posicionarSugerencias);
    // Arrastrar la barra de la lista no debe quitar el foco del campo.
    caja.addEventListener('mousedown', function (e) { e.preventDefault(); });

    // --- Unidades de Medida: se filtran en el navegador al escribir ---
    var nombresUnidades = [];

    fetch('<?= base_url('unidades-medida/activas') ?>', { headers: { 'Accept': 'application/json' } })
        .then(function (resp) { return resp.json(); })
        .then(function (nombres) { nombresUnidades = nombres; })
        .catch(function (error) { console.error('No se pudieron cargar las unidades de medida:', error); });

    function sugerirUnidades(campo) {
        var texto = campo.value.trim().toLowerCase();
        var items = nombresUnidades
            .filter(function (n) { return n.toLowerCase().indexOf(texto) !== -1; })
            .map(function (n) { return { texto: n }; });
        mostrarSugerencias(campo, items, function () {});
    }

    cuerpo.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('campo-unidad')) sugerirUnidades(e.target);
    });
    cuerpo.addEventListener('click', function (e) {
        if (e.target.classList.contains('campo-unidad')) sugerirUnidades(e.target);
    });
    cuerpo.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-unidad')) sugerirUnidades(e.target);
    });
    cuerpo.addEventListener('focusout', function () { setTimeout(ocultarSugerencias, 150); });

    // --- salen los biees y servicion es descripcion ---
    function prepararAutocompletarBien(fila) {
        var campoDescripcion = fila.querySelector('.campo-descripcion');
        if (!campoDescripcion) return;

        var temporizador = null;

        campoDescripcion.addEventListener('input', function () {
            var texto = campoDescripcion.value.trim();

            clearTimeout(temporizador);
            if (texto.length < 3) { ocultarSugerencias(); return; }

            temporizador = setTimeout(function () {
                fetch('<?= base_url('bienes-servicios/buscar') ?>?q=' + encodeURIComponent(texto), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (resp) { return resp.json(); })
                .then(function (resultados) {
                    var items = resultados.map(function (r) {
                        return { texto: r.nombre, unidad: r.unidad_medida, precio: r.precio };
                    });

                    mostrarSugerencias(campoDescripcion, items, function (item) {
                        if (item.unidad) fila.querySelector('.campo-unidad').value = item.unidad;
                        if (item.precio !== null && item.precio !== undefined) {
                            fila.querySelector('.campo-precio').value = item.precio;
                        }
                        recalcular();
                    });
                })
                .catch(function (error) { console.error('Error buscando en el catálogo:', error); });
            }, 300);
        });
    }

    cuerpo.querySelectorAll('.fila-partida').forEach(prepararAutocompletarBien);

    cuerpo.addEventListener('click', function (e) {
        var boton = e.target.closest('.btnQuitarFila');
        if (!boton) return;

        var filas = cuerpo.querySelectorAll('.fila-partida');
        if (filas.length <= 1) {
            // Siempre debe quedar al menos una fila: en vez de quitarla, se vacía.
            boton.closest('tr').querySelectorAll('input').forEach(function (input) { input.value = ''; });
        } else {
            boton.closest('tr').remove();
        }
        recalcular();
    });

    document.getElementById('btnAgregarFila').addEventListener('click', agregarFila);

    recalcular();
})();
</script>