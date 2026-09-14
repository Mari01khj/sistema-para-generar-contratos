<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TipoContrato; // Ajusta según el nombre de tu archivo en Models

class TipoContratoController extends BaseController
{
    protected $tipoContratoModel;

    public function __construct()
    {
        $this->tipoContratoModel = new TipoContrato();
    }

    // GET: /tipos-contrato
    public function index()
    {
        $tipos = $this->tipoContratoModel->findAll();
        return $this->response->setJSON($tipos);
    }

    // POST: /tipos-contrato/crear
    public function create()
    {
        // 1. Obtener valores de texto (vienen por POST / multipart form-data)
        $data = [
            'nombre'             => $this->request->getPost('nombre'),
            'requiere_conceptos' => $this->request->getPost('requiere_conceptos') ?? 0,
            'lleva_precios'      => $this->request->getPost('lleva_precios') ?? 0,
            'datos_entrega'      => $this->request->getPost('datos_entrega') ?? 0,
            'activo'             => $this->request->getPost('activo') ?? 1,
        ];

        // 2. Procesar el archivo físico de la plantilla
        $archivo = $this->request->getFile('plantilla');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $extension = strtolower($archivo->getClientExtension());
            $permitidas = ['docx', 'doc', 'xlsx', 'xls'];

            if (!in_array($extension, $permitidas)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 400,
                    'message' => 'Formato no permitido. Solo se aceptan archivos Word (.docx) o Excel (.xlsx).'
                ]);
            }

            // Forzar nombre único con la extensión real
            $nuevoNombre = uniqid('plantilla_', true) . '.' . $extension;
            $archivo->move(WRITEPATH . 'uploads/plantillas', $nuevoNombre);

            $data['plantilla'] = 'uploads/plantillas/' . $nuevoNombre;
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'Es obligatorio adjuntar un archivo de plantilla válido.'
            ]);
        }

        // 3. Inserción en la base de datos
        if ($this->tipoContratoModel->insert($data)) {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Tipo de contrato registrado exitosamente',
                'id'      => $this->tipoContratoModel->getInsertID(),
                'archivo' => $data['plantilla']
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->tipoContratoModel->errors()
        ]);
    }
}