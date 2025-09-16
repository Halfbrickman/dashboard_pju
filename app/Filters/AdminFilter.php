<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Periksa apakah user login dan apakah role_id-nya adalah admin (1)
        if (!session()->get('isLoggedIn') || session()->get('role_id') != 1) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki hak akses untuk halaman ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}