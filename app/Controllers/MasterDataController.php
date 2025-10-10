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
    protected $photoModel;

    public function __construct()
    {
        $this->koordinatModel = new M_koordinat();
        $this->mWilayah = new M_Wilayah();
        $this->sumberDataModel = new M_sumberData();
        $this->judulKeteranganModel = new M_judulKeterangan();
        $this->isiKeteranganModel = new M_isiKeterangan();
        $this->photoModel = new \App\Models\M_photo();
        $this->pager = \Config\Services::pager();
    }

    public function index()
    {
        $sumberdataId = $this->request->getVar('sumberdata');
        $keyword = $this->request->getVar('keyword');
        
        // Panggil kueri dasar (termasuk JOIN) dari Model
        $koordinatQuery = $this->koordinatModel->getDataKoordinatQuery();

        if ($sumberdataId) {
            $koordinatQuery->where('koordinat.id_sumberdata', $sumberdataId);
        }

        if ($keyword) {
            $koordinatQuery ->groupStart()
                            ->orLike('koordinat.latitude', $keyword)
                            ->orLike('koordinat.longitude', $keyword)
                            ->orLike('kota_kab.nama_kotakab', $keyword)
                            ->orLike('kecamatan.nama_kec', $keyword)
                            ->orLike('kelurahan.nama_kel', $keyword)
                            ->orLike('sumber_data.nama_sumber', $keyword)
                            ->groupEnd();
        }

        $data = [
            'title'              => 'Data Koordinat',
            'koordinat'          => $koordinatQuery->paginate(10, 'default'),
            'pager'              => $this->koordinatModel->pager,
            'sumberdata'         => $this->sumberDataModel->findAll(),
            'judulKeterangan'    => $this->judulKeteranganModel->findAll(),
            'selectedSumberdata' => $sumberdataId,
            'keyword'            => $keyword,
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
        // Hard Delete data terkait (Keterangan)
        $this->isiKeteranganModel->where('id_koordinat', $id)->delete();
        // Soft Delete marker utama (Model M_koordinat menangani deleted_by via hook)
        $this->koordinatModel->delete($id); 

        session()->setFlashdata('success', 'Data koordinat berhasil dihapus!');

        return redirect()->to('/koordinat');
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

    public function deleteMultiple()
    {
        $session = \Config\Services::session(); 
    
        $ids = $this->request->getPost('selected');
    
        if (empty($ids) || !is_array($ids)) {
            $session->setFlashdata('warning', 'Tidak ada data yang dipilih untuk dihapus.');
            return redirect()->to('/koordinat');
        }
    
        $db = \Config\Database::connect();
        $db->transBegin(); // START TRANSACTION
    
        try {
            // --- 1. Hapus File Fisik Foto dan Data Foto (Hard Delete) ---
            $photos = $this->photoModel->whereIn('id_koordinat', $ids)->findAll();
            
            if (!empty($photos)) {
                foreach ($photos as $photo) {
                    $file_path = FCPATH . $photo['file_path']; 
                    if (file_exists($file_path)) {
                        @unlink($file_path); 
                    }
                }
                // Hard Delete data foto
                if (!$this->photoModel->whereIn('id_koordinat', $ids)->delete()) {
                    throw new \Exception("Gagal menghapus data foto terkait.");
                }
            }
    
            // --- 2. Hapus data keterangan terkait (Hard Delete) ---
            if (!$this->isiKeteranganModel->whereIn('id_koordinat', $ids)->delete()) {
                throw new \Exception("Gagal menghapus data keterangan terkait.");
            }
            
            // --- 3. Soft Delete Marker (Data Koordinat) ---
            // TENTUKAN DATA UNTUK SOFT DELETE
            $now = date('Y-m-d H:i:s');
            $dataSoftDelete = [
                // Harus diisi manual karena mass update melewati hook
                'deleted_at' => $now, 
                // Updated at harus diisi untuk memicu logika CI
                'updated_at' => $now, 
                'deleted_by' => $session->get('nama') ?? 'System/Guest' 
            ];
            
            // Lakukan soft delete massal menggunakan Query Builder
            // Memanggil builder() memastikan kita menggunakan Query Builder murni, bukan Model update hook yang kompleks.
            $result = $this->koordinatModel
                            ->builder()
                            ->whereIn('id_koordinat', $ids)
                            ->update($dataSoftDelete);
    
            if ($result === FALSE) { 
                // Jika kueri gagal dieksekusi, lempar exception
                throw new \Exception("Kueri Soft Delete massal gagal dieksekusi di database.");
            }
            
            // --- 4. Cek Status Transaksi dan Commit ---
            if ($db->transStatus() === FALSE) {
                $db->transRollback();
                log_message('error', 'DB STATUS: Transaksi gagal sebelum commit.');
                throw new \Exception("Gagal melakukan commit database. Transaksi dibatalkan.");
            }
            
            $db->transCommit(); // COMMIT: Semua berhasil.
            
            $session->setFlashdata('success', count($ids) . ' Data marker berhasil dihapus.');
            
        } catch (\Exception $e) {
            if ($db->transStatus() !== FALSE) {
                $db->transRollback(); // ROLLBACK: Ada yang gagal.
            }
            
            log_message('error', 'Multiple Soft Delete failed and rolled back. Detail: ' . $e->getMessage());
            $session->setFlashdata('error', 'Gagal menghapus data. Transaksi dibatalkan. Pesan: ' . $e->getMessage());
        }
    
        return redirect()->to('/koordinat');
    }
}
