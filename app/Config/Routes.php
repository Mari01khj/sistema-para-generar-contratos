<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('login', 'AuthController::login', ['as' => 'loginForm']);
$routes->post('login-process', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

//filtro 1 
$routes->group('', ['filter' => 'authFilter'], static function ($routes) 
{

    $routes->get('/', 'Home::index');
    $routes->get('dashboard', 'Home::index');

    //  ADMINISTRADOR con filtro 2 
    $routes->group('', ['filter' => 'roleFilter:1'], static function ($routes)
     {
        $routes->get('admin/dashboard', 'AdminController::dashboard');

        $routes->group('tipos-contrato', static function ($routes) 
        {
            $routes->get('/', 'TipoContratoController::gestion', ['as' => 'tiposContratoGestion']);
            $routes->post('crear', 'TipoContratoController::create');

            // Campos 
            $routes->get('(:num)/campos', 'CamposFormularioController::gestion/$1', ['as' => 'camposFormularioGestion']);
            $routes->post('(:num)/campos/crear', 'CamposFormularioController::create/$1');
        });

        $routes->group('campos', static function ($routes) {
            $routes->post('(:num)/editar', 'CamposFormularioController::update/$1');
            $routes->post('(:num)/eliminar', 'CamposFormularioController::delete/$1');
            $routes->post('(:num)/activar', 'CamposFormularioController::activar/$1');
        });

        $routes->group('proveedores', static function ($routes) {
            $routes->get('/', 'ProveedoresController::index');
            $routes->post('crear', 'ProveedoresController::create');
            $routes->get('list', 'ProveedoresController::listar');
        });

        $routes->group('areas', static function ($routes) {
            $routes->get('/', 'AreasController::index');
            $routes->post('crear', 'AreasController::create');
            $routes->get('list', 'AreasController::listar');
        });

        $routes->group('usuarios', static function ($routes) {
            $routes->get('/', 'UserController::index');
            $routes->post('crear', 'UserController::create');
            $routes->get('list', 'UserController::listar');
        });
    });

    $routes->group('', ['filter' => 'roleFilter:2'], static function ($routes) 
    {
        $routes->get('operador/dashboardOperador', 'OperadorController::dashboardOperador');
        $routes->get('perfil', 'OperadorController::misDatos');
        $routes->post('operador/actualizar-datos', 'OperadorController::actualizarDatos');
    });
    
    $routes->group('contratos', ['filter' => 'roleFilter:1,2'], static function ($routes) 
    {
        $routes->get('/', 'ContratosController::index');
        $routes->get('nuevo', 'ContratosController::nuevo');
        $routes->post('crear', 'ContratosController::create');
    });
});