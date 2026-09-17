<?php

namespace App\Controllers;


class Home extends BaseController
{
        public function index()
    {
        $data = [
            'rol_id' => 1, 
            'titulo' => 'Panel Administrador',
        ];

        return view('admin/dashboard', $data);
    }
}