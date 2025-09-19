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
            . view('koordinat/import', $data)
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
            $photoModel = new M_photo();

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

            $staticHeaders = ['latitude', 'longitude', 'sumber data', 'kota/kab', 'kecamatan', 'kelurahan', 'nama photo'];
            $dynamicHeaders = array_diff($header, $staticHeaders);

            $importedCount = 0;
            $failedRows = [];
            $importedKoordinatIds = [];

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
                $importedKoordinatIds[] = $newKoordinatId;

                $namaPhotoValue = $rowData['nama photo'] ?? null;
                if (!empty($namaPhotoValue)) {
                    $photoNames = explode(',', $namaPhotoValue);
                    foreach ($photoNames as $photoName) {
                        $trimmedName = trim($photoName);
                        if (!empty($trimmedName)) {
                            // Panggil fungsi sanitasi nama file
                            $sanitizedName = $this->_sanitizeFileName($trimmedName);

                            // Cari entri foto yang sudah ada
                            $existingPhoto = $photoModel->where('nama_photo', $sanitizedName)->first();

                            if ($existingPhoto) {
                                // Jika foto sudah ada, periksa apakah id_koordinatnya kosong
                                if (empty($existingPhoto['id_koordinat'])) {
                                    // Jika id_koordinat kosong, perbarui dengan id_koordinat yang baru
                                    $photoModel->update($existingPhoto['id_photo'], ['id_koordinat' => $newKoordinatId]);
                                }
                                // Jika id_koordinat sudah terisi, biarkan saja (tidak melakukan apa-apa)
                            } else {
                                // Jika foto belum ada, buat entri baru
                                $photoModel->insert([
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
                session()->setFlashdata('imported_count', $importedCount);
                session()->setFlashdata('imported_koordinat_ids', implode(',', $importedKoordinatIds));
                return redirect()->to('/koordinat/import')->with('success', "Berhasil mengimpor {$importedCount} data. Silakan unggah foto terkait.");
            }
        } catch (\Exception $e) {
            if (isset($koordinatModel) && $koordinatModel->db->transStatus() !== false) {
                $koordinatModel->db->transRollback();
            }
            return redirect()->to('/koordinat/import')->with('error', 'Terjadi error saat memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Helper function untuk membersihkan nama file.
     * Mengganti karakter non-alphanumeric (kecuali . dan _) dengan underscore.
     */
    private function _sanitizeFileName(string $filename): string
    {
        $info = pathinfo($filename);
        $name = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        // Mengubah semua karakter non-alphanumeric, non-titik, dan non-underscore menjadi underscore.
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        // Menggabungkan multiple underscores menjadi satu
        $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);

        // Membersihkan underscore di awal atau akhir nama file
        $sanitizedName = trim($sanitizedName, '_');

        return $sanitizedName . $extension;
    }

    public function uploadPhotos()
    {
        $photoModel = new M_photo();
        $uploadedCount = 0;
        $uploadPath = 'uploads/';
        $filesToProcess = [];
        $isZip = false;

        // Periksa apakah request adalah POST dan apakah ada file yang dikirim
        if (!$this->request->is('post')) {
            session()->setFlashdata('error', 'Metode permintaan tidak valid.');
            return redirect()->to('/koordinat/import');
        }

        // Deteksi apakah file yang diunggah adalah ZIP
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
                    // Ekstrak semua file ke folder temporer
                    $zip->extractTo($tempPath);
                    $zip->close();

                    // Ambil daftar file yang sudah diekstrak
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
            // Proses unggahan multi-select jika tidak ada file ZIP
            $files = $this->request->getFiles();
            if (isset($files['photos'])) {
                $filesToProcess = $files['photos'];
            }
        }

        // Jika tidak ada file yang ditemukan, kembalikan error
        if (empty($filesToProcess)) {
            session()->setFlashdata('error', 'Tidak ada foto yang ditemukan untuk diunggah.');
            return redirect()->to('/koordinat/import');
        }

        // Loop melalui setiap file dan memprosesnya
        foreach ($filesToProcess as $file) {
            if ($isZip) {
                // Proses file dari ZIP
                $originalName = basename($file);
                $sanitizedName = $this->_sanitizeFileName($originalName);

                // Pindahkan file dari temp ke folder uploads
                if (rename($file, FCPATH . $uploadPath . $sanitizedName)) {
                    $this->processPhoto($photoModel, $sanitizedName, $uploadPath);
                    $uploadedCount++;
                }
            } else {
                // Proses file dari multi-select
                if ($file->isValid() && !$file->hasMoved()) {
                    $originalName = $file->getName();
                    $sanitizedName = $this->_sanitizeFileName($originalName);

                    // Tentukan jalur file akhir
                    $destinationPath = FCPATH . $uploadPath;

                    if ($file->move($destinationPath, $sanitizedName, true)) {
                        $this->processPhoto($photoModel, $sanitizedName, $uploadPath);
                        $uploadedCount++;
                    }
                }
            }
        }

        // Bersihkan file temporer jika dari ZIP
        if ($isZip && isset($tempPath)) {
            $this->deleteDirectory($tempPath);
        }

        session()->setFlashdata('success', "$uploadedCount foto berhasil diunggah.");
        return redirect()->to('/koordinat/import');
    }

    private function processPhoto($photoModel, $sanitizedName, $uploadPath)
    {
        $existingPhoto = $photoModel->where('nama_photo', $sanitizedName)->first();
        if ($existingPhoto) {
            $photoModel->update($existingPhoto['id_photo'], ['file_path' => $uploadPath . $sanitizedName]);
        } else {
            $photoModel->insert([
                'id_koordinat' => null,
                'nama_photo' => $sanitizedName,
                'file_path' => $uploadPath . $sanitizedName
            ]);
        }
    }

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