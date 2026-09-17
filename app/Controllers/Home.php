<?php

namespace App\Controllers;


class Home extends BaseController
{
    public function index(): string
    {
        return view('dashboard');
    }
}


class Home extends BaseController
{
    public function index()
{
    $data = [
        'rol_id' => 1, 
        'titulo' => 'Panel Admin'
    ];

    return view('admin/dashboard', $data);
}
}