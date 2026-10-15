<?php

namespace App\Libraries;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

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
                'Faltan las librerías para generar el PDF. Corre "composer install" '
                . '(o "composer require dompdf/dompdf phpoffice/phpword") en la carpeta '
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

        
        if (strtolower(pathinfo($rutaPlantilla, PATHINFO_EXTENSION)) !== 'docx') 
        {
            throw new RuntimeException(
                'La plantilla de este tipo de contrato no es un archivo .docx '
                . '(Word moderno). Vuelve a subirla guardada en ese formato '
                . 'desde Word con "Guardar como" → "Documento de Word (.docx)".'
            );
        }

        $tp = new TemplateProcessor($rutaPlantilla);

        $valores = array_merge(
            $this->datosFijos($contrato),
            $this->datosProveedor($proveedor),
            $this->datosArea($area)
        );

        foreach ($valoresDinamicos as $valor) {
            $valores[$valor['nombre_campo']] = $valor['valor'] ?? '';
        }

        foreach ($valores as $nombrePlaceholder => $valorReal) {
            $tp->setValue($nombrePlaceholder, (string) $valorReal);
        }

        try {
            $filas = [];
            foreach ($partidas as $partida) {
                $filas[] = [
                    'partida'    => (string) $partida['num_partida'],
                    'nombre_bien' => $partida['descripcion'],
                    'u_medida'   => $partida['unidad_medida'],
                    'cantidad'   => (string) $partida['cantidad'],
                    'marca'      => $partida['marca'] ?? '',
                    'p_unit'     => number_format((float) $partida['precio_unitario'], 2),
                    'importe'    => number_format((float) $partida['importe'], 2),
                ];
            }

            $tp->cloneRowAndSetValues('partida', $filas);
        } catch (\Exception $e) {
            throw new RuntimeException(
                'La plantilla de este tipo de contrato no tiene preparada la '
                . 'tabla de Partidas (debe tener una fila con el texto '
                . '"${partida}" dentro de una tabla). Revisa el Word y vuelve '
                . 'a intentar.'
            );
        }

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
        $rutaDocxLleno = $carpetaTemporal . '/' . $nombreBase . '.docx';
        $rutaPdf       = $carpetaTemporal . '/' . $nombreBase . '.pdf';

        $tp->saveAs($rutaDocxLleno);

        
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(VENDORPATH . 'dompdf/dompdf');

        $documentoLleno = IOFactory::load($rutaDocxLleno);
        IOFactory::createWriter($documentoLleno, 'PDF')->save($rutaPdf);

        unlink($rutaDocxLleno);

        return $rutaPdf;
    }
}
