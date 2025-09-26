<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_Wilayah;
use App\Models\M_photo;
use App\Models\M_notifikasi;
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
            $koordinatModel = new M_koordinat();
            $wilayahModel = new M_Wilayah();
            $sumberDataModel = new M_sumberData();
            $judulKeteranganModel = new M_judulKeterangan();
            $isiKeteranganModel = new M_isiKeterangan();
            $photoModel = new M_photo();
            $notifikasiModel = new M_notifikasi();

            $sumberDataMap = array_change_key_case(array_column($sumberDataModel->findAll(), 'id_sumberdata', 'nama_sumber'), CASE_LOWER);
            $kotaKabMap = array_change_key_case(array_column($wilayahModel->getKotaKab(), 'id_kotakab', 'nama_kotakab'), CASE_LOWER);
            $kecamatanMap = array_change_key_case(array_column($wilayahModel->getKecamatan(), 'id_kec', 'nama_kec'), CASE_LOWER);
            $kelurahanMap = array_change_key_case(array_column($wilayahModel->getKelurahan(), 'id_kel', 'nama_kel'), CASE_LOWER);
            $judulKeteranganMap = array_change_key_case(array_column($judulKeteranganModel->findAll(), 'id_jdlketerangan', 'jdl_keterangan'), CASE_LOWER);

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
                            $sanitizedName = $this->_sanitizeFileName($trimmedName);
                            $existingPhoto = $photoModel->where('nama_photo', $sanitizedName)->first();

                            if ($existingPhoto) {
                                if (empty($existingPhoto['id_koordinat'])) {
                                    $photoModel->update($existingPhoto['id_photo'], ['id_koordinat' => $newKoordinatId]);
                                }
                            } else {
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
                if (file_exists($pathFilePermanen)) {
                    unlink($pathFilePermanen);
                }
                return redirect()->to('/koordinat/import')->with('error', 'Proses impor dibatalkan karena ada data yang tidak valid.')->with('failed_rows', $failedRows);
            } else {
                $notifikasiBerhasil = true; // Asumsikan berhasil
                if ($importedCount > 0) {
                    $dataNotif = [
                        'pesan'       => "{$importedCount} data baru dari file '{$namaFileAsli}' berhasil diimport.",
                        'nama_file'   => $namaFileAsli,
                        'path_file'   => $pathFilePermanen,
                        'tipe'        => 'batch'
                    ];

                    if (!$notifikasiModel->insert($dataNotif)) {
                        $notifikasiBerhasil = false;
                    }
                }

                if ($notifikasiBerhasil) {
                    $koordinatModel->db->transCommit();
                    session()->setFlashdata('imported_count', $importedCount);
                    session()->setFlashdata('imported_koordinat_ids', implode(',', $importedKoordinatIds));
                    return redirect()->to('/koordinat/import')->with('success', "Berhasil mengimpor {$importedCount} data. Silakan unggah foto terkait.");
                } else {
                    $koordinatModel->db->transRollback();
                    if (file_exists($pathFilePermanen)) {
                        unlink($pathFilePermanen);
                    }
                    $notifError = $notifikasiModel->errors() ? implode(', ', $notifikasiModel->errors()) : 'Unknown error.';
                    return redirect()->to('/koordinat/import')->with('error', 'GAGAL MEMBUAT NOTIFIKASI: ' . $notifError);
                }
            }
        } catch (\Exception $e) {
            if (isset($koordinatModel) && $koordinatModel->db->transStatus() !== false) {
                $koordinatModel->db->transRollback();
            }
            if (isset($pathFilePermanen) && file_exists($pathFilePermanen)) {
                unlink($pathFilePermanen);
            }
            log_message('error', '[Import Error] ' . $e->getMessage());
            return redirect()->to('/koordinat/import')->with('error', 'Terjadi error saat memproses file. Silakan cek log untuk detail.');
        }
    }

    private function _sanitizeFileName(string $filename): string
    {
        $info = pathinfo($filename);
        $name = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);
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
                    $this->processPhoto($photoModel, $sanitizedName, $uploadPath);
                    $uploadedCount++;
                }
            } else {
                if ($file->isValid() && !$file->hasMoved()) {
                    $originalName = $file->getName();
                    $sanitizedName = $this->_sanitizeFileName($originalName);
                    $destinationPath = FCPATH . $uploadPath;

                    if ($file->move($destinationPath, $sanitizedName, true)) {
                        $this->processPhoto($photoModel, $sanitizedName, $uploadPath);
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
