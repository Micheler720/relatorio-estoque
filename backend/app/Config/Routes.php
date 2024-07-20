<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Api;

/**
 * @var RouteCollection $routes
 */

 /*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
// $routes->setDefaultNamespace('App\Controllers');
// $routes->setDefaultController('Home');
// $routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(true);

$routes->get('getRelatorioDespesas', 'Home::getRelatorioDespesas');
$routes->get('/', 'Home::index');
// $routes->get('/', [Home::class, 'index']);
// $routes->get('/', 'Home::index');



