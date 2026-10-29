<?php

namespace App\Libraries;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

use RuntimeException;
class GeneradorPdfContrato
{

    private function datosFijos(array $contrato): array
    {
        $subtotal = (float) $contrato['subtotal'];
        $total    = (float) $contrato['total'];

        $montoMinimo   = $total * 0.60;
        $diezImporte   = $subtotal * 0.10;
        $cincoImporte  = $subtotal * 0.05;

        return [
            'folio'               => $contrato['folio'],
            'fecha_elaboracion'   => isset($contrato['created_at']) ? date('d/m/Y', strtotime($contrato['created_at'])) : '',
            'descripcion'         => $contrato['descripcion_corta'],
            'nombre_bien'         => $contrato['nombre_bien'] ?? '',
            'fecha_entrega'       => $contrato['fecha_entrega'] ?? '',
            'lugar_entrega'       => $contrato['lugar_entrega'] ?? '',
            'plazo_pago'          => $contrato['plazo_pago'] ?? '',
            'condiciones_entrega' => $contrato['condiciones_entrega'] ?? '',
            'subtotal'            => number_format($subtotal, 2),
            'iva'                 => number_format((float) $contrato['iva'], 2),
            'total'               => number_format($total, 2),
            'total_texto'         => $this->numeroALetras($total),

            'monto_min'           => number_format($montoMinimo, 2),
            'monto_min_texto'     => $this->numeroALetras($montoMinimo),
            'diez_importe'        => number_format($diezImporte, 2),
            'diez_importe_texto'  => $this->numeroALetras($diezImporte),
            'cinco_importe'       => number_format($cincoImporte, 2),
            'cinco_importe_texto' => $this->numeroALetras($cincoImporte),

            'nombre_proveedor'    => $contrato['razon_social'],

            'area_usuaria'        => $contrato['nombre_area'],
            'area_tecnica'        => $contrato['nombre_area'],
            'area_requirente'     => $contrato['nombre_area'],
        ];
    }


    private function datosProveedor(array $proveedor): array
    {
        return [
            'rfc_proveedor'        => $proveedor['rfc'] ?? '',
            'nombre_representante' => $proveedor['representante_legal'] ?? '',
            'folio_ine'            => $proveedor['folio_ine'] ?? '',
            'nci'                  => $proveedor['nci'] ?? '',
            'telefono_proveedor'   => $proveedor['telefono'] ?? '',
            'correo_proveedor'     => $proveedor['correo'] ?? '',
            'domicilio_fiscal'     => $proveedor['domicilio_fiscal'] ?? '',
            'actividad_economica'  => $proveedor['actividad_economica'] ?? '',
            'marca_fisica'         => ($proveedor['tipo_persona'] ?? '') === 'fisica' ? 'X' : '',
            'marca_moral'          => ($proveedor['tipo_persona'] ?? '') === 'moral' ? 'X' : '',
        ];
    }

    private function datosArea(array $area): array
    {
        return [
            'titular_area'  => $area['titular_area'] ?? '',
            'cargo_titular' => $area['cargo_titular'] ?? '',
            'titular_area' => $area['titular_area'] ?? '',
        ];
    }

