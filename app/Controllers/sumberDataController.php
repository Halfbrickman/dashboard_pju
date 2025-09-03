<?php

namespace App\Controllers;

use App\Models\M_sumberData;

class sumberDataController extends BaseController
{
    protected $sumberDataModel;

    public function __construct()
    {
        $this->sumberDataModel = new M_sumberData();
    }

    public function index()
    {
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
        // PENTING: Lakukan validasi data di sini
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
                $this->sumberDataModel->update($id, $dataToSave);
                session()->setFlashdata('pesan', 'Data sumber berhasil diubah.');
            } else {
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
        $this->sumberDataModel->delete($id);
        session()->setFlashdata('pesan', 'Data sumber berhasil dihapus.');
        return redirect()->to('/sumberdata');
    }
}