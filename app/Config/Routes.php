<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

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

