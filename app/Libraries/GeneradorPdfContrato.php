<?php

namespace App\Libraries;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

use RuntimeException;
/**

 *  1. PhpWord\TemplateProcessor abre el .docx tal cual y busca placeholders de
 *    para reemplazarlos por el valor real. El resultado es OTRO .docx
 *
 *  2. El .docx ya lleno se convierte a PDF con LibreOffice en modo
 *     "headless".
 */
class GeneradorPdfContrato
{

    private function datosFijos(array $contrato): array
    {
        return [
            'folio'               => $contrato['folio'],
            'descripcion'         => $contrato['descripcion_corta'],
            'fecha_entrega'       => $contrato['fecha_entrega'] ?? '',
            'lugar_entrega'       => $contrato['lugar_entrega'] ?? '',
            'plazo_pago'          => $contrato['plazo_pago'] ?? '',
            'subtotal'            => number_format((float) $contrato['subtotal'], 2),
            'iva'                 => number_format((float) $contrato['iva'], 2),
            'total'               => number_format((float) $contrato['total'], 2),

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

        //el .docx ya lleno se convierte a PDF

        $ejecutableLibreOffice = $this->rutaEjecutableLibreOffice();

        // Cada conversión usa (parámetro -env:UserInstallation).
        // por default, LibreOffice SIEMPRE usa la misma carpeta de perfil del sistema.
        $carpetaPerfil = $carpetaTemporal . '/perfil_' . $nombreBase;

        [$salidaComando, $codigoSalida] = $this->ejecutarComando($ejecutableLibreOffice, [
            '--headless',
            '--norestore',
            '-env:UserInstallation=file://' . $carpetaPerfil,
            '--convert-to',
            'pdf',
            '--outdir',
            $carpetaTemporal,
            $rutaDocumentoLleno,
        ]);

        // se borra la carpeta del perfil y el archivo .docx
        $this->borrarCarpeta($carpetaPerfil);
        unlink($rutaDocumentoLleno);

        if ($codigoSalida !== 0 || ! is_file($rutaPdf))
        {
            throw new RuntimeException(
                'No se pudo convertir el documento a PDF con LibreOffice. '
                . 'Revisa que esté bien instalado y que el archivo de la '
                . 'plantilla no esté dañado. Detalle técnico: '
                . $salidaComando
            );
        }

        return $rutaPdf;
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
        $tp = new TemplateProcessor($rutaPlantilla);

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

        return $rutaDocxLleno;
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
                $texto = $celda->getValue();
                if (is_string($texto) && str_contains($texto, '${')) {
                    $celda->setValue($this->reemplazarPlaceholders($texto, $valores));
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


    private function rutaEjecutableLibreOffice(): string
    {
        $candidatos = [
            'soffice',
            'libreoffice',
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ];

        foreach ($candidatos as $candidato) {
            [, $codigo] = $this->ejecutarComando($candidato, ['--version']);
            if ($codigo === 0) {
                return $candidato;
            }
        }

        throw new RuntimeException(
            'No se encontró LibreOffice instalado en esta computadora. Es '
            . 'necesario para convertir el Word a PDF '
            . 'Descárgalo gratis desde '
            . 'https://www.libreoffice.org/download/download/, instálalo, '
            . 'y vuelve a intentar .'
        );
    }

     private function ejecutarComando(string $ejecutable, array $argumentos): array
    {
        $descriptores = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proceso = @proc_open(
            array_merge([$ejecutable], $argumentos),
            $descriptores,
            $tuberias,
            null,
            null,
            ['bypass_shell' => true]
        );

        if (! is_resource($proceso)) {
            return ['No se pudo ejecutar el comando.', 1];
        }

        fclose($tuberias[0]);
        $salida      = stream_get_contents($tuberias[1]);
        $salidaError = stream_get_contents($tuberias[2]);
        fclose($tuberias[1]);
        fclose($tuberias[2]);

        $codigoSalida = proc_close($proceso);

        return [trim($salida . "\n" . $salidaError), $codigoSalida];
    }


    /**
     * Borra una carpeta junto con todo lo que tenga adentro
     */
    private function borrarCarpeta(string $ruta): void
    {
        if (! is_dir($ruta)) {
            return;
        }

        foreach (scandir($ruta) ?: [] as $elemento) {
            if ($elemento === '.' || $elemento === '..') {
                continue;
            }

            $rutaElemento = $ruta . '/' . $elemento;
            is_dir($rutaElemento) ? $this->borrarCarpeta($rutaElemento) : unlink($rutaElemento);
        }

        rmdir($ruta);
    }
}