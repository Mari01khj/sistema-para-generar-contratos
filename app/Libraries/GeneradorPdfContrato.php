<?php

namespace App\Libraries;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

use RuntimeException;
/**
 *  1) Se llena el machote (.docx con PhpWord o .xlsx con PhpSpreadsheet)
 *     reemplazando los ${marcadores} por los valores reales.
 *
 *  2) El archivo ya lleno se convierte a PDF usando Word/Excel reales
 *     (COM), que es lo que hay instalado en esta computadora.
 */
class GeneradorPdfContrato
{

    private function datosFijos(array $contrato): array
    {
        return [
            'folio'               => $contrato['folio'],
            // fecha en que se elaboró el contrato (esquina superior del machote)
            'fecha_elaboracion'   => isset($contrato['created_at']) ? date('d/m/Y', strtotime($contrato['created_at'])) : '',
            'descripcion'         => $contrato['descripcion_corta'],
            'fecha_entrega'       => $contrato['fecha_entrega'] ?? '',
            'lugar_entrega'       => $contrato['lugar_entrega'] ?? '',
            'plazo_pago'          => $contrato['plazo_pago'] ?? '',
            'condiciones_entrega' => $contrato['condiciones_entrega'] ?? '',
            'subtotal'            => number_format((float) $contrato['subtotal'], 2),
            'iva'                 => number_format((float) $contrato['iva'], 2),
            'total'               => number_format((float) $contrato['total'], 2),
            'total_texto'         => $this->numeroALetras((float) $contrato['total']),

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
            'telefono_proveedor'   => $proveedor['telefono'] ?? '',
            'correo_proveedor'     => $proveedor['correo'] ?? '',
            'domicilio_fiscal'     => $proveedor['domicilio_fiscal'] ?? '',
            'actividad_economica'  => $proveedor['actividad_economica'] ?? '',
            // la "X" junto a PERSONA FISICA / PERSONA MORAL en el machote
            'marca_fisica'         => ($proveedor['tipo_persona'] ?? '') === 'fisica' ? 'X' : '',
            'marca_moral'          => ($proveedor['tipo_persona'] ?? '') === 'moral' ? 'X' : '',
        ];
    }

    private function datosArea(array $area): array
    {
        return [
            'titular_area'  => $area['titular_area'] ?? '',
            'cargo_titular' => $area['cargo_titular'] ?? '',
        ];
    }

