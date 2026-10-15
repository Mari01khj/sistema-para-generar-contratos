<?php
/** Dibuja el formulario completo de un contrato.*/

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
                    <label>Área solicitante <span class="text-danger">*</span></label>
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

            <div class="form-group">
                <label>Descripción <span class="text-danger">*</span></label>
                <input type="text" name="descripcion_corta" class="form-control" maxlength="255"
                    value="<?= esc(old('descripcion_corta') ?? ($valoresFijos['descripcion_corta'] ?? '')) ?>"
                    placeholder="Ej. Adquisición de uniformes del Ayuntamiento" required>
                <small class="form-text text-muted">Resumen del contrato. Los detalles de cada bien van en la tabla de Partidas.</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Fecha de entrega</label>
                    <input type="date" name="fecha_entrega" class="form-control"
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

    <!--  El importe de cada renglón y los totales se recalculan aquí solo para que el usuario los vea-->
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
                                    <input type="text" name="bienes[<?= $i ?>][descripcion]" class="form-control"
                                        value="<?= esc($fila['descripcion'] ?? '') ?>" placeholder="Ej. Camisa tipo polo, talla M, bordado logo">
                                </td>
                                <td>
                                    <input type="text" name="bienes[<?= $i ?>][unidad_medida]" class="form-control"
                                        value="<?= esc($fila['unidad_medida'] ?? '') ?>" placeholder="Pieza">
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
                    <td><input type="text" name="bienes[__i__][descripcion]" class="form-control" placeholder="Ej. Camisa tipo polo, talla M, bordado logo"></td>
                    <td><input type="text" name="bienes[__i__][unidad_medida]" class="form-control" placeholder="Pieza"></td>
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
            <p class="text-muted small mb-0">Estos montos el sistema los vuelve a calcular al guardar.</p>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?= esc($textoBoton) ?>
    </button>
</form>

<script>
(function () {
    var cuerpo = document.getElementById('cuerpoPartidas');
    if (!cuerpo) return; // no selecciono el tipo de contrato

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
        cuerpo.appendChild(contenedorTemporal.firstElementChild);
        contador++;
        recalcular();
    }

    cuerpo.addEventListener('input', function (e) {
        if (e.target.classList.contains('campo-cantidad') || e.target.classList.contains('campo-precio')) 
        {
            recalcular();
        }
    });

    cuerpo.addEventListener('click', function (e) {
        var boton = e.target.closest('.btnQuitarFila');
        if (!boton) return;

        var filas = cuerpo.querySelectorAll('.fila-partida');
        if (filas.length <= 1) 
        {
            boton.closest('tr').querySelectorAll('input').forEach(function (input) { input.value = ''; });
        } 
        else 
        {
            boton.closest('tr').remove();
        }
        recalcular();
    });

    document.getElementById('btnAgregarFila').addEventListener('click', agregarFila);

    recalcular();
})();
</script>
