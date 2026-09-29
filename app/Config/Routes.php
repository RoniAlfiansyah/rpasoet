<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Tides::index');
$routes->get('/tides', 'Tides::index');
$routes->get('/admiralty', 'Tides::admiralty');
$routes->post('/admiralty/reset', 'Tides::resetAdmiralty');
$routes->post('/admiralty/validate-upload', 'Tides::validateAdmiraltyUpload');
$routes->post('/admiralty/save-dataset', 'Tides::saveAdmiraltyDataset');
$routes->post('/admiralty/start-calculation', 'Tides::startAdmiraltyCalculation');
$routes->post('/admiralty/generate-prediction', 'Tides::generateAdmiraltyPrediction');
$routes->get('/admiralty/download-sample/(:segment)', 'Tides::downloadSample/$1');
$routes->get('/admiralty/download-sample', 'Tides::downloadSample');
$routes->get('/tides/fetch-day', 'Tides::fetchDay');
$routes->get('/tides/fetch-range', 'Tides::fetchRange');

// Authentication Routes (Passcode Access)
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::processLogin');
$routes->get('/logout', 'Auth::logout');
