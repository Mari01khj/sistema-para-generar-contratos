<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AreasModel;

class AreasController extends BaseController
{
    protected $areaModel;

    public function __construct()
    {
        $this->areaModel = new AreasModel();
    }

   
    public function index()
    {
        $areas = $this->areaModel->findAll();
        return $this->response->setJSON($areas);
    }

    // METODO PARA CREAR UN NUEVO AREA
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) 
        {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se recibieron datos.'
            ]);
        }

        if (!isset($data['activo'])) 
        {
            $data['activo'] = 1;
        }

        if ($this->areaModel->insert($data)) 
        {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Área registrada correctamente',
                'id'      => $this->areaModel->getInsertID()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->areaModel->errors()
        ]);
    }
}