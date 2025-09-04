<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminDashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard Admin',
            // Tambahkan data khusus untuk halaman admin di sini
        ];

        // Tampilkan view khusus admin
        return view('Template/header', $data)
             . view('Template/sidebar')
             . view('admin/dashboard') // Pastikan ada file view ini
             . view('Template/assetDashboard')
             . view('Template/footer');
    }
}