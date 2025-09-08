<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_Wilayah;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use CodeIgniter\Controller;

class MasterDataController extends Controller
{
    protected $koordinatModel;
    protected $mWilayah;
    protected $sumberDataModel;
    protected $judulKeteranganModel;
    protected $isiKeteranganModel;
    protected $pager;

    public function __construct()
    {
        $this->koordinatModel = new M_koordinat();
        $this->mWilayah = new M_Wilayah();
        $this->sumberDataModel = new M_sumberData();
        $this->judulKeteranganModel = new M_judulKeterangan();
        $this->isiKeteranganModel = new M_isiKeterangan();
        $this->pager = \Config\Services::pager();
    }

    public function index()
    {
        $sumberdataId = $this->request->getVar('sumberdata');
        
        $koordinatQuery = $this->koordinatModel->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, kota_kab.nama_kotakab')
                                             ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                                             ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                                             ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                                             ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left');

        if ($sumberdataId) {
            $koordinatQuery->where('koordinat.id_sumberdata', $sumberdataId);
        }

        $data = [
            'title'              => 'Data Koordinat',
            'koordinat'          => $koordinatQuery->paginate(10, 'default'),
            'pager'              => $this->koordinatModel->pager,
            'sumberdata'         => $this->sumberDataModel->findAll(),
            'judulKeterangan'    => $this->judulKeteranganModel->findAll(),
            'selectedSumberdata' => $sumberdataId
        ];

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('koordinat/masterData', $data)
            . view('Template/footer');
    }

    public function form($id = null)
    {
        $data = [
            'title'      => 'Tambah Data Koordinat',
            'koordinat'  => null,
            'kotakab'    => $this->mWilayah->getKotaKab(),
            'kecamatan'  => [],
            'kelurahan'  => [],
            'sumberdata' => $this->sumberDataModel->findAll(),
            'isiKeterangan' => [],
            'validation' => \Config\Services::validation()
        ];
        
        if ($id) {
            $koordinat = $this->koordinatModel->find($id);
            if (empty($koordinat)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Koordinat tidak ditemukan.');
            }

            $data['title'] = 'Edit Data Koordinat';
            $data['koordinat'] = $koordinat;
            $data['kecamatan'] = $this->mWilayah->getKecamatan(['id_kotakab' => $koordinat['id_kotakab']]);
            $data['kelurahan'] = $this->mWilayah->getKelurahan(['id_kec' => $koordinat['id_kec']]);
            $data['isiKeterangan'] = $this->isiKeteranganModel->where('id_koordinat', $id)->findAll();
        }

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('koordinat/formMasterData', $data)
            . view('Template/footer');
    }

    public function save()
    {
        $id_koordinat = $this->request->getPost('id_koordinat');
        
        $rules = [
            
            'id_sumberdata' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataKoordinat = [
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'id_kotakab' => $this->request->getPost('id_kotakab'),
            'id_kec' => $this->request->getPost('id_kec'),
            'id_kel' => $this->request->getPost('id_kel'),
            'id_sumberdata' => $this->request->getPost('id_sumberdata'),
        ];
        
        if ($id_koordinat) {
            // Update mode
            $this->koordinatModel->update($id_koordinat, $dataKoordinat);
            // Hapus keterangan lama sebelum menyimpan yang baru
            $this->isiKeteranganModel->where('id_koordinat', $id_koordinat)->delete();
            $message = 'Data koordinat dan keterangannya berhasil diperbarui.';
        } else {
            // Create mode
            $this->koordinatModel->insert($dataKoordinat);
            $id_koordinat = $this->koordinatModel->getInsertID();
            $message = 'Data koordinat dan keterangannya berhasil ditambahkan.';
        }

        // Proses dan simpan data keterangan dinamis
        $keteranganInputs = $this->request->getPost('keterangan');
        if ($keteranganInputs && is_array($keteranganInputs)) {
            $batchData = [];
            foreach ($keteranganInputs as $id_jdlketerangan => $isi_keterangan) {
                if (!empty($isi_keterangan)) {
                    $batchData[] = [
                        'id_jdlketerangan' => $id_jdlketerangan,
                        'isi_keterangan' => $isi_keterangan,
                        'id_koordinat' => $id_koordinat,
                    ];
                }
            }
            if (!empty($batchData)) {
                $this->isiKeteranganModel->insertBatch($batchData);
            }
        }

        return redirect()->to('/koordinat')->with('success', $message);
    }
    
    public function delete($id)
    {
        // Pastikan data keterangan ikut terhapus
        $this->isiKeteranganModel->where('id_koordinat', $id)->delete();
        $this->koordinatModel->delete($id);
        
        // Ubah respons menjadi JSON agar bisa ditangani oleh fetch API
        return $this->response->setJSON(['status' => 'success', 'message' => 'Data koordinat berhasil dihapus.']);
    }
        
    public function getKecamatanByKotaKab($id_kotakab)
    {
        $kecamatan = $this->mWilayah->getKecamatan(['id_kotakab' => $id_kotakab]);
        return $this->response->setJSON($kecamatan);
    }
    
    public function getKelurahanByKecamatan($id_kec)
    {
        $kelurahan = $this->mWilayah->getKelurahan(['id_kec' => $id_kec]);
        return $this->response->setJSON($kelurahan);
    }
    
    public function getJudulKeteranganBySumberData($id_sumberdata)
    {
        $judulKeterangan = $this->judulKeteranganModel->where('id_sumberdata', $id_sumberdata)->findAll();
        return $this->response->setJSON($judulKeterangan);
    }
}