<?php

namespace App\Controllers;

use App\Models\M_sumberData;
use CodeIgniter\Controller; // Pastikan menggunakan use CodeIgniter\Controller; jika extend BaseController

class sumberDataController extends Controller // Mengganti BaseController ke Controller jika BaseController Anda tidak didefinisikan
{
    protected $sumberDataModel;

    public function __construct()
    {
        $this->sumberDataModel = new M_sumberData();
    }

    public function index()
    {
        // findAll() secara otomatis hanya mengambil data yang BELUM di soft delete
        $data = [
            'title'        => 'Master Sumber Data',
            'sumber_data'  => $this->sumberDataModel->findAll()
        ];
        
        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('sumberData/sumberData', $data)
            . view('Template/footer');
    }
    
    public function form($id = null)
    {
        $data = [
            'title' => 'Form Sumber Data',
            'sumber' => null // Default value
        ];

        // Jika ada ID, ini adalah mode edit
        if ($id) {
            $data['sumber'] = $this->sumberDataModel->find($id);
            $data['title'] = 'Edit Sumber Data';
        }
        
        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('sumberData/formSumberData', $data)
            . view('Template/footer');
    }

    public function saveOrUpdate()
    {
        $rules = [
            'nama_sumber'   => 'required|min_length[3]',
            'warna'         => 'required|min_length[3]'
        ];

        if ($this->validate($rules)) {
            $id = $this->request->getPost('id_sumberdata');
            $dataToSave = [
                'nama_sumber' => $this->request->getPost('nama_sumber'),
                'warna' => $this->request->getPost('warna')
            ];

            if ($id) {
                // update() akan otomatis mengisi updated_at
                $this->sumberDataModel->update($id, $dataToSave);
                session()->setFlashdata('pesan', 'Data sumber berhasil diubah.');
            } else {
                // save() akan otomatis mengisi created_at dan updated_at
                $this->sumberDataModel->save($dataToSave);
                session()->setFlashdata('pesan', 'Data sumber berhasil ditambahkan.');
            }

            return redirect()->to('/sumberdata');
        } else {
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        // delete() akan otomatis melakukan SOFT DELETE (mengisi kolom deleted_at)
        $this->sumberDataModel->delete($id);
        session()->setFlashdata('pesan', 'Data sumber berhasil dihapus (soft delete).');
        return redirect()->to('/sumberdata');
    }
}