    public function generar(
        array $contrato,
        array $proveedor,
        array $area,
        array $valoresDinamicos,
        array $partidas,
        string $rutaPlantilla
    ): string {

        if (! defined('VENDORPATH'))
        {
            throw new RuntimeException(
                'Faltan las librerías para generar el PDF. Corre "composer install"en la carpeta '
                . 'del proyecto y vuelve a intentar.'
            );
        }

        if (! is_file($rutaPlantilla))
        {
            throw new RuntimeException(
                'Este tipo de contrato no tiene una plantilla de Word válida. '
                . 'Sube una en Tipos de contrato → Gestionar plantilla.'
            );
        }

        $extension = strtolower(pathinfo($rutaPlantilla, PATHINFO_EXTENSION));

        // TemplateProcessor SOLO entiende el formato .docx
        if (! in_array($extension, ['docx', 'xlsx'], true))
        {
            throw new RuntimeException(
                'La plantilla de este tipo de contrato no es un archivo .docx o .xlsx'
                . 'Vuelve a subirla guardada en ese formato '
            );
        }


        $valores = array_merge(
            $this->datosFijos($contrato),
            $this->datosProveedor($proveedor),
            $this->datosArea($area)
        );

        //para los campos dinamicos
        foreach ($valoresDinamicos as $valor)
        {
            $valores[$valor['nombre_campo']] = $valor['valor'] ?? '';
        }

        //para las partidas

        $filasPartidas = [];
        foreach ($partidas as $partida)
        {
            $filasPartidas[] = [
                'partida'              => (string) $partida['num_partida'],
                'descripcion_detallada' => $partida['descripcion'],
                'u_medida'             => $partida['unidad_medida'],
                'cantidad'             => $this->formatoCantidad((float) $partida['cantidad']),
                'marca'                => $partida['marca'] ?? '',
                'p_unit'               => number_format((float) $partida['precio_unitario'], 2),
                'importe'              => number_format((float) $partida['importe'], 2),
            ];
        }


        // temporal: se limpia solo despues de una hora.
        $carpetaTemporal = WRITEPATH . 'uploads/documentos_temporales';
        if (! is_dir($carpetaTemporal)) {
            mkdir($carpetaTemporal, 0775, true);
        }

        foreach (glob($carpetaTemporal . '/*.{docx,xlsx}', GLOB_BRACE) ?: [] as $archivoViejo) {
            if (filemtime($archivoViejo) < time() - 3600) {
                unlink($archivoViejo);
            }
        }

        $nombreBase = uniqid('contrato_', true);

        return $extension === 'docx'
            ? $this->llenarPlantillaWord($rutaPlantilla, $valores, $filasPartidas, $carpetaTemporal, $nombreBase)
            : $this->llenarPlantillaExcel($rutaPlantilla, $valores, $filasPartidas, $carpetaTemporal, $nombreBase);
    }

    /**
     * Llena un machote de Word y regresa la ruta del .docx ya lleno.
     */
    private function llenarPlantillaWord(
        string $rutaPlantilla,
        array $valores,
        array $filasPartidas,
        string $carpetaTemporal,
        string $nombreBase
    ): string {
        // arregla "${marcador}" partido por un cuadro amarillo antes de que PhpWord lo corrompa.
        $rutaPlantillaSaneada = $this->saneaMarcadoresPartidosPorControles($rutaPlantilla);

        try {
            $tp = new TemplateProcessor($rutaPlantillaSaneada);

            // si el marcador no esta no ocurre error
            foreach ($valores as $nombrePlaceholder => $valorReal) {
                $tp->setValue($nombrePlaceholder, (string) $valorReal);
            }
            try {
                $tp->cloneRowAndSetValues('partida', $filasPartidas);
            } catch (\Exception $e) {
                throw new RuntimeException(
                    'La plantilla de este tipo de contrato no tiene preparada la '
                    . 'tabla de Partidas (debe tener una fila con el texto '
                    . '"${partida}" dentro de una tabla). Revisa el Word y vuelve '
                    . 'a intentar.'
                );
            }

            $rutaDocxLleno = $carpetaTemporal . '/' . $nombreBase . '.docx';
            $tp->saveAs($rutaDocxLleno);
        } finally {
            // la copia saneada es solo de trabajo, nunca se guarda
            if (is_file($rutaPlantillaSaneada)) {
                unlink($rutaPlantillaSaneada);
            }
        }

        // quita w14:paraId/w14:textId duplicados que deja cloneRowAndSetValues.
        $this->quitarIdsDuplicadosDeParrafos($rutaDocxLleno);

        return $rutaDocxLleno;
    }

