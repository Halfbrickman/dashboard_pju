<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('dashboard', 'Dashboard::index');

// Rute default yang mengarahkan ke halaman login
$routes->get('/', 'AuthController::login');

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
$routes->get('koordinat/import', 'KoordinatController::import');
$routes->post('koordinat/upload', 'KoordinatController::upload');
$routes->post('koordinat/uploadPhotos/(:num)', 'KoordinatController::uploadPhotos/$1');

// Rute API untuk dropdown dinamis
$routes->group('api', function ($routes) {
    $routes->get('kecamatan/(:num)', 'MasterDataController::getKecamatanByKotaKab/$1');
    $routes->get('kelurahan/(:num)', 'MasterDataController::getKelurahanByKecamatan/$1');
    $routes->get('judul-keterangan/(:num)', 'MasterDataController::getJudulKeteranganBySumberData/$1');
});

// Rute untuk peta
$routes->get('/maps', 'MapController::index');
$routes->get('/api/markers', 'MapController::getMarkerData');
$routes->get('map/exportKML', 'MapController::exportKML');
$routes->get('map/exportExcel', 'MapController::exportExcel');
$routes->get('map/exportPDF', 'MapController::exportPDF');

// Rute untuk Auth
$routes->get('/login', 'AuthController::login');
$routes->post('/auth/processLogin', 'AuthController::processLogin');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/register', 'AuthController::register'); // Rute registrasi
$routes->post('/auth/processRegister', 'AuthController::processRegister'); // Rute proses registrasi

// Rute untuk halaman yang dilindungi
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('koordinat', 'Koordinat::index', ['filter' => 'auth']);
$routes->get('sumberdata', 'SumberData::index', ['filter' => 'auth']);
$routes->get('judul-keterangan', 'JudulKeterangan::index', ['filter' => 'auth']);

//Rute Register Admin
$routes->get('register/admin', 'AuthController::registerAdmin');
$routes->post('auth/processRegisterAdmin', 'AuthController::processRegisterAdmin');

// Rute untuk registrasi
$routes->get('/register', 'AuthController::register');
$routes->post('/auth/processRegister', 'AuthController::processRegister');

// Rute CUD yang hanya bisa diakses admin
$routes->group('', ['filter' => 'admin'], function ($routes) {
    // Rute untuk Koordinat
    $routes->get('koordinat/form', 'Koordinat::form');
    $routes->get('koordinat/form/(:num)', 'Koordinat::form/$1');
    $routes->post('koordinat/save', 'Koordinat::save');
    $routes->post('koordinat/delete/(:num)', 'Koordinat::delete/$1');

    // Rute untuk Sumber Data
    $routes->get('sumberdata/form', 'SumberData::form');
    $routes->get('sumberdata/form/(:num)', 'SumberData::form/$1');
    $routes->post('sumberdata/save', 'SumberData::save');
    $routes->post('sumberdata/delete/(:num)', 'SumberData::delete/$1');

    // Rute untuk Judul Keterangan
    $routes->get('judul-keterangan/form', 'JudulKeterangan::form');
    $routes->get('judul-keterangan/form/(:num)', 'JudulKeterangan::form/$1');
    $routes->post('judul-keterangan/save', 'JudulKeterangan::save');
    $routes->post('judul-keterangan/delete/(:num)', 'JudulKeterangan::delete/$1');
});
$routes->group('api', function ($routes) {
    $routes->post('markers/update/(:any)', 'Api\Koordinat::update/$1');
});
$routes->post('api/markers/update', 'MapController::updateMarker/$1');

$routes->get('api/kecamatan_by_kotakab', 'MapController::getKecamatan');
$routes->get('api/kelurahan_by_kecamatan', 'MapController::getKelurahan');