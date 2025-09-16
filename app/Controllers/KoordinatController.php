<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_Wilayah;
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
}