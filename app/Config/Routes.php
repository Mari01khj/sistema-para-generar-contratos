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
      
            $routes->post('(:num)/plantilla', 'TipoContratoController::actualizarPlantilla/$1');
            $routes->post('(:num)/folio', 'TipoContratoController::actualizarFolio/$1');

            // Campos
            $routes->get('(:num)/campos', 'CamposFormularioController::gestion/$1', ['as' => 'camposFormularioGestion']);
            $routes->post('(:num)/campos/crear', 'CamposFormularioController::create/$1');
        });

        $routes->group('campos', static function ($routes) 
        {
            $routes->post('(:num)/editar', 'CamposFormularioController::update/$1');
            $routes->post('(:num)/eliminar', 'CamposFormularioController::delete/$1');
            $routes->post('(:num)/activar', 'CamposFormularioController::activar/$1');
        });

        $routes->group('proveedores', static function ($routes) 
        {
            $routes->get('/', 'ProveedoresController::index');
            $routes->post('crear', 'ProveedoresController::create');
            $routes->get('list', 'ProveedoresController::listar');
            $routes->post('actualizar/(:num)', 'ProveedoresController::update/$1');
            $routes->post('eliminar/(:num)', 'ProveedoresController::delete/$1');
        });

        $routes->group('areas', static function ($routes)
        {
            $routes->get('/', 'AreasController::index');
            $routes->get('list', 'AreasController::listar');

            $routes->post('crear', 'AreasController::create');
            $routes->post('actualizar/(:num)', 'AreasController::update/$1');
            $routes->post('eliminar/(:num)', 'AreasController::delete/$1');
        });

        $routes->group('fuentes-financiamiento', static function ($routes)
        {
            $routes->get('/', 'FuentesFinanciamientoController::index');
            $routes->get('list', 'FuentesFinanciamientoController::listar');

            $routes->post('crear', 'FuentesFinanciamientoController::create');
            $routes->post('actualizar/(:num)', 'FuentesFinanciamientoController::update/$1');
            $routes->post('eliminar/(:num)', 'FuentesFinanciamientoController::delete/$1');
        });

        $routes->group('unidades-medida', static function ($routes)
        {
            $routes->get('/', 'UnidadesMedidaController::index');
            $routes->get('list', 'UnidadesMedidaController::listar');

            $routes->post('crear', 'UnidadesMedidaController::create');
            $routes->post('eliminar/(:num)', 'UnidadesMedidaController::delete/$1');
        });

        $routes->group('bienes-servicios', static function ($routes)
        {
            $routes->get('/', 'BienesServiciosController::index');
            $routes->get('list', 'BienesServiciosController::listar');
        });

        $routes->group('usuarios', static function ($routes) 
        {
            $routes->get('/', 'UserController::index');
            $routes->post('crear', 'UserController::create');
            $routes->get('list', 'UserController::listar');
            $routes->post('actualizar/(:num)', 'UserController::update/$1');
            $routes->post('eliminar/(:num)', 'UserController::delete/$1');
        });
    });

    $routes->group('', ['filter' => 'roleFilter:2'], static function ($routes) 
    {
        $routes->get('operador/dashboardOperador', 'OperadorController::dashboardOperador');
        $routes->get('perfil', 'OperadorController::misDatos');
        $routes->post('operador/actualizar-datos', 'OperadorController::actualizarDatos');
    });
   
    $routes->group('', ['filter' => 'roleFilter:1,2'], static function ($routes)
    {
        $routes->get('bienes-servicios/buscar', 'BienesServiciosController::buscar');
        $routes->get('unidades-medida/activas', 'UnidadesMedidaController::activas');
    });

    $routes->group('contratos', ['filter' => 'roleFilter:1,2'], static function ($routes)
    {
        $routes->get('/', 'ContratosController::index');
        $routes->get('nuevo', 'ContratosController::nuevo');
        $routes->post('crear', 'ContratosController::create');

        $routes->get('(:num)', 'ContratosController::ver/$1', ['as' => 'contratoVer']);
        $routes->get('(:num)/editar', 'ContratosController::editar/$1', ['as' => 'contratoEditar']);
       
        $routes->get('(:num)/documento', 'ContratosController::exportarDocumento/$1', ['as' => 'contratoDocumento']);
        $routes->post('(:num)/actualizar', 'ContratosController::actualizar/$1');
        $routes->post('(:num)/eliminar', 'ContratosController::eliminar/$1');
    });
});