<?php

namespace App\Controllers;


class Home extends BaseController
{
    public function index()
{
    $data = [
        'rol_id' => 1, // Le dice al sidebar que oculte catálogos
        'titulo' => 'Panel Admin'
    ];

    // Llamas directamente al archivo del administrador
    return view('admin/dashboard', $data);
}
}