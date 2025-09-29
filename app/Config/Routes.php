<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('dashboard', 'Dashboard::index');
$routes->get('/dashboard/downloadNotificationFile/(:num)', 'Dashboard::downloadNotificationFile/$1');

// Rute default yang mengarahkan ke halaman login
$routes->get('/', 'AuthController::login');

// Routes Sumber Data (Ganti delete ke DELETE method)
$routes->get('sumberdata', 'SumberDataController::index');
$routes->get('sumberdata/form', 'SumberDataController::form');
$routes->get('sumberdata/form/(:num)', 'SumberDataController::form/$1');
$routes->post('sumberdata/saveOrUpdate', 'SumberDataController::saveOrUpdate');
// Pastikan ini menggunakan DELETE method untuk konsistensi. Jika view menggunakan POST, ganti ke POST.
$routes->delete('sumberdata/delete/(:any)', 'SumberDataController::delete/$1'); 

// Routes Judul Keterangan (Pusat perubahan)
$routes->group('judul-keterangan', function ($routes) {
    $routes->get('/', 'JudulKeteranganController::index');
    $routes->get('form', 'JudulKeteranganController::form');
    $routes->get('form/(:num)', 'JudulKeteranganController::form/$1');
    $routes->post('saveOrUpdate', 'JudulKeteranganController::saveOrUpdate');
    // Rute ini akan digunakan untuk Soft Delete
    $routes->delete('delete/(:num)', 'JudulKeteranganController::delete/$1'); 
});

// Perubahan Rute untuk Master Data Koordinat
$routes->get('koordinat', 'MasterDataController::index');
$routes->get('koordinat/form', 'MasterDataController::form'); // Rute untuk form tambah
$routes->get('koordinat/form/(:num)', 'MasterDataController::form/$1'); // Rute untuk form edit
$routes->post('koordinat/save', 'MasterDataController::save');
// Rute delete di Koordinat sebaiknya juga menggunakan DELETE jika ingin konsisten dengan soft delete
$routes->post('koordinat/delete/(:num)', 'MasterDataController::delete/$1'); // Biarkan POST jika Anda menggunakan POST di view
$routes->get('koordinat/import', 'KoordinatController::import');
$routes->post('koordinat/upload', 'KoordinatController::upload');
// Rute untuk mengunggah foto tanpa parameter
$routes->post('koordinat/uploadPhotos', 'KoordinatController::uploadPhotos');

// **INI PERUBAHAN UTAMA:** Pindahkan rute delete_multiple ke sini
$routes->post('koordinat/delete_multiple', 'MasterDataController::deleteMultiple');


// Rute API untuk dropdown dinamis
$routes->group('api', function ($routes) {

    // --- Rute untuk Peta ---
    $routes->get('markers', 'MapController::getMarkerData');
    $routes->post('markers/update', 'MapController::updateMarker');
    $routes->post('koordinat/delete/(:num)', 'MapController::deleteMarker/$1');
    // Rute untuk menghapus foto
    $routes->post('photo/delete/(:num)', 'MapController::deletePhoto/$1');

    // --- Rute untuk Data Master (yang sudah ada sebelumnya) ---
    $routes->get('kecamatan_by_kotakab', 'MapController::getKecamatan');
    $routes->get('kelurahan_by_kecamatan', 'MapController::getKelurahan');
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

$routes->get('galeri', 'GaleriController::index');

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
$routes->get('judul-keterangan', 'JudulKeteranganController::index', ['filter' => 'auth']); // Ganti JudulKeterangan ke JudulKeteranganController

//Rute Register Admin
$routes->get('register/admin', 'AuthController::registerAdmin');
$routes->post('auth/processRegisterAdmin', 'AuthController::processRegisterAdmin');

// Rute untuk registrasi
$routes->get('/register', 'AuthController::register');
$routes->post('/auth/processRegister', 'AuthController::processRegister');

// Rute CUD yang hanya bisa diakses admin
// PENTING: Jika menggunakan group 'judul-keterangan' di atas, rute di bawah ini akan duplikat/redundant. 
// Saya anggap Anda menggunakan rute di atas. Jika Anda ingin memisahkannya, pastikan konsisten.
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
    $routes->get('judul-keterangan/form', 'JudulKeteranganController::form');
    $routes->get('judul-keterangan/form/(:num)', 'JudulKeteranganController::form/$1');
    $routes->post('judul-keterangan/save', 'JudulKeteranganController::save');
    // Jika Anda menggunakan rute group 'judul-keterangan' di atas, nonaktifkan rute ini
    // $routes->post('judul-keterangan/delete/(:num)', 'JudulKeteranganController::delete/$1'); 
});

// Tambahkan route ini di app/Config/Routes.php
$routes->get('users', 'UserController::index');
$routes->get('users/create', 'UserController::create');
$routes->post('users/save', 'UserController::save');
$routes->get('users/edit/(:num)', 'UserController::edit/$1');
$routes->post('users/update/(:num)', 'UserController::update/$1');
$routes->delete('users/delete/(:num)', 'UserController::delete/$1');