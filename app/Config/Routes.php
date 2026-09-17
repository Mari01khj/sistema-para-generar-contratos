<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');
//=================================
//RUTAS PARA EL LOGIN
//=================================

$routes->get('/',      'AuthController::login');
$routes->get('login',  'AuthController::login');
$routes->post('login', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

//ADMIN
$routes->group('admin', ['filter' => 'role:1'], function($routes) 
{
    $routes->get('dashboard',      'AdminController::dashboard');
    $routes->get('proveedores',    'ProveedoresController::index');
    $routes->get('areas',          'AreasController::index');
    $routes->get('tipos-contrato', 'TiposContratoController::index');
    $routes->get('usuarios',       'UserController::index');
});

// OPERADOR
$routes->group('operador', ['filter' => 'role:2'], function($routes) 
{
    $routes->get('dashboard', 'Operador::dashboard');
    $routes->get('perfil',    'Operador::perfil');
});


$routes->group('contratos', ['filter' => 'role:1,2'], function($routes) 
{
    $routes->get('/',           'Contratos::index');
    $routes->get('nuevo',       'Contratos::crear');
    $routes->post('guardar',    'Contratos::guardar');
});


//================================
//RUTAS PARA LAS AREAS
//================================
$routes->group('areas', static function ($routes) 
{
    $routes->get('/', 'AreasController::index');
    $routes->post('crear', 'AreasController::create');
});
//================================
//RUTAS PARA LOS PROVEEDORES
//================================
$routes->group('proveedores', static function ($routes) 
{
    $routes->get('/', 'ProveedoresController::index');
    $routes->post('crear', 'ProveedoresController::create');
});

//=================================
//RUTAS PARA LOS USUARIOS
//=================================
$routes->group('usuarios', static function ($routes) 
{
    $routes->get('/',        'UserController::index');
    $routes->post('crear', 'UserController::create');
});

//=================================
//RUTAS PARA LOS TIPOS DE CONTRATO  
//=================================
$routes->group('tiposContrato', static function ($routes) 
{
    $routes->get('/', 'TipoContratoController::index');
    $routes->post('crear', 'TipoContratoController::create');
});

//=================================
//RUTAS PARA LOS ROLES
//================================= 
$routes->group('roles', static function ($routes) 
{
    $routes->get('/', 'RolesController::index');
    $routes->post('crear', 'RolesController::create');
});

//=================================
//RUTAS PARA LOS CONTRATOS
//=================================
$routes->group('contratos', static function ($routes) 
{
    $routes->get('/', 'ContratosController::index');
    $routes->post('crear', 'ContratosController::create');
});

//=================================
//RUTAS PARA LOS BIENES
//=================================
$routes->group('bienes', static function ($routes) 
{
    $routes->get('/', 'BienesController::index');
    $routes->post('crear', 'BienesController::create');
});

$routes->get('/', 'Home::index');