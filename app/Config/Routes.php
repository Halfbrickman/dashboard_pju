<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('dashboard', 'Dashboard::index');

// Routes Sumber Data
$routes->get('sumberdata', 'sumberDataController::index');
$routes->get('sumberdata/form', 'sumberDataController::form');
$routes->get('sumberdata/form/(:num)', 'sumberDataController::form/$1');
$routes->post('sumberdata/saveOrUpdate', 'sumberDataController::saveOrUpdate');
$routes->post('sumberdata/delete/(:any)', 'sumberDataController::delete/$1');

$routes->group('judul-keterangan', function ($routes) {
    $routes->get('/', 'JudulKeteranganController::index');
    $routes->get('form', 'JudulKeteranganController::form');
    $routes->get('form/(:num)', 'JudulKeteranganController::form/$1');
    $routes->post('saveOrUpdate', 'JudulKeteranganController::saveOrUpdate');
    $routes->delete('delete/(:num)', 'JudulKeteranganController::delete/$1');
});

// Perubahan Rute untuk Master Data Koordinat
$routes->get('koordinat', 'MasterDataController::index');
$routes->get('koordinat/form', 'MasterDataController::form'); // Rute untuk form tambah
$routes->get('koordinat/form/(:num)', 'MasterDataController::form/$1'); // Rute untuk form edit
$routes->post('koordinat/save', 'MasterDataController::save');
$routes->post('koordinat/delete/(:num)', 'MasterDataController::delete/$1');

// Rute API untuk dropdown dinamis
$routes->group('api', function ($routes) {
    $routes->get('kecamatan/(:num)', 'MasterDataController::getKecamatanByKotaKab/$1');
    $routes->get('kelurahan/(:num)', 'MasterDataController::getKelurahanByKecamatan/$1');
    $routes->get('judul-keterangan/(:num)', 'MasterDataController::getJudulKeteranganBySumberData/$1');
});