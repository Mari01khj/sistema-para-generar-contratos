<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        helper('sesion');

        return redirect()->to(ruta_dashboard());
    }
}
