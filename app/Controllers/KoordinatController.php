<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_Wilayah;
use App\Models\M_photo;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KoordinatController extends BaseController
{
    public function import()
    {
        $data['title'] = 'Import Data Koordinat';
        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('koordinat/import', $data) // Kita akan buat view ini
            . view('Template/footer');
    }

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

        try {
            // Inisialisasi Model
            $koordinatModel = new M_koordinat();
            $wilayahModel = new M_Wilayah();
            $sumberDataModel = new M_sumberData();
            $judulKeteranganModel = new M_judulKeterangan();
            $isiKeteranganModel = new M_isiKeterangan();

            // Caching data master
            $sumberDataMap = array_change_key_case(array_column($sumberDataModel->findAll(), 'id_sumberdata', 'nama_sumber'), CASE_LOWER);
            $kotaKabMap = array_change_key_case(array_column($wilayahModel->getKotaKab(), 'id_kotakab', 'nama_kotakab'), CASE_LOWER);
            $kecamatanMap = array_change_key_case(array_column($wilayahModel->getKecamatan(), 'id_kec', 'nama_kec'), CASE_LOWER);
            $kelurahanMap = array_change_key_case(array_column($wilayahModel->getKelurahan(), 'id_kel', 'nama_kel'), CASE_LOWER);
            $judulKeteranganMap = array_change_key_case(array_column($judulKeteranganModel->findAll(), 'id_jdlketerangan', 'jdl_keterangan'), CASE_LOWER);

            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $headerRow = $sheet->getRowIterator(1, 1)->current();

            $header = [];
            foreach ($headerRow->getCellIterator() as $cell) {
                $header[] = strtolower($cell->getValue());
            }

            $staticHeaders = ['latitude', 'longitude', 'sumber data', 'kota/kab', 'kecamatan', 'kelurahan'];
            $dynamicHeaders = array_diff($header, $staticHeaders);

            $importedCount = 0;
            $failedRows = [];

            $koordinatModel->db->transBegin();

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
                $idKotaKab = $kotaKabMap[strtolower($rowData['kota/kab'])] ?? null;
                $idKecamatan = $kecamatanMap[strtolower($rowData['kecamatan'])] ?? null;
                $idKelurahan = $kelurahanMap[strtolower($rowData['kelurahan'])] ?? null;

                if (!$idSumberData || !$idKotaKab || !$idKecamatan || !$idKelurahan) {
                    $failedRows[] = "Baris " . ($index) . ": Data master (Sumber Data/Wilayah) tidak valid atau tidak ditemukan.";
                    continue;
                }

                $koordinatModel->insert([
                    'latitude' => $rowData['latitude'],
                    'longitude' => $rowData['longitude'],
                    'id_sumberdata' => $idSumberData,
                    'id_kotakab' => $idKotaKab,
                    'id_kec' => $idKecamatan,
                    'id_kel' => $idKelurahan
                ]);
                $newKoordinatId = $koordinatModel->getInsertID();

                foreach ($dynamicHeaders as $dynHeader) {
                    $idJdlKeterangan = $judulKeteranganMap[$dynHeader] ?? null;
                    $isiKeterangan = $rowData[$dynHeader] ?? null;

                    if ($idJdlKeterangan && $isiKeterangan !== null && $isiKeterangan !== '') {
                        $isiKeteranganModel->insert([
                            'id_koordinat' => $newKoordinatId,
                            'id_jdlketerangan' => $idJdlKeterangan,
                            'isi_keterangan' => $isiKeterangan
                        ]);
                    }
                }
                $importedCount++;
            }

            if ($koordinatModel->db->transStatus() === false || !empty($failedRows)) {
                $koordinatModel->db->transRollback();
                return redirect()->to('/koordinat/import')->with('error', 'Proses impor dibatalkan karena ada data yang tidak valid.')->with('failed_rows', $failedRows);
            } else {
                $koordinatModel->db->transCommit();
                return redirect()->to('/koordinat/import')->with('success', "Berhasil mengimpor {$importedCount} data.");
            }
        } catch (\Exception $e) {
            return redirect()->to('/koordinat/import')->with('error', 'Terjadi error saat memproses file: ' . $e->getMessage());
        }
    }

    public function uploadPhotos($koordinatId)
    {
        // Pastikan request adalah POST
        if (!$this->request->is('post')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
        }

        $validationRule = [
            'photos' => [
                'label' => 'Gambar',
                'rules' => 'uploaded[photos.0]|max_size[photos,2048]|is_image[photos]',
                'errors' => [
                    'uploaded' => 'Tidak ada gambar yang diunggah.',
                    'max_size' => 'Ukuran gambar maksimal adalah 2MB.',
                    'is_image' => 'File yang diunggah harus berupa gambar.'
                ]
            ],
        ];

        // Jalankan validasi
        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        $files = $this->request->getFiles();
        $photoModel = new M_photo();
        $uploadedCount = 0;

        // Tentukan folder penyimpanan yang dapat diakses publik
        $uploadPath = 'uploads/koordinat_photos/';

        foreach ($files['photos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();

                // Pindahkan file ke folder di dalam 'public'
                $file->move(FCPATH . $uploadPath, $newName);

                // Simpan jalur file yang dapat diakses publik ke database
                $photoModel->insert([
                    'koordinat_id' => $koordinatId,
                    'file_path'    => $uploadPath . $newName,
                ]);

                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            return $this->response->setJSON(['status' => 'success', 'message' => "$uploadedCount foto berhasil diunggah."]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada foto yang berhasil diunggah.']);
        }
    }
}