<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TipoContratoModel;

class TipoContratoController extends ResourceController
{
    protected $modelName = 'App\Models\TipoContrato';
    protected $format    = 'json';

    //FUNCION PARA CREAR TIPOS DE CONTRATO NUEVO
    public function create()
    {
        $data = [
            'nombre'             => $this->request->getPost('nombre'),
            'requiere_conceptos' => $this->request->getPost('requiere_conceptos') ?? 0,
            'lleva_precios'      => $this->request->getPost('lleva_precios') ?? 0,
            'datos_entrega'      => $this->request->getPost('datos_entrega') ?? 0,
            'activo'             => $this->request->getPost('activo') ?? 1,
            ];
            
            
        $archivo = $this->request->getFile('plantilla');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            //extensiones 
            $extensionesValidas = ['docx', 'doc', 'xlsx', 'xls'];
            $extension = $archivo->getClientExtension();

            if (!in_array(strtolower($extension), $extensionesValidas)) {
                return $this->fail('formato del machote no válido', 400);
            }

            //guardarlos  
            $nuevoNombre = $archivo->getRandomName();
            $archivo->move(WRITEPATH . 'uploads/plantillas', $nuevoNombre);

            $data['plantilla'] = 'uploads/plantillas/' . $nuevoNombre;
        } 
        else 
        {
            return $this->fail('Sube un archivo de plantilla válido.', 400);
        }

        // Guardarlos en base de datos
        if ($this->model->insert($data)) 
        {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'registro correcto',
                'id'      => $this->model->getInsertID(),
                'archivo' => $data['plantilla']
            ]);
        }

        return $this->failValidationErrors($this->model->errors());
    }
}