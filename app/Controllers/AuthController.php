<?php

namespace App\Controllers;

use App\Models\M_user;

class AuthController extends BaseController
{
    // Tampilkan halaman login
    public function login()
    {
        // Panggil helper form jika diperlukan
        helper(['form']); 
        return view('auth/login');
    }

    // Proses login pengguna
    public function processLogin()
    {
        $session = session();
        $userModel = new M_user();

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        // Cari user berdasarkan username dan bersihkan input
        $user = $userModel->where('username', trim($username))->first();

        if ($user) {
            $hashedPassword = $user['password'];

            // Verifikasi password dengan hash yang tersimpan di DB
            if (password_verify($password, $hashedPassword)) {
                
                // DATA SESSION
                $ses_data = [
                    'id'            => $user['id'],
                    'username'      => $user['username'],
                    'nama'          => $user['nama'] ?? $user['username'], // Gunakan 'nama' jika tersedia
                    'role_id'       => $user['role_id'],
                    'id_sumberdata' => $user['id_sumberdata'], 
                    'isLoggedIn'    => true
                ];
                $session->set($ses_data);

                return redirect()->to('/dashboard');
            } else {
                // Password salah
                log_message('debug', 'AUTH DEBUG - Verifikasi password GAGAL.');
                $session->setFlashdata('msg', 'Username atau password salah.');
                return redirect()->to('/login');
            }
        } else {
            // Username tidak ditemukan
            log_message('debug', 'AUTH DEBUG - Username TIDAK DITEMUKAN.');
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
            // DATA REGISTRASI ADMIN: Lakukan HASHING di Controller
            $data = [
                'username' => $this->request->getVar('username'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT), // <-- HASHING DI SINI
                'nama'     => $this->request->getVar('username'), 
                'role_id'  => 1, // Role Admin
                'id_sumberdata' => null 
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
        helper(['form']); 
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
        // DATA REGISTRASI USER BIASA: Lakukan HASHING di Controller
        $userData = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT), // <-- HASHING DI SINI
            'nama'     => $this->request->getPost('username'), 
            'role_id' => 2, // Role User Biasa
            'id_sumberdata' => null 
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
