<?php

namespace App\Controllers;

use App\Models\M_user;
use App\Models\M_sumberData;
use App\Models\M_role;
use CodeIgniter\Controller;

class UserController extends Controller
{
    protected $userModel;
    protected $sumberDataModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new M_user();
        $this->sumberDataModel = new M_sumberData();
        $this->roleModel = new M_role();
    }

    // Menampilkan daftar semua pengguna
    public function index()
    {
        $data['users'] = $this->userModel->getAllUsersWithRoleAndSource();
        return view('Template/header')
            . view('Template/sidebar')
            . view('users/user_list', $data)
            . view('Template/footer');
    }

    // Menampilkan form untuk menambah pengguna baru
    public function create()
    {
        $data['sumber_data'] = $this->sumberDataModel->findAll();
        $data['roles'] = $this->roleModel->getAssignableRoles(1);
        
        return view('Template/header')
            . view('Template/sidebar')
            . view('users/user_form', $data)
            . view('Template/footer');
    }

    // Memproses data dari form tambah pengguna
    public function save()
    {
        $rules = [
            'nama'             => 'required|min_length[3]|max_length[255]',
            'username'         => 'required|min_length[5]|max_length[20]|is_unique[users.username]',
            'password'         => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'role_id'          => 'required|integer|not_in_list[1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'nama'          => $this->request->getVar('nama'),
            'username'      => $this->request->getVar('username'),
            'password'      => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role_id'       => $this->request->getVar('role_id'),
            'id_sumberdata' => $this->request->getVar('id_sumberdata') ?: null,
        ];
        
        $this->userModel->insert($data);
        // SweetAlert: Flash message untuk success
        return redirect()->to('/users')->with('success', 'Pengguna baru **' . esc($data['username']) . '** berhasil ditambahkan. 🚀');
    }

    // Menampilkan form edit dengan data pengguna yang sudah ada
    public function edit($id)
    {
        $data['user'] = $this->userModel->find($id);
        
        if (empty($data['user'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna dengan ID ' . $id . ' tidak ditemukan.');
        }

        $data['sumber_data'] = $this->sumberDataModel->findAll();
        $data['roles'] = $this->roleModel->getAssignableRoles(1);

        return view('Template/header')
            . view('Template/sidebar')
            . view('users/user_form', $data)
            . view('Template/footer');
    }

    // Memproses data dari form edit
    public function update($id)
    {
        $rules = [
            'nama'             => 'required|min_length[3]|max_length[255]',
            'username'         => "required|min_length[5]|max_length[20]|is_unique[users.username,id,{$id}]",
            'password'         => 'permit_empty|min_length[8]|max_length[255]',
            'password_confirm' => 'matches[password]',
            'role_id'          => 'required|integer|not_in_list[1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'          => $this->request->getVar('nama'),
            'username'      => $this->request->getVar('username'),
            'role_id'       => $this->request->getVar('role_id'),
            'id_sumberdata' => $this->request->getVar('id_sumberdata') ?: null,
        ];

        if ($this->request->getVar('password')) {
            $data['password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        // SweetAlert: Flash message untuk success
        return redirect()->to('/users')->with('success', 'Data pengguna ' . esc($data['username']) . ' berhasil diperbarui.');
    }

    // Menghapus pengguna (Soft Delete)
    public function delete($id)
    {
        $this->userModel->delete($id);
        // SweetAlert: Flash message untuk success
        return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
    }
}