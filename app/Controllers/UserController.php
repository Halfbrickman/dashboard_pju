<?php

namespace App\Controllers;

use App\Models\M_user;
use CodeIgniter\Controller;

class UserController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new M_user();
    }

    // Menampilkan daftar semua pengguna
    public function index()
    {
        $data['users'] = $this->userModel->getAllUsersWithRole();
        return view('users/user_list', $data);
    }

    // Menampilkan form untuk menambah pengguna baru
    public function create()
    {
        // Tidak perlu mengirim data apa pun, karena form-nya kosong
        return view('users/user_form');
    }

    // Memproses data dari form tambah pengguna
    public function save()
    {
        $rules = [
            'username' => 'required|min_length[5]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'role_id'  => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role_id'  => $this->request->getVar('role_id')
        ];
        
        $this->userModel->insert($data);
        return redirect()->to('/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    // Menampilkan form edit dengan data pengguna yang sudah ada
    public function edit($id)
    {
        $data['user'] = $this->userModel->find($id);
        
        if (empty($data['user'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna dengan ID ' . $id . ' tidak ditemukan.');
        }

        // Mengirimkan data pengguna ke form yang sama
        return view('users/user_form', $data);
    }

    // Memproses data dari form edit
    public function update($id)
    {
        $rules = [
            'username' => "required|min_length[5]|max_length[20]|is_unique[users.username,id,{$id}]",
            'password' => 'permit_empty|min_length[8]|max_length[255]',
            'password_confirm' => 'matches[password]',
            'role_id'  => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getVar('username'),
            'role_id'  => $this->request->getVar('role_id')
        ];

        if ($this->request->getVar('password')) {
            $data['password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        return redirect()->to('/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    // Menghapus pengguna
    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
    }
}