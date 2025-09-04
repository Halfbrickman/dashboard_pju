<?php

namespace App\Controllers;

use App\Models\M_user;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

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
                $ses_data = [
                    'id'         => $user['id'],
                    'username'   => $user['username'],
                    'role_id'    => $user['role_id'],
                    'isLoggedIn' => true
                ];
                $session->set($ses_data);

                // Arahkan semua pengguna (admin & user biasa) ke dashboard yang sama
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

    public function registerAdmin()
    {
        // Pastikan halaman ini hanya bisa diakses dalam mode development
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/');
        }

        return view('auth/register_admin');
    }

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
            $data = [
                'username' => $this->request->getVar('username'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
                'role_id'  => 1 // role_id 1 diasumsikan untuk admin
            ];
            
            $userModel->insert($data);
            
            $session->setFlashdata('msg', 'Akun admin berhasil dibuat. Silakan login.');
            return redirect()->to('/login');

        } else {
            // Jika validasi gagal, kembali ke halaman register dengan pesan error
            $session->setFlashdata('msg', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
    }

    // Register User
    public function register()
    {
        // Tampilkan form registrasi
        return view('auth/register');
    }

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

        $modelUser = new \App\Models\M_user(); // Ganti dengan nama model user Anda
        $userData = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id' => 2 // Default role untuk user biasa (misal: 2)
        ];

        $modelUser->insert($userData);

        // Redirect ke halaman login dengan pesan sukses
        session()->setFlashdata('pesan_swal', 'Akun berhasil dibuat! Silakan login.');
        return redirect()->to('/login');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}