    // Copia el machote y mete el "$" suelto dentro del cuadro amarillo
    // cuando quedó partido a la mitad por la etiqueta <w:sdt>.
    private function saneaMarcadoresPartidosPorControles(string $rutaPlantillaOriginal): string
    {
        $rutaTemp = tempnam(sys_get_temp_dir(), 'machote_') . '.docx';
        copy($rutaPlantillaOriginal, $rutaTemp);

        $zip = new \ZipArchive();
        if ($zip->open($rutaTemp) !== true) {
            return $rutaTemp;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml !== false) {
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = true;
            if (@$dom->loadXML($xml)) {
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                foreach (iterator_to_array($xpath->query('//w:sdt')) as $sdt) {
                    // excluye campos nativos de Word (num. de pagina, etc).
                    if ($xpath->query('.//w:docPartGallery', $sdt)->length > 0) {
                        continue;
                    }

                    $sdtContent = $xpath->query('./w:sdtContent', $sdt)->item(0);
                    if (! $sdtContent) {
                        continue;
                    }

                    // Primer texto visible DENTRO del cuadro: ¿empieza con "{"?
                    $primerTexto = $xpath->query('.//w:t', $sdtContent)->item(0);
                    if (! $primerTexto || ! str_starts_with($primerTexto->textContent, '{')) {
                        continue;
                    }

                    // Último texto visible de la run/párrafo que viene
                    // ANTES del cuadro (fuera de él): ¿termina en "$"?
                    $runAnterior = $this->runDeTextoAnterior($sdt);
                    if (! $runAnterior) {
                        continue;
                    }
                    $textoAnterior = $xpath->query('.//w:t', $runAnterior)->item(0);
                    if (! $textoAnterior || ! str_ends_with($textoAnterior->textContent, '$')) {
                        continue;
                    }

                    // Mueve el "$" de afuera hacia adentro del cuadro, al
                    // inicio de su primer texto, y quita esa run si se
                    // queda vacía (si solo tenía el "$" suelto).
                    $textoAnterior->textContent = substr($textoAnterior->textContent, 0, -1);
                    $primerTexto->textContent    = '$' . $primerTexto->textContent;

                    if ($textoAnterior->textContent === '') {
                        $runAnterior->parentNode->removeChild($runAnterior);
                    }
                }

                $zip->addFromString('word/document.xml', $dom->saveXML($dom->documentElement));
            }
        }

        $zip->close();

        return $rutaTemp;
    }

    /**
     * Busca, hacia atrás a partir de $nodo, el nodo <w:r> (run) de texto
     * más cercano que no sea el propio $nodo — saltándose nodos vacíos o
     * sin texto visible  que pudieran estar entre medio. Regresa null si no encuentra ninguno antes de llegar al
     * principio del párrafo.
     */
    private function runDeTextoAnterior(\DOMNode $nodo): ?\DOMElement
    {
        $anterior = $nodo->previousSibling;

        while ($anterior !== null) {
            if ($anterior instanceof \DOMElement
                && $anterior->localName === 'r'
                && trim($anterior->textContent) !== ''
            ) {
                return $anterior;
            }

            $anterior = $anterior->previousSibling;
        }

        return null;
    }

