<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─────────────── Rutas públicas (sin sesión) ───────────────
$routes->get('login', 'AuthController::login', ['as' => 'loginForm']);
$routes->post('login-process', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

// ─────────────── Rutas privadas: requieren haber iniciado sesión ───────────────
$routes->group('', ['filter' => 'authFilter'], static function ($routes) {

    // "/" y "/dashboard" solo redirigen al dashboard que le toca al rol
    $routes->get('/', 'Home::index');
    $routes->get('dashboard', 'Home::index');

    // ── Solo ADMINISTRADOR (rol 1) ──
    // authFilter (grupo padre) + roleFilter:1 se aplican en ese orden.
    $routes->group('', ['filter' => 'roleFilter:1'], static function ($routes) {

        $routes->get('admin/dashboard', 'AdminController::dashboard');

        $routes->group('tipos-contrato', static function ($routes) {
            $routes->get('/', 'TipoContratoController::gestion', ['as' => 'tiposContratoGestion']);
            $routes->post('crear', 'TipoContratoController::create');

            // Campos de UN tipo de contrato en particular.
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
        });

        $routes->group('areas', static function ($routes) {
            $routes->get('/', 'AreasController::index');
            $routes->post('crear', 'AreasController::create');
        });

        $routes->group('usuarios', static function ($routes) {
            $routes->get('/', 'UserController::index');
            $routes->post('crear', 'UserController::create');
        });
    });

    // ── Solo OPERADOR (rol 2) ──
    $routes->group('', ['filter' => 'roleFilter:2'], static function ($routes) {
        $routes->get('operador/dashboardOperador', 'OperadorController::dashboardOperador');
    });

    // ── Administrador y Operador ──
    $routes->group('contratos', ['filter' => 'roleFilter:1,2'], static function ($routes) {
        $routes->get('/', 'ContratosController::index');
        $routes->post('crear', 'ContratosController::create');
    });
});
