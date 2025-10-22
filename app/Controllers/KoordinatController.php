<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_Wilayah;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_photo; // Tambahkan M_photo
use App\Models\M_notifikasi; // Tambahkan M_notifikasi
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory; // Tambahkan library untuk Import

class KoordinatController extends Controller // Nama kelas diganti menjadi KoordinatController
{
    // Properti Model dan Service
    protected $koordinatModel;
    protected $mWilayah;
    protected $sumberDataModel;
    protected $judulKeteranganModel;
    protected $isiKeteranganModel;
    protected $photoModel;
    protected $notifikasiModel; // Tambahkan model notifikasi
    protected $pager;

    public function __construct()
    {
        // Inisialisasi semua Model yang diperlukan dari kedua Controller lama
        $this->koordinatModel = new M_koordinat();
        $this->mWilayah = new M_Wilayah();
        $this->sumberDataModel = new M_sumberData();
        $this->judulKeteranganModel = new M_judulKeterangan();
        $this->isiKeteranganModel = new M_isiKeterangan();
        $this->photoModel = new M_photo();
        $this->notifikasiModel = new M_notifikasi(); 
        $this->pager = \Config\Services::pager();
    }

    // Fungsi Index (dari MasterDataController)
    public function index()
    {
        $sumberdataId = $this->request->getVar('sumberdata');
        $keyword = $this->request->getVar('keyword');

        // 🔑 KUNCI: Ambil nilai 'per_page' dari URL atau gunakan default 10
        $perPage = $this->request->getVar('per_page') ?? 10;
        // Pastikan nilai perPage adalah angka dan bukan 0
        $perPage = max(1, (int)$perPage);
        
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
            // 🔑 KUNCI: Gunakan $perPage di fungsi paginate()
            'koordinat'          => $koordinatQuery->paginate($perPage, 'default'),
            'pager'              => $this->koordinatModel->pager,
            'sumberdata'         => $this->sumberDataModel->findAll(),
            'judulKeterangan'    => $this->judulKeteranganModel->findAll(),
            'selectedSumberdata' => $sumberdataId,
            'keyword'            => $keyword,
            // 🔑 TAMBAHKAN $perPage ke data
            'perPage'            => $perPage,
        ];

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('koordinat/masterData', $data)
            . view('Template/footer');
    }

    // Fungsi Form (dari MasterDataController)
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

    // Fungsi Save (dari MasterDataController)
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

    // Fungsi Delete (dari MasterDataController)
    public function delete($id)
    {
        // Hard Delete data terkait (Keterangan)
        $this->isiKeteranganModel->where('id_koordinat', $id)->delete();
        // Soft Delete marker utama (Model M_koordinat menangani deleted_by via hook)
        $this->koordinatModel->delete($id); 

        session()->setFlashdata('success', 'Data koordinat berhasil dihapus!');

        return redirect()->to('/koordinat');
    }

    // Fungsi getKecamatanByKotaKab (dari MasterDataController - AJAX helper)
    public function getKecamatanByKotaKab($id_kotakab)
    {
        $kecamatan = $this->mWilayah->getKecamatan(['id_kotakab' => $id_kotakab]);
        return $this->response->setJSON($kecamatan);
    }
    
    // Fungsi getKelurahanByKecamatan (dari MasterDataController - AJAX helper)
    public function getKelurahanByKecamatan($id_kec)
    {
        $kelurahan = $this->mWilayah->getKelurahan(['id_kec' => $id_kec]);
        return $this->response->setJSON($kelurahan);
    }
    
    // Fungsi getJudulKeteranganBySumberData (dari MasterDataController - AJAX helper)
    public function getJudulKeteranganBySumberData($id_sumberdata)
    {
        $judulKeterangan = $this->judulKeteranganModel->where('id_sumberdata', $id_sumberdata)->findAll();
        return $this->response->setJSON($judulKeterangan);
    }

    // Fungsi deleteMultiple (dari MasterDataController)
    public function deleteMultiple()
    {
        $session = \Config\Services::session(); 
        $ids = $this->request->getPost('selected');
    
        if (empty($ids) || !is_array($ids)) {
            $session->setFlashdata('warning', 'Tidak ada data yang dipilih untuk dihapus.');
            return redirect()->to('/koordinat');
        }
    
        $db = \Config\Database::connect();
        $db->transBegin();
    
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
            $now = date('Y-m-d H:i:s');
            $dataSoftDelete = [
                'deleted_at' => $now, 
                'updated_at' => $now, 
                'deleted_by' => $session->get('nama') ?? 'System/Guest' 
            ];
            
            $result = $this->koordinatModel
                            ->builder()
                            ->whereIn('id_koordinat', $ids)
                            ->update($dataSoftDelete);
    
            if ($result === FALSE) { 
                throw new \Exception("Kueri Soft Delete massal gagal dieksekusi di database.");
            }
            
            // --- 4. Cek Status Transaksi dan Commit ---
            if ($db->transStatus() === FALSE) {
                $db->transRollback();
                log_message('error', 'DB STATUS: Transaksi gagal sebelum commit.');
                throw new \Exception("Gagal melakukan commit database. Transaksi dibatalkan.");
            }
            
            $db->transCommit();
            
            $session->setFlashdata('success', count($ids) . ' Data marker berhasil dihapus.');
            
        } catch (\Exception $e) {
            if ($db->transStatus() !== FALSE) {
                $db->transRollback();
            }
            
            log_message('error', 'Multiple Soft Delete failed and rolled back. Detail: ' . $e->getMessage());
            $session->setFlashdata('error', 'Gagal menghapus data. Transaksi dibatalkan. Pesan: ' . $e->getMessage());
        }
    
        return redirect()->to('/koordinat');
    }
    
    // =========================================================
    // FUNGSI IMPORT DATA (dari KoordinatController lama)
    // =========================================================

    // Fungsi Import (dari KoordinatController lama)
    public function import()
    {
        $data['title'] = 'Import Data Koordinat';
        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('koordinat/import', $data)
            . view('Template/footer');
    }

    // Fungsi Upload (dari KoordinatController lama)
    public function upload()
    {
        $file = $this->request->getFile('excel_file');

        $validationRule = [
            'excel_file' => [
                'rules' => 'uploaded[excel_file]|ext_in[excel_file,xlsx]',
                'errors' => [
                    'uploaded' => 'Harap pilih file untuk diunggah.',
                    'ext_in' => 'Hanya file .xlsx yang diizinkan.',
                ],
            ],
        ];
        if (!$this->validate($validationRule)) {
            return redirect()->to('/koordinat/import')->with('error', $this->validator->getErrors()['excel_file']);
        }

        $namaFileAsli = $file->getName();
        $namaFileServer = $file->getRandomName();

        $uploadPath = WRITEPATH . 'uploads/batch_files/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if (!is_writable($uploadPath)) {
            return redirect()->to('/koordinat/import')->with('error', "Error: Folder '{$uploadPath}' tidak dapat ditulis. Periksa izin folder.");
        }

        if (!$file->move($uploadPath, $namaFileServer)) {
            return redirect()->to('/koordinat/import')->with('error', 'Gagal memindahkan file yang diunggah. Periksa konfigurasi server.');
        }
        $pathFilePermanen = $uploadPath . $namaFileServer;

        try {
            // Mapping Data
            $sumberDataMap = array_change_key_case(array_column($this->sumberDataModel->findAll(), 'id_sumberdata', 'nama_sumber'), CASE_LOWER);
            $kotaKabMap = array_change_key_case(array_column($this->mWilayah->getKotaKab(), 'id_kotakab', 'nama_kotakab'), CASE_LOWER);
            $kecamatanMap = array_change_key_case(array_column($this->mWilayah->getKecamatan(), 'id_kec', 'nama_kec'), CASE_LOWER);
            $kelurahanMap = array_change_key_case(array_column($this->mWilayah->getKelurahan(), 'id_kel', 'nama_kel'), CASE_LOWER);
            $judulKeteranganMap = array_change_key_case(array_column($this->judulKeteranganModel->findAll(), 'id_jdlketerangan', 'jdl_keterangan'), CASE_LOWER);

            $spreadsheet = IOFactory::load($pathFilePermanen);
            $sheet = $spreadsheet->getActiveSheet();
            $headerRow = $sheet->getRowIterator(1, 1)->current();

            $header = [];
            foreach ($headerRow->getCellIterator() as $cell) {
                $header[] = strtolower($cell->getValue());
            }

            $staticHeaders = ['latitude', 'longitude', 'sumber data', 'kota/kab', 'kecamatan', 'kelurahan', 'nama photo'];
            $dynamicHeaders = array_diff($header, $staticHeaders);

            $importedCount = 0;
            $failedRows = [];
            $importedKoordinatIds = [];

            $this->koordinatModel->db->transBegin();

            foreach ($sheet->getRowIterator(2) as $index => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);

                $rowData = [];
                $cellIndex = 0;
                foreach ($cellIterator as $cell) {
                    if (isset($header[$cellIndex])) {
                        $rowData[$header[$cellIndex]] = $cell->getFormattedValue();
                    }
                    $cellIndex++;
                }

                if (empty(implode('', $rowData))) {
                    continue;
                }

                $idSumberData = $sumberDataMap[strtolower($rowData['sumber data'])] ?? null;
                
                $idKotaKab = !empty($rowData['kota/kab']) ? ($kotaKabMap[strtolower($rowData['kota/kab'])] ?? null) : null;
                $idKecamatan = !empty($rowData['kecamatan']) ? ($kecamatanMap[strtolower($rowData['kecamatan'])] ?? null) : null;
                $idKelurahan = !empty($rowData['kelurahan']) ? ($kelurahanMap[strtolower($rowData['kelurahan'])] ?? null) : null;

                if (!$idSumberData) {
                    $failedRows[] = "Baris " . ($index) . ": Data Sumber Data tidak valid atau tidak ditemukan.";
                    continue;
                }

                $this->koordinatModel->insert([
                    'latitude' => $rowData['latitude'],
                    'longitude' => $rowData['longitude'],
                    'id_sumberdata' => $idSumberData,
                    'id_kotakab' => $idKotaKab,
                    'id_kec' => $idKecamatan,
                    'id_kel' => $idKelurahan
                ]);
                $newKoordinatId = $this->koordinatModel->getInsertID();
                $importedKoordinatIds[] = $newKoordinatId;

                $namaPhotoValue = $rowData['nama photo'] ?? null;
                if (!empty($namaPhotoValue)) {
                    $photoNames = explode(',', $namaPhotoValue);
                    foreach ($photoNames as $photoName) {
                        $trimmedName = trim($photoName);
                        if (!empty($trimmedName)) {
                            $sanitizedName = $this->_sanitizeFileName($trimmedName);
                            $existingPhoto = $this->photoModel->where('nama_photo', $sanitizedName)->first();

                            if ($existingPhoto) {
                                if (empty($existingPhoto['id_koordinat'])) {
                                    $this->photoModel->update($existingPhoto['id_photo'], ['id_koordinat' => $newKoordinatId]);
                                }
                            } else {
                                $this->photoModel->insert([
                                    'id_koordinat' => $newKoordinatId,
                                    'nama_photo'   => $sanitizedName,
                                    'file_path'    => 'uploads/' . $sanitizedName
                                ]);
                            }
                        }
                    }
                }

                foreach ($dynamicHeaders as $dynHeader) {
                    $idJdlKeterangan = $judulKeteranganMap[$dynHeader] ?? null;
                    $isiKeterangan = $rowData[$dynHeader] ?? null;

                    if ($idJdlKeterangan && $isiKeterangan !== null && $isiKeterangan !== '') {
                        $this->isiKeteranganModel->insert([
                            'id_koordinat' => $newKoordinatId,
                            'id_jdlketerangan' => $idJdlKeterangan,
                            'isi_keterangan' => $isiKeterangan
                        ]);
                    }
                }
                $importedCount++;
            }


            if ($this->koordinatModel->db->transStatus() === false || !empty($failedRows)) {
                $this->koordinatModel->db->transRollback();
                if (file_exists($pathFilePermanen)) {
                    unlink($pathFilePermanen);
                }
                return redirect()->to('/koordinat/import')->with('error', 'Proses impor dibatalkan karena ada data yang tidak valid.')->with('failed_rows', $failedRows);
            } else {
                $notifikasiBerhasil = true;
                if ($importedCount > 0) {
                    $dataNotif = [
                        'pesan'       => "{$importedCount} data baru dari file '{$namaFileAsli}' berhasil diimport.",
                        'nama_file'   => $namaFileAsli,
                        'path_file'   => $pathFilePermanen,
                        'tipe'        => 'batch'
                    ];

                    if (!$this->notifikasiModel->insert($dataNotif)) {
                        $notifikasiBerhasil = false;
                    }
                }

                if ($notifikasiBerhasil) {
                    $this->koordinatModel->db->transCommit();
                    session()->setFlashdata('imported_count', $importedCount);
                    session()->setFlashdata('imported_koordinat_ids', implode(',', $importedKoordinatIds));
                    return redirect()->to('/koordinat/import')->with('success', "Berhasil mengimpor {$importedCount} data. Silakan unggah foto terkait.");
                } else {
                    $this->koordinatModel->db->transRollback();
                    if (file_exists($pathFilePermanen)) {
                        unlink($pathFilePermanen);
                    }
                    $notifError = $this->notifikasiModel->errors() ? implode(', ', $this->notifikasiModel->errors()) : 'Unknown error.';
                    return redirect()->to('/koordinat/import')->with('error', 'GAGAL MEMBUAT NOTIFIKASI: ' . $notifError);
                }
            }
        } catch (\Exception $e) {
            if ($this->koordinatModel->db->transStatus() !== false) {
                $this->koordinatModel->db->transRollback();
            }
            if (isset($pathFilePermanen) && file_exists($pathFilePermanen)) {
                unlink($pathFilePermanen);
            }
            log_message('error', '[Import Error] ' . $e->getMessage());
            return redirect()->to('/koordinat/import')->with('error', 'Terjadi error saat memproses file. Silakan cek log untuk detail.');
        }
    }

    // Fungsi Sanitize File Name (dari KoordinatController lama)
    private function _sanitizeFileName(string $filename): string
    {
        $info = pathinfo($filename);
        $name = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $name); // Ditingkatkan agar mendukung titik untuk ekstensi
        $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);
        $sanitizedName = trim($sanitizedName, '_');
        return $sanitizedName . $extension;
    }

    // Fungsi Upload Photos (dari KoordinatController lama)
    public function uploadPhotos()
    {
        $uploadedCount = 0;
        $uploadPath = 'uploads/';
        $filesToProcess = [];
        $isZip = false;

        if (!$this->request->is('post')) {
            session()->setFlashdata('error', 'Metode permintaan tidak valid.');
            return redirect()->to('/koordinat/import');
        }

        $zipFile = $this->request->getFile('zip_file');
        if ($zipFile && $zipFile->isValid() && !$zipFile->hasMoved()) {
            $isZip = true;
            $tempPath = WRITEPATH . 'temp_zip/';

            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0777, true);
            }

            if ($zipFile->move($tempPath, $zipFile->getName())) {
                $zip = new \ZipArchive;
                if ($zip->open($tempPath . $zipFile->getName()) === TRUE) {
                    $zip->extractTo($tempPath);
                    $zip->close();

                    $filesInZip = scandir($tempPath);
                    foreach ($filesInZip as $file) {
                        if ($file != '.' && $file != '..' && is_file($tempPath . $file) && strtolower($file) !== strtolower($zipFile->getName())) {
                            $filesToProcess[] = $tempPath . $file;
                        }
                    }
                } else {
                    session()->setFlashdata('error', 'Gagal membuka file ZIP.');
                    return redirect()->to('/koordinat/import');
                }
            }
        } else {
            $files = $this->request->getFiles();
            if (isset($files['photos'])) {
                $filesToProcess = $files['photos'];
            }
        }

        if (empty($filesToProcess)) {
            session()->setFlashdata('error', 'Tidak ada foto yang ditemukan untuk diunggah.');
            return redirect()->to('/koordinat/import');
        }

        foreach ($filesToProcess as $file) {
            if ($isZip) {
                $originalName = basename($file);
                $sanitizedName = $this->_sanitizeFileName($originalName);

                if (rename($file, FCPATH . $uploadPath . $sanitizedName)) {
                    $this->processPhoto($sanitizedName, $uploadPath);
                    $uploadedCount++;
                }
            } else {
                if ($file->isValid() && !$file->hasMoved()) {
                    $originalName = $file->getName();
                    $sanitizedName = $this->_sanitizeFileName($originalName);
                    $destinationPath = FCPATH . $uploadPath;

                    if ($file->move($destinationPath, $sanitizedName, true)) {
                        $this->processPhoto($sanitizedName, $uploadPath);
                        $uploadedCount++;
                    }
                }
            }
        }

        if ($isZip && isset($tempPath)) {
            $this->deleteDirectory($tempPath);
        }

        session()->setFlashdata('success', "$uploadedCount foto berhasil diunggah.");
        return redirect()->to('/koordinat/import');
    }

    // Fungsi Process Photo (dari KoordinatController lama)
    private function processPhoto($sanitizedName, $uploadPath)
    {
        $existingPhoto = $this->photoModel->where('nama_photo', $sanitizedName)->first();
        if ($existingPhoto) {
            $this->photoModel->update($existingPhoto['id_photo'], ['file_path' => $uploadPath . $sanitizedName]);
        } else {
            $this->photoModel->insert([
                'id_koordinat' => null,
                'nama_photo' => $sanitizedName,
                'file_path' => $uploadPath . $sanitizedName
            ]);
        }
    }

    // Fungsi Delete Directory (dari KoordinatController lama)
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}