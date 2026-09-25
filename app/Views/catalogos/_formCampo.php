<?php
// Parcial reutilizado por el modal de "nuevo campo" y el de "editar campo",
// para no mantener el mismo formulario escrito dos veces.
// $prefijoId distingue los IDs de cada copia (nuevo_etiqueta vs editar_etiqueta)
// porque un id de HTML no puede repetirse dos veces en la misma página.
?>
<div class="form-group">
    <label>Etiqueta (lo que ve quien llena el contrato)</label>
    <input type="text" name="etiqueta" id="<?= $prefijoId ?>_etiqueta" class="form-control" placeholder="Ej. Fecha de entrega" required>
</div>

<div class="form-group">
    <label>Nombre técnico (marcador en el machote)</label>
    <input type="text" name="nombre_campo" id="<?= $prefijoId ?>_nombre_campo" class="form-control" pattern="[a-z][a-z0-9_]*" placeholder="Ej. fecha_entrega" required>
    <small class="form-text text-muted">Se sugiere solo a partir de la etiqueta, pero puedes editarlo. Sin espacios ni acentos.</small>
</div>

<div class="form-group">
    <label>Tipo de dato</label>
    <select name="tipo_dato" id="<?= $prefijoId ?>_tipo_dato" class="form-control" required>
        <option value="">Selecciona...</option>
        <?php foreach ($tiposDato as $valor => $etiquetaTipo): ?>
            <option value="<?= esc($valor, 'attr') ?>"><?= esc($etiquetaTipo) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group" id="<?= $prefijoId ?>_grupo_origen_lista" style="display: none;">
    <label>¿De qué catálogo salen las opciones?</label>
    <select name="origen_lista" id="<?= $prefijoId ?>_origen_lista" class="form-control">
        <option value="">Selecciona...</option>
        <?php foreach ($catalogosLista as $valor => $etiquetaCatalogo): ?>
            <option value="<?= esc($valor, 'attr') ?>"><?= esc($etiquetaCatalogo) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>Orden de aparición</label>
    <input type="number" name="orden" id="<?= $prefijoId ?>_orden" class="form-control" min="0" value="<?= (int) $orden ?>">
</div>

<div class="form-group form-check">
    <input type="checkbox" name="obligatorio" id="<?= $prefijoId ?>_obligatorio" class="form-check-input" value="1">
    <label class="form-check-label" for="<?= $prefijoId ?>_obligatorio">Obligatorio</label>
</div>
