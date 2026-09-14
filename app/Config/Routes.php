<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->resource('api/proveedores',        ['controller' => 'ProveedoresController']);
$routes->resource('api/usuarios',           ['controller' => 'UserController']);
$routes->resource('api/areas',              ['controller' => 'AreasController']);
$routes->resource('api/tipos-contrato',     ['controller' => 'TipoContratoController']);
$routes->resource('api/roles',              ['controller' => 'RolesController']);
$routes->resource('api/contratos',          ['controller' => 'ContratosController']);
$routes->resource('api/bienes',             ['controller' => 'BienesController']); 