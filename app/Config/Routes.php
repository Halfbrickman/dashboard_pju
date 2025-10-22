<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('dashboard', 'Dashboard::index');
$routes->get('/dashboard/downloadNotificationFile/(:num)', 'Dashboard::downloadNotificationFile/$1');

// Rute default yang mengarahkan ke halaman login
$routes->get('/', 'AuthController::login');

// Routes Sumber Data (Biarkan SumberDataController yang lama)
$routes->get('sumberdata', 'SumberDataController::index');
$routes->get('sumberdata/form', 'SumberDataController::form');
$routes->get('sumberdata/form/(:num)', 'SumberDataController::form/$1');
$routes->post('sumberdata/saveOrUpdate', 'SumberDataController::saveOrUpdate');
$routes->delete('sumberdata/delete/(:any)', 'SumberDataController::delete/$1'); 

// Routes Judul Keterangan (Biarkan JudulKeteranganController yang lama)
$routes->group('judul-keterangan', function ($routes) {
    $routes->get('/', 'JudulKeteranganController::index');
    $routes->get('form', 'JudulKeteranganController::form');
    $routes->get('form/(:num)', 'JudulKeteranganController::form/$1');
    $routes->post('saveOrUpdate', 'JudulKeteranganController::saveOrUpdate');
    $routes->delete('delete/(:num)', 'JudulKeteranganController::delete/$1'); 
});

// =================================================================================
// PENYESUAIAN RUTE KOORDINAT: Semua menunjuk ke KoordinatController yang BARU (hasil gabungan)
// =================================================================================

// Rute CRUD dan Listing
$routes->get('koordinat', 'KoordinatController::index');
$routes->get('koordinat/form', 'KoordinatController::form'); // Rute untuk form tambah
$routes->get('koordinat/form/(:num)', 'KoordinatController::form/$1'); // Rute untuk form edit
$routes->post('koordinat/save', 'KoordinatController::save');
$routes->post('koordinat/delete/(:num)', 'KoordinatController::delete/$1'); 
$routes->post('koordinat/delete_multiple', 'KoordinatController::deleteMultiple');

// Rute Import
$routes->get('koordinat/import', 'KoordinatController::import');
$routes->post('koordinat/upload', 'KoordinatController::upload');
$routes->post('koordinat/uploadPhotos', 'KoordinatController::uploadPhotos');


// Rute API untuk dropdown dinamis (Sekarang merujuk ke KoordinatController baru)
$routes->group('api', function ($routes) {

    // --- Rute untuk Peta (Tetap di MapController) ---
    $routes->get('markers', 'MapController::getMarkerData');
    $routes->post('markers/update', 'MapController::updateMarker');
    $routes->post('koordinat/delete/(:num)', 'MapController::deleteMarker/$1');
    $routes->post('photo/delete/(:num)', 'MapController::deletePhoto/$1');

    // --- Rute untuk Data Master Wilayah & Keterangan (Pindah ke KoordinatController) ---
    // Rute yang lama (kecamatan_by_kotakab & kelurahan_by_kecamatan) mungkin bisa dihapus/diarahkan
    $routes->get('kecamatan_by_kotakab', 'MapController::getKecamatan'); // Biarkan jika MapController butuh ini
    $routes->get('kelurahan_by_kecamatan', 'MapController::getKelurahan'); // Biarkan jika MapController butuh ini
    
    $routes->get('kecamatan/(:num)', 'KoordinatController::getKecamatanByKotaKab/$1');
    $routes->get('kelurahan/(:num)', 'KoordinatController::getKelurahanByKecamatan/$1');
    $routes->get('judul-keterangan/(:num)', 'KoordinatController::getJudulKeteranganBySumberData/$1');

});

// Rute untuk peta
$routes->get('/maps', 'MapController::index');
$routes->get('/api/markers', 'MapController::getMarkerData');
$routes->get('map/exportKML', 'MapController::exportKML');
$routes->get('map/exportExcel', 'MapController::exportExcel');
$routes->get('map/exportPDF', 'MapController::exportPDF');
$routes->post('map/deleteMarker/(:num)', 'MapController::deleteMarker/$1');

$routes->get('galeri', 'GaleriController::index');

// Rute untuk Auth
$routes->get('/login', 'AuthController::login');
$routes->post('/auth/processLogin', 'AuthController::processLogin');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/register', 'AuthController::register'); 
$routes->post('/auth/processRegister', 'AuthController::processRegister'); 

// Rute untuk halaman yang dilindungi (pastikan menunjuk ke Controller yang baru)
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('koordinat', 'KoordinatController::index', ['filter' => 'auth']);
$routes->get('sumberdata', 'SumberDataController::index', ['filter' => 'auth']);
$routes->get('judul-keterangan', 'JudulKeteranganController::index', ['filter' => 'auth']); 

//Rute Register Admin
$routes->get('register/admin', 'AuthController::registerAdmin');
$routes->post('auth/processRegisterAdmin', 'AuthController::processRegisterAdmin');

// Rute CUD yang hanya bisa diakses admin
$routes->group('', ['filter' => 'admin'], function ($routes) {
    // Rute untuk Koordinat (semua mengarah ke KoordinatController)
    $routes->get('koordinat/form', 'KoordinatController::form');
    $routes->get('koordinat/form/(:num)', 'KoordinatController::form/$1');
    $routes->post('koordinat/save', 'KoordinatController::save');
    $routes->post('koordinat/delete/(:num)', 'KoordinatController::delete/$1');

    // Rute untuk Sumber Data (tetap)
    $routes->get('sumberdata/form', 'SumberDataController::form');
    $routes->get('sumberdata/form/(:num)', 'SumberDataController::form/$1');
    $routes->post('sumberdata/save', 'SumberDataController::saveOrUpdate'); // Disarankan pakai saveOrUpdate
    $routes->post('sumberdata/delete/(:num)', 'SumberDataController::delete/$1'); // Gunakan DELETE jika bisa

    // Rute untuk Judul Keterangan (tetap)
    $routes->get('judul-keterangan/form', 'JudulKeteranganController::form');
    $routes->get('judul-keterangan/form/(:num)', 'JudulKeteranganController::form/$1');
    $routes->post('judul-keterangan/save', 'JudulKeteranganController::saveOrUpdate'); // Disarankan pakai saveOrUpdate
    // $routes->post('judul-keterangan/delete/(:num)', 'JudulKeteranganController::delete/$1'); // Sudah di handle di group di atas
});

// Rute User Management
$routes->get('users', 'UserController::index');
$routes->get('users/create', 'UserController::create');
$routes->post('users/save', 'UserController::save');
$routes->get('users/edit/(:num)', 'UserController::edit/$1');
$routes->post('users/update/(:num)', 'UserController::update/$1');
$routes->delete('users/delete/(:num)', 'UserController::delete/$1');