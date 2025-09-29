<?php

namespace App\Controllers;

use App\Models\M_user;
use CodeIgniter\Controller; // <-- Dihapus karena kelas ini extends BaseController

class AuthController extends BaseController
{
    // Tampilkan halaman login
    public function login()
    {
        return view('auth/login');
    }

    // Proses login pengguna
    public function processLogin()
    {
        $session = session();
        $userModel = new M_user();

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            $hashedPassword = $user['password'];
            if (password_verify($password, $hashedPassword)) {
                
                // DATA SESSION DISESUAIKAN DENGAN FIELD BARU
                $ses_data = [
                    'id'            => $user['id'],
                    'username'      => $user['username'],
                    'nama'          => $user['nama'] ?? $user['username'], // Ambil nama, jika null gunakan username
                    'role_id'       => $user['role_id'],
                    'id_sumberdata' => $user['id_sumberdata'], // Tambahkan id_sumberdata
                    'isLoggedIn'    => true
                ];
                $session->set($ses_data);

                return redirect()->to('/dashboard');
            } else {
                // Password salah
                $session->setFlashdata('msg', 'Username atau password salah.');
                return redirect()->to('/login');
            }
        } else {
            // Username tidak ditemukan
            $session->setFlashdata('msg', 'Username atau password salah.');
            return redirect()->to('/login');
        }
    }

    // Tampilkan form registrasi admin (hanya untuk development)
    public function registerAdmin()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/');
        }
        return view('auth/register_admin');
    }

    // Proses registrasi admin
    public function processRegisterAdmin()
    {
        $session = session();
        $userModel = new M_user();
        
        $rules = [
            'username' => 'required|min_length[5]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]'
        ];

        if ($this->validate($rules)) {
            // DATA REGISTRASI ADMIN DISESUAIKAN
            $data = [
                'username' => $this->request->getVar('username'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
                'nama'     => $this->request->getVar('username'), // Default: nama sama dengan username
                'role_id'  => 1, // Role Admin
                'id_sumberdata' => null // Default: null untuk admin
            ];
            
            $userModel->insert($data);
            
            $session->setFlashdata('msg', 'Akun admin berhasil dibuat. Silakan login.');
            return redirect()->to('/login');

        } else {
            $session->setFlashdata('msg', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
    }

    // Tampilkan form registrasi user biasa
    public function register()
    {
        return view('auth/register');
    }

    // Proses registrasi user biasa
    public function processRegister()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $modelUser = new M_user();
        // DATA REGISTRASI USER BIASA DISESUAIKAN
        $userData = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama'     => $this->request->getPost('username'), // Default: nama sama dengan username
            'role_id' => 2, // Role User Biasa
            'id_sumberdata' => null // Default: null untuk user biasa
        ];

        $modelUser->insert($userData);

        session()->setFlashdata('pesan_swal', 'Akun berhasil dibuat! Silakan login.');
        return redirect()->to('/login');
    }

    // Logout
    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}