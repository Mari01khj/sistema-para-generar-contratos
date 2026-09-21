<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get ('login', 'AuthController::login', ['as' => 'loginForm']);
$routes->post('login-process', 'AuthController::authenticate');
$routes->get ('logout', 'AuthController::logout');


$routes->group('', ['filter' => 'authFilter'], static function ($routes) 
{

    $routes->get('/', 'Home::index');
    $routes->get('dashboard', 'Home::index');

//para el administrador
    $routes->group('', ['filter' => 'roleFilter:1'], static function ($routes) 
    {

        $routes->get('admin/dashboard', 'AdminController::dashboard');

        $routes->group('proveedores', static function ($routes) 
    {
            $routes->get ('/', 'ProveedoresController::index');
            $routes->post('crear', 'ProveedoresController::create');
        });

        $routes->group('areas', static function ($routes) 
        {
            $routes->get ('/', 'AreasController::index');
            $routes->post('crear', 'AreasController::create');
        });

        $routes->group('usuarios', static function ($routes) 
        {
            $routes->get('/', 'UserController::index');
            $routes->post('crear', 'UserController::create');
        });
    });
//para el operador
    $routes->group('', ['filter' => 'roleFilter:2'], static function ($routes) 
    {
        $routes->get('operador/dashboardOperador', 'OperadorController::dashboardOperador');
    });

//´para ambos roles
    $routes->group('contratos', ['filter' => 'roleFilter:1,2'], static function ($routes) 
    {
        $routes->get('/', 'ContratosController::index');
        $routes->post('crear', 'ContratosController::create');
    });
});