    /**

     */
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
                'partida'     => (string) $partida['num_partida'],
                'nombre_bien' => $partida['descripcion'],
                'u_medida'    => $partida['unidad_medida'],
                'cantidad'    => (string) $partida['cantidad'],
                'marca'       => $partida['marca'] ?? '',
                'p_unit'      => number_format((float) $partida['precio_unitario'], 2),
                'importe'     => number_format((float) $partida['importe'], 2),
            ];
        }


        // el .docx intermediario se borra y los pdfs viejos se limpian cada hora, para no llenar el disco con basura
        $carpetaTemporal = WRITEPATH . 'uploads/pdf_temporal';
        if (! is_dir($carpetaTemporal)) {
            mkdir($carpetaTemporal, 0775, true);
        }

        foreach (glob($carpetaTemporal . '/*.pdf') ?: [] as $pdfViejo) {
            if (filemtime($pdfViejo) < time() - 3600) {
                unlink($pdfViejo);
            }
        }

        $nombreBase  = uniqid('contrato_', true);
        $rutaPdf       = $carpetaTemporal . '/' . $nombreBase . '.pdf';

        $rutaDocumentoLleno = $extension === 'docx'
            ? $this->llenarPlantillaWord($rutaPlantilla, $valores, $filasPartidas, $carpetaTemporal, $nombreBase)
            : $this ->llenarPlantillaExcel($rutaPlantilla, $valores, $filasPartidas, $carpetaTemporal, $nombreBase);

        //el .docx/.xlsx ya lleno se convierte a PDF con Word/Excel (COM)

        set_time_limit(60);

        if (! $this->convertirAPdfConOffice($rutaDocumentoLleno, $rutaPdf, $extension)) {
            // @: si el archivo quedó bloqueado (ej. todavía lo tiene abierto
            // algún proceso de Office), que no pueda borrarse no debe tapar
            // el verdadero motivo de la falla con un error de PHP distinto.
            if (is_file($rutaDocumentoLleno)) {
                @unlink($rutaDocumentoLleno);
            }
            throw new RuntimeException(
                'No se pudo convertir el documento a PDF. Revisa que Microsoft '
                . 'Word/Excel estén instalados en esta computadora y que la '
                . 'extensión "com_dotnet" de PHP esté activa (extension=com_dotnet '
                . 'en el php.ini de Apache).'
                . ($this->ultimoErrorConversion !== '' ? ' Detalle técnico: ' . $this->ultimoErrorConversion : '')
            );
        }

        if (is_file($rutaDocumentoLleno)) {
            @unlink($rutaDocumentoLleno);
        }

        return $rutaPdf;
    }

    /**
     * Guarda el mensaje real de la última conversión fallida, para poder
     * mostrarlo en vez del genérico "no se pudo convertir" (ver generar()).
     */
    private string $ultimoErrorConversion = '';

    /**
     * Convierte el documento a PDF usando Word/Excel reales via COM (solo
     * funciona en Windows y con Office instalado). Regresa false si no se pudo.
     */
    private function convertirAPdfConOffice(string $rutaDocumento, string $rutaPdfDestino, string $extension): bool
    {
        $this->ultimoErrorConversion = '';

        if (! class_exists('COM')) {
            $this->ultimoErrorConversion = 'La clase COM de PHP no está disponible '
                . '(extension=com_dotnet no está activa en el php.ini).';
            return false;
        }

        $rutaDocumentoReal = realpath($rutaDocumento);
        if ($rutaDocumentoReal === false) {
            $this->ultimoErrorConversion = 'No se encontró el archivo ya lleno en "' . $rutaDocumento . '" '
                . '(realpath() no lo encontró).';
            return false;
        }

        $rutaDocumentoAbs = str_replace('/', '\\', $rutaDocumentoReal);
        $rutaPdfAbs        = str_replace('/', '\\', $rutaPdfDestino);

        // Si Open() o SaveAs()/ExportAsFixedFormat() truenan a la mitad,
        // SIN este try/finally nunca se llega a Quit(): Word/Excel se
        // queda abierto en segundo plano con el archivo .docx/.xlsx
        // detenido (bloqueado), y el unlink() de generar() justo después
        // truena con "Resource temporarily unavailable" — un error de
        // Windows que tapa el error real de la conversión. Por eso
        // Quit() va en finally: pase lo que pase, Word/Excel se cierra
        // antes de salir de esta función.
        $app = null;

        // Cada paso se marca con una etiqueta ("al abrir", "al guardar
        // como PDF", "al cerrar"), porque "type mismatch" solo no dice
        // cuál de las llamadas a Word/Excel fue la que realmente falló.
        $paso = 'al iniciar Word/Excel';

        try {
            if ($extension === 'docx') {
                $app = new \COM('Word.Application');
                $app->Visible = false;

                $paso = 'al abrir el documento';
                $documento = $app->Documents->Open($rutaDocumentoAbs);

                $paso = 'al guardar como PDF';
                // SaveAs($ruta, 17) truena con "type mismatch" porque
                // SaveAs tiene muchos parámetros opcionales con tipos
                // muy específicos y la llamada posicional desde PHP no
                // siempre los acomoda bien. ExportAsFixedFormat() es el
                // método que Word trae hecho justo para exportar a PDF
                // (no para "guardar como" en general), con solo 2
                // parámetros obligatorios: es el mismo que ya usamos con
                // Excel, y ahí sí funciona sin problema.
                $documento->ExportAsFixedFormat($rutaPdfAbs, 17); // 17 = wdExportFormatPDF

                $paso = 'al cerrar el documento';
                // A diferencia de Excel, en Word el parametro "guardar
                // cambios" de Close() es del tipo WdSaveOptions (un
                // numero: 0/-1/-2), no un booleano real. Pasarle un
                // false de PHP directo revienta con "type mismatch".
                $documento->Close(0); // 0 = wdDoNotSaveChanges
            } else {
                $app = new \COM('Excel.Application');
                $app->Visible = false;
                $app->DisplayAlerts = false;

                $paso = 'al abrir el libro';
                $libro = $app->Workbooks->Open($rutaDocumentoAbs);

                $paso = 'al exportar como PDF';
                $libro->ExportAsFixedFormat(0, $rutaPdfAbs); // 0 = xlTypePDF

                $paso = 'al cerrar el libro';
                $libro->Close(false);
            }
        } catch (\Throwable $e) {
            // Antes este error se tiraba silenciosamente. Se guarda aquí
            // para que generar() lo pueda mostrar y así ya no haya que
            // adivinar por qué falló la conversión.
            $this->ultimoErrorConversion = $paso . ': ' . $e->getMessage();
            return false;
        } finally {
            if ($app !== null) {
                try {
                    $app->Quit();
                } catch (\Throwable $e) {
                    // si ni siquiera esto funciona, ya no hay nada más que
                    // hacer aquí; seguir no sirve de nada.
                }
            }
        }

        return is_file($rutaPdfDestino);
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
        // Algunos machotes traen "cuadros de texto" de Word (controles de
        // contenido / content controls, ej. casillas en amarillo para
        // llenar a mano) alrededor de algún ${marcador}. PhpWord, al abrir
        // el archivo, intenta recomponer marcadores partidos en varias
        // piezas de texto y a veces borra la etiqueta de apertura de ese
        // cuadro sin borrar la de cierre, dejando un .docx que Word
        // considera corrupto al convertirlo a PDF. Aplanarlos (quitar el
        // cuadro y dejar solo su texto) antes de que PhpWord toque el
        // archivo evita el problema. No se tocan los campos nativos de
        // Word (como "Página X de Y"), solo los cuadros de texto sueltos.
        $rutaPlantillaAplanada = $this->aplanarControlesDeContenido($rutaPlantilla);

        try {
            $tp = new TemplateProcessor($rutaPlantillaAplanada);

            // si el marcador no esta no ocurre error
            foreach ($valores as $nombrePlaceholder => $valorReal) {
                $tp->setValue($nombrePlaceholder, (string) $valorReal);
            }
            try {
                // cloneRowAndSetValues(): 1.encuentra la fila de la tabla que tiene y la duplica una vez por cada partida,
                // 2. llena cada copia con los valores de su propia partida
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
            // la copia aplanada es solo de trabajo, nunca se guarda
            if (is_file($rutaPlantillaAplanada)) {
                unlink($rutaPlantillaAplanada);
            }
        }

        // cloneRowAndSetValues() duplica la fila de la tabla de partidas tal
        // cual, sin tocar los identificadores internos w14:paraId/w14:textId
        // de cada párrafo (son para el control de cambios de Word, no para
        // el contenido). Si el machote tiene 2+ partidas, esos IDs quedan
        // repetidos en varias filas, y Word rechaza el archivo como
        // "corrupto" al abrirlo por automatización (COM). Son opcionales:
        // quitarlos no afecta el contenido ni el formato del documento.
        $this->quitarIdsDuplicadosDeParrafos($rutaDocxLleno);

        return $rutaDocxLleno;
    }

    /**
     * Quita los "cuadros de texto" (controles de contenido / <w:sdt>) de un
     * machote de Word y regresa la ruta de una COPIA nueva ya sin ellos
     * (nunca toca el archivo original). Se deja intacto el contenido de
     * adentro del cuadro, solo se quita el cuadro en sí; los campos nativos
     * de Word (como "Página X de Y" en el pie de página) no se tocan. Ver
     * el comentario en llenarPlantillaWord() para el porqué.
     */
    private function aplanarControlesDeContenido(string $rutaPlantillaOriginal): string
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
                    // Los campos nativos de Word (num. de pagina, fecha
                    // automatica, etc.) sí son necesarios: se dejan intactos.
                    if ($xpath->query('.//w:docPartGallery', $sdt)->length > 0) {
                        continue;
                    }

                    $sdtContent = $xpath->query('./w:sdtContent', $sdt)->item(0);
                    if (! $sdtContent) {
                        continue;
                    }

                    while ($sdtContent->firstChild) {
                        $sdt->parentNode->insertBefore($sdtContent->firstChild, $sdt);
                    }
                    $sdt->parentNode->removeChild($sdt);
                }

                $zip->addFromString('word/document.xml', $dom->saveXML($dom->documentElement));
            }
        }

        $zip->close();

        return $rutaTemp;
    }

    /**
     * Quita los atributos w14:paraId/w14:textId del documento ya guardado
     * (ver el comentario en llenarPlantillaWord()). Se hace directamente
     * sobre el .docx ya guardado porque TemplateProcessor no expone una
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
                // celdas con texto de formato mixto (ej. "ETIQUETA: ${marcador}")
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
                    $this->reemplazarPlaceholders($textoOriginal, $datosPartida)
                );
            }
        }
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
                    $celda->setValue($this->reemplazarPlaceholders($valorCelda, $valores));
                    continue;
                }

                // celdas con texto de formato mixto (ej. "ETIQUETA: ${marcador}"),
                // PhpSpreadsheet las guarda como RichText y no como string simple
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

    /**
     * Convierte un monto a su representacion en letras para contratos
     * (ej. 553.32 -> "QUINIENTOS CINCUENTA Y TRES PESOS 32/100 M.N.")
     */
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