    /**
     * Quita los atributos w14:paraId/w14:textId del documento ya guardado
     *se hace directamente sobre el .docx ya guardado porque TemplateProcessor no expone una
     * forma de tocar su XML interno antes de saveAs().
     */
    private function quitarIdsDuplicadosDeParrafos(string $rutaDocx): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($rutaDocx) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml !== false) {
            $xmlLimpio = preg_replace('/ w14:(paraId|textId)="[0-9A-Fa-f]+"/', '', $xml);
            $zip->addFromString('word/document.xml', $xmlLimpio);
        }

        $zip->close();
    }

    /**
     * Llena un machote de Excel con PhpSpreadsheet y regresa la ruta ya lleno.
     */
    private function llenarPlantillaExcel(
        string $rutaPlantilla,
        array $valores,
        array $filasPartidas,
        string $carpetaTemporal,
        string $nombreBase
    ): string {
        $libro = SpreadsheetIOFactory::load($rutaPlantilla);

        $seEncontroTablaPartidas = false;

        foreach ($libro->getAllSheets() as $hoja) {
            // 1) Buscar en esta hoja la fila ancla, Si no la tiene, se salta de hoja
            $filaAncla = $this->buscarFilaAncla($hoja, '${partida}');

            if ($filaAncla !== null) {
                $seEncontroTablaPartidas = true;
                $this->llenarTablaPartidasExcel($hoja, $filaAncla, $filasPartidas);
            }

            //El resto de los placeholders
            $this->reemplazarEnHoja($hoja, $valores);
        }

        if (! $seEncontroTablaPartidas) {
            throw new RuntimeException(
                'La plantilla de este tipo de contrato no tiene preparada la '
                . 'tabla de Partidas (debe tener una celda con el texto '
                . 'exacto "${partida}" en alguna hoja). Revisa el Excel y '
                . 'vuelve a intentar.'
            );
        }

        $rutaXlsxLleno = $carpetaTemporal . '/' . $nombreBase . '.xlsx';
        SpreadsheetIOFactory::createWriter($libro, 'Xlsx')->save($rutaXlsxLleno);

        return $rutaXlsxLleno;
    }

    /**
     * busca la primera celda cuyo valor sea  el texto buscado y regresa el
     * número de esa fila, o null si no la encuentra.
     */
    private function buscarFilaAncla(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, string $textoBuscado): ?int
    {
        foreach ($hoja->getRowIterator() as $fila) {
            foreach ($fila->getCellIterator() as $celda) {
                if ($celda->getValue() === $textoBuscado) {
                    return $fila->getRowIndex();
                }
            }
        }

        return null;
    }

    /**
     * Duplica la fila ancla de la tabla de Partidas una vez por cada
     * partida copiando también el formato de las celdas, y llena cada copia con los datos de su propia
     * partida. Si no hay partidas, simplemente deja la fila ancla vacía
     */
    private function llenarTablaPartidasExcel(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja,
        int $filaAncla,
        array $filasPartidas
    ): void {
        $columnaMaximaIndice = Coordinate::columnIndexFromString($hoja->getHighestColumn());

        $moldePorColumna = [];
        for ($columnaIndice = 1; $columnaIndice <= $columnaMaximaIndice; $columnaIndice++) {
            $letraColumna = $this->letraColumna($columnaIndice);
            $valorCelda   = $hoja->getCell($letraColumna . $filaAncla)->getValue();

            if (is_string($valorCelda) && $valorCelda !== '') {
                $moldePorColumna[$letraColumna] = $valorCelda;
            } elseif ($valorCelda instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                // celdas con texto de formato mixto 
                $moldePorColumna[$letraColumna] = $valorCelda->getPlainText();
            }
        }

        $cantidadPartidas = count($filasPartidas);

        if ($cantidadPartidas === 0) {
            foreach ($moldePorColumna as $letraColumna => $textoOriginal) {
                if (str_contains($textoOriginal, '${')) {
                    $hoja->setCellValue($letraColumna . $filaAncla, '');
                }
            }

            return;
        }

        //aumentar las filas para las partidas
        $filasExtra = $cantidadPartidas - 1;

        if ($filasExtra > 0) {
            $hoja->insertNewRowBefore($filaAncla + 1, $filasExtra);

            // se copiar el formato de las celdas de la fila principal
            for ($i = 1; $i <= $filasExtra; $i++) {
                $filaDestino = $filaAncla + $i;

                foreach (array_keys($moldePorColumna) as $letraColumna) {
                    $hoja->duplicateStyle(
                        $hoja->getStyle($letraColumna . $filaAncla),
                        $letraColumna . $filaDestino
                    );
                }
            }
        }

        // se llena todo
        foreach ($filasPartidas as $indice => $datosPartida) {
            $filaActual = $filaAncla + $indice;

            foreach ($moldePorColumna as $letraColumna => $textoOriginal) {
                $hoja->setCellValue(
                    $letraColumna . $filaActual,
                    $this->valorCeldaResuelto($textoOriginal, $datosPartida)
                );
            }
        }
    }

    // Si la celda es solo un placeholder numérico, manda el número real
    private function valorCeldaResuelto(string $textoOriginal, array $valores)
    {
        if (preg_match('/^\$\{([a-zA-Z0-9_]+)\}$/', trim($textoOriginal), $coincidencia)
            && array_key_exists($coincidencia[1], $valores)
        ) {
            $valorCrudo = $valores[$coincidencia[1]];
            $sinComas   = is_string($valorCrudo) ? str_replace(',', '', $valorCrudo) : $valorCrudo;

            if (is_numeric($sinComas)) {
                return (float) $sinComas;
            }
        }

        return $this->reemplazarPlaceholders($textoOriginal, $valores);
    }

    /**
     * Convierte un número de columna (1, 2, 3...) a su letra de Excel (A, B, C... AA, AB...).
     */
    private function letraColumna(int $indice): string
    {
        return Coordinate::stringFromColumnIndex($indice);
    }

    /**
     * Reemplaza los placeholders sueltos.
     */
    private function reemplazarEnHoja(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, array $valores): void
    {
        foreach ($hoja->getRowIterator() as $fila) {
            foreach ($fila->getCellIterator() as $celda) {
                $valorCelda = $celda->getValue();

                if (is_string($valorCelda) && str_contains($valorCelda, '${')) {
                    $celda->setValue($this->valorCeldaResuelto($valorCelda, $valores));
                    continue;
                }

                // texto mixto viene como RichText.
                if ($valorCelda instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    foreach ($valorCelda->getRichTextElements() as $elemento) {
                        $texto = $elemento->getText();
                        if (str_contains($texto, '${')) {
                            $elemento->setText($this->reemplazarPlaceholders($texto, $valores));
                        }
                    }
                    $celda->setValue($valorCelda);
                }
            }
        }
    }


    private function reemplazarPlaceholders(string $texto, array $valores): string
    {
        foreach ($valores as $nombrePlaceholder => $valorReal) {
            $texto = str_replace('${' . $nombrePlaceholder . '}', (string) $valorReal, $texto);
        }

        return $texto;
    }

    // Quita los decimales solo cuando son puro 
    private function formatoCantidad(float $cantidad): string
    {
        if ($cantidad == floor($cantidad)) {
            return number_format($cantidad, 0);
        }

        return number_format($cantidad, 2);
    }

    /** Convierte un monto a su representacion en letras */
    private function numeroALetras(float $monto): string
    {
        $entero   = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);

        $texto = $entero === 0 ? 'CERO' : $this->enteroALetras($entero);

        return $texto . ' PESOS ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 M.N.';
    }

    private function enteroALetras(int $numero): string
    {
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $especiales = [
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
            15 => 'QUINCE', 16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO',
            19 => 'DIECINUEVE', 20 => 'VEINTE',
        ];
        $decenas = [
            3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA',
            6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA',
        ];
        $centenas = [
            1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS',
            5 => 'QUINIENTOS', 6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS',
            9 => 'NOVECIENTOS',
        ];

        if ($numero === 0) {
            return 'CERO';
        }

        if ($numero >= 1000000) {
            $millones = intdiv($numero, 1000000);
            $resto    = $numero % 1000000;
            $texto    = $millones === 1 ? 'UN MILLON' : $this->enteroALetras($millones) . ' MILLONES';

            return $resto > 0 ? $texto . ' ' . $this->enteroALetras($resto) : $texto;
        }

        if ($numero >= 1000) {
            $miles = intdiv($numero, 1000);
            $resto = $numero % 1000;
            $texto = $miles === 1 ? 'MIL' : $this->enteroALetras($miles) . ' MIL';

            return $resto > 0 ? $texto . ' ' . $this->enteroALetras($resto) : $texto;
        }

        if ($numero >= 100) {
            if ($numero === 100) {
                return 'CIEN';
            }

            $centena = intdiv($numero, 100);
            $resto   = $numero % 100;
            $texto   = $centenas[$centena];

            return $resto > 0 ? $texto . ' ' . $this->enteroALetras($resto) : $texto;
        }

        if ($numero > 20) {
            $decena = intdiv($numero, 10);
            $resto  = $numero % 10;

            if ($decena === 2) {
                return $resto > 0 ? 'VEINTI' . $unidades[$resto] : 'VEINTE';
            }

            $texto = $decenas[$decena];

            return $resto > 0 ? $texto . ' Y ' . $unidades[$resto] : $texto;
        }

        if ($numero >= 10) {
            return $especiales[$numero];
        }

        return $unidades[$numero];
    }
}