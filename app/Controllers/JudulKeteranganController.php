<?php

namespace App\Controllers;

use App\Models\M_judulKeterangan;
use App\Models\M_sumberData;
use CodeIgniter\Controller;

class JudulKeteranganController extends Controller
{
    protected $judulKeteranganModel;
    protected $sumberDataModel;
    protected $helpers = ['form'];

    public function __construct()
    {
        $this->judulKeteranganModel = new M_judulKeterangan();
        $this->sumberDataModel = new M_sumberData();
    }

    public function index()
    {
        $allJudulKeterangan = $this->judulKeteranganModel->getJudulKeterangan();

        // Mengelompokkan data berdasarkan 'nama_sumber'
        $groupedData = [];
        foreach ($allJudulKeterangan as $row) {
            $sumber = $row['nama_sumber'];
            if (!isset($groupedData[$sumber])) {
                $groupedData[$sumber] = [];
            }
            $groupedData[$sumber][] = $row;
        }

        $data = [
            'title' => 'Data Judul Keterangan',
            'groupedJudulKeterangan' => $groupedData
        ];

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('Keterangan/judulKeterangan', $data)
            . view('Template/footer');
    }

    public function form($id = null)
    {
        $data = [
            'title'          => 'Form Judul Keterangan',
            'judulKeterangan' => null,
            'sumberdata'     => $this->sumberDataModel->findAll(),
            'validation'     => \Config\Services::validation()
        ];

        if ($id) {
            $judulKeterangan = $this->judulKeteranganModel->find($id);

            if (empty($judulKeterangan)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Judul Keterangan tidak ditemukan.');
            }

            $data['judulKeterangan'] = $judulKeterangan;
            $data['title'] = 'Edit Judul Keterangan';
        }

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('Keterangan/formJudulKeterangan', $data)
            . view('Template/footer');
    }

    public function saveOrUpdate()
    {
        $id = $this->request->getPost('id_jdlketerangan');
        
        $rules = [
            'id_sumberdata'  => 'required',
            'jdl_keterangan' => 'required',
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        $dataToSave = [
            'id_sumberdata'  => $this->request->getPost('id_sumberdata'),
            'jdl_keterangan' => $this->request->getPost('jdl_keterangan')
        ];

        if ($id) {
            $this->judulKeteranganModel->update($id, $dataToSave);
            // Mengganti 'pesan' menjadi 'pesan_swal' untuk konsistensi SweetAlert
            session()->setFlashdata('pesan_swal', 'Data keterangan berhasil diperbarui.');
        } else {
            $this->judulKeteranganModel->save($dataToSave);
            session()->setFlashdata('pesan_swal', 'Data keterangan berhasil ditambahkan.');
        }

        return redirect()->to('/judul-keterangan');
    }

    public function delete($id)
    {
        $this->judulKeteranganModel->delete($id);
        session()->setFlashdata('pesan_swal', 'Data judul keterangan berhasil dihapus.');
        return redirect()->to('/judul-keterangan');
    }
}