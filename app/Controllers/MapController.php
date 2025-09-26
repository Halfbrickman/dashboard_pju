<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_Wilayah;
use App\Models\M_photo;
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MapController extends BaseController
{
    use ResponseTrait;

    protected $koordinatModel;
    protected $sumberDataModel;
    protected $wilayahModel;
    protected $judulKeteranganModel;
    protected $isiKeteranganModel;
    protected $photoModel;

    public function __construct()
    {
        // Memuat helper
        helper('form');

        // Menginisialisasi model-model yang digunakan
        $this->koordinatModel = new M_koordinat();
        $this->sumberDataModel = new M_sumberData();
        $this->wilayahModel = new M_Wilayah();
        $this->judulKeteranganModel = new M_judulKeterangan();
        $this->isiKeteranganModel = new M_isiKeterangan();
        $this->photoModel = new M_photo();
    }

    public function index()
    {
        // Ambil data dari model dan siapkan untuk view
        $data = [
            'sumber_data' => $this->sumberDataModel->findAll(),
            'kotakab' => $this->wilayahModel->getKotaKab(),
            'kecamatan' => $this->wilayahModel->getKecamatan(),
            'kelurahan' => $this->wilayahModel->getKelurahan(),
            'judul_keterangan' => $this->judulKeteranganModel->findAll(),
            'title' => 'Peta Data'
        ];

        // Kirim data ke view
        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('maps/maps', $data)
            . view('Template/footer');
    }

    public function getMarkerData()
    {
        $request = \Config\Services::request();
        
        // --- AWAL LOGIKA MULTIPLE FILTER ---
        // 1. Ambil nilai sumber_data_id dari URL (contoh: "1,5,10")
        $sumber_ids_raw = $request->getGet('sumber_data_ids');
        // 2. Ubah string menjadi array numerik: [1, 5, 10]
        $sumber_ids = $sumber_ids_raw ? array_map('intval', explode(',', $sumber_ids_raw)) : null; 
        // --- AKHIR LOGIKA MULTIPLE FILTER ---

        $koordinat_id = $request->getGet('id_koordinat');
        $id_kotakab = $request->getGet('id_kotakab');
        $id_kec = $request->getGet('id_kec');
        $id_kel = $request->getGet('id_kel');

        $photoModel = new \App\Models\M_photo();

        if ($koordinat_id) {
            // Logika untuk ID tunggal (detail marker)
            $dataKoordinat = $this->koordinatModel->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_koordinat', $koordinat_id)
                ->findAll();
        } else {
            // Panggil Model dengan array $sumber_ids yang baru
            // Model (M_koordinat) akan menggunakan whereIn() di dalamnya.
            $dataKoordinat = $this->koordinatModel->getFilteredMarkers($sumber_ids, $id_kotakab, $id_kec, $id_kel);
        }

        if (empty($dataKoordinat)) {
            return $this->response->setJSON([]);
        }

        $koordinatIds = array_column($dataKoordinat, 'id_koordinat');

        // Pastikan array ID tidak kosong sebelum melakukan query keterangan
        $allKeterangan = [];
        if (!empty($koordinatIds)) {
            $allKeterangan = $this->isiKeteranganModel
                ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan, isi_keterangan.id_koordinat')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->whereIn('isi_keterangan.id_koordinat', $koordinatIds)
                ->findAll();
        }

        // Pastikan array ID tidak kosong sebelum melakukan query foto
        $allPhotos = [];
        if (!empty($koordinatIds)) {
            $allPhotos = $photoModel->whereIn('id_koordinat', $koordinatIds)->findAll();
        }

        $keteranganGrouped = [];
        foreach ($allKeterangan as $keterangan) {
            $keteranganGrouped[$keterangan['id_koordinat']][] = [
                'isi_keterangan' => $keterangan['isi_keterangan'],
                'jdl_keterangan' => $keterangan['jdl_keterangan']
            ];
        }

        $photosGrouped = [];
        foreach ($allPhotos as $photo) {
            $photosGrouped[$photo['id_koordinat']][] = $photo;
        }

        foreach ($dataKoordinat as &$koordinat) {
            $koordinat['keterangan'] = $keteranganGrouped[$koordinat['id_koordinat']] ?? [];
            $koordinat['photos'] = $photosGrouped[$koordinat['id_koordinat']] ?? [];
        }

        return $this->response->setJSON($dataKoordinat);
    }

    public function deletePhoto($id_photo)
    {
        $request = \Config\Services::request();
        // Pastikan permintaan datang dari AJAX
        if (!$request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $photoModel = new \App\Models\M_photo();
        $photo = $photoModel->find($id_photo);

        if (!$photo) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Foto tidak ditemukan.']);
        }

        // Dapatkan path file dari database
        $file_path = FCPATH . $photo['file_path'];

        // Hapus file fisik dari server jika ada
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Hapus data foto dari database
        if ($photoModel->delete($id_photo)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Foto berhasil dihapus.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data foto dari database.']);
        }
    }

    public function getKecamatan()
    {
        $idKotakab = $this->request->getGet('id_kotakab');
        if ($idKotakab) {
            $kecamatan = $this->wilayahModel->getKecamatanByKotakabId($idKotakab);
            return $this->respond($kecamatan);
        }
        return $this->fail('Parameter id_kotakab tidak ditemukan', 400);
    }

    public function getKelurahan()
    {
        $idKec = $this->request->getGet('id_kec');
        if ($idKec) {
            $kelurahan = $this->wilayahModel->getKelurahanByKecId($idKec);
            return $this->respond($kelurahan);
        }
        return $this->fail('Parameter id_kecamatan tidak ditemukan', 400);
    }

    public function updateMarker()
    {
        $request = \Config\Services::request();
        if (!$request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $id = $request->getPost('id_koordinat');
        $data_koordinat = [
            'id_sumberdata' => $request->getPost('id_sumberdata'),
            'id_kotakab'    => $request->getPost('id_kotakab'),
            'id_kec'        => $request->getPost('id_kec'),
            'id_kel'        => $request->getPost('id_kel'),
            'latitude'      => $request->getPost('latitude'),
            'longitude'     => $request->getPost('longitude'),
        ];

        $keteranganData = json_decode($request->getPost('keterangan'), true);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Update tabel koordinat
            $this->koordinatModel->update($id, $data_koordinat);

            // 2. Update atau tambah keterangan
            $keteranganModel = new \App\Models\M_isiKeterangan();
            $keteranganModel->where('id_koordinat', $id)->delete();
            if ($keteranganData) {
                foreach ($keteranganData as $id_jdlketerangan => $isi_keterangan) {
                    if (!empty($isi_keterangan)) {
                        $data_keterangan = [
                            'id_koordinat' => $id,
                            'id_jdlketerangan' => $id_jdlketerangan,
                            'isi_keterangan' => $isi_keterangan
                        ];
                        $keteranganModel->insert($data_keterangan);
                    }
                }
            }

            // 3. Unggah dan simpan foto baru
            $photosNew = $this->request->getFiles('photos_new');
            $upload_dir = 'uploads/';
            $photoModel = new \App\Models\M_photo();

            if ($photosNew && isset($photosNew['photos_new'])) {
                foreach ($photosNew['photos_new'] as $photo) {
                    // Aturan validasi yang diperbarui
                    $allowedMimeTypes = ['image/jpeg', 'image/png'];
                    $fileMimeType = $photo->getClientMimeType();

                    if ($photo->isValid() && !$photo->hasMoved() && in_array($fileMimeType, $allowedMimeTypes) && $photo->getSizeByUnit('mb') <= 5) {
                        $originalName = $photo->getName();
                        $cleanedName = preg_replace('/[^A-Za-z0-9_.]/', '_', $originalName);
                        $newName = str_replace(' ', '_', $cleanedName);

                        $photo->move(FCPATH . $upload_dir, $newName);

                        $dataPhoto = [
                            'id_koordinat' => $id,
                            'file_path' => $upload_dir . $newName,
                            'nama_photo' => $newName, // Simpan nama file yang telah diubah
                            'file_type' => $fileMimeType,
                            'file_size' => $photo->getSizeByUnit('kb')
                        ];
                        $photoModel->insert($dataPhoto);
                    } else {
                        // Rollback transaksi jika ada file yang gagal validasi
                        $db->transRollback();
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Pastikan file adalah PNG atau JPG dan tidak lebih dari 5MB.'
                        ]);
                    }
                }
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Update failed: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function saveMarker()
    {
        $request = \Config\Services::request();

        // 1. Ambil semua data marker dari POST request
        $data_koordinat = [
            'id_sumberdata' => $request->getPost('id_sumberdata'),
            'id_kotakab' => $request->getPost('id_kotakab'),
            'id_kec' => $request->getPost('id_kec'),
            'id_kel' => $request->getPost('id_kel'),
            'latitude' => $request->getPost('latitude'),
            'longitude' => $request->getPost('longitude'),
        ];

        // Validasi data
        if (empty($data_koordinat['latitude']) || empty($data_koordinat['longitude'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data koordinat tidak lengkap.']);
        }

        // Mulai transaksi untuk memastikan semua operasi berhasil
        $this->koordinatModel->db->transBegin();

        // 2. Simpan data marker ke tabel 'koordinat' dan dapatkan ID-nya
        $success = $this->koordinatModel->insert($data_koordinat);
        $koordinat_id = $this->koordinatModel->getInsertID();

        if ($success) {
            // 3. Simpan data keterangan jika ada
            $keteranganData = $request->getPost('keterangan');
            if ($keteranganData) {
                foreach ($keteranganData as $idJdlKeterangan => $isiKeterangan) {
                    if (!empty($isiKeterangan)) {
                        $dataKeterangan = [
                            'id_koordinat' => $koordinat_id,
                            'id_jdlketerangan' => $idJdlKeterangan,
                            'isi_keterangan' => $isiKeterangan,
                        ];
                        $this->isiKeteranganModel->insert($dataKeterangan);
                    }
                }
            }

            // 4. Unggah dan simpan foto jika ada
            $photos = $request->getFileMultiple('photos');
            if ($photos) {
                foreach ($photos as $photo) {
                    // Aturan validasi yang diperbarui
                    $allowedMimeTypes = ['image/jpeg', 'image/png'];
                    $fileMimeType = $photo->getClientMimeType();

                    if ($photo->isValid() && !$photo->hasMoved() && in_array($fileMimeType, $allowedMimeTypes) && $photo->getSizeByUnit('mb') <= 5) {
                        $originalName = $photo->getName();
                        $cleanedName = preg_replace('/[^A-Za-z0-9_.]/', '_', $originalName);
                        $newName = str_replace(' ', '_', $cleanedName);

                        $photo->move(FCPATH . 'uploads/photos', $newName);
                        $data_photo = [
                            'id_koordinat' => $koordinat_id,
                            'nama_photo' => $newName, // Simpan nama file yang telah diubah
                            'file_path' => 'uploads/photos/' . $newName,
                            'file_type' => $fileMimeType,
                            'file_size' => $photo->getSizeByUnit('kb')
                        ];
                        $this->photoModel->insert($data_photo);
                    } else {
                        // Rollback transaksi jika ada file yang gagal validasi
                        $this->koordinatModel->db->transRollback();
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Validasi foto gagal. Pastikan file adalah PNG atau JPG dan tidak lebih dari 5MB.'
                        ]);
                    }
                }
            }

            $this->koordinatModel->db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Marker dan foto berhasil disimpan.', 'id_koordinat' => $koordinat_id]);
        } else {
            $this->koordinatModel->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data marker.']);
        }
    }

    public function deleteMarker($id)
    {
        $request = \Config\Services::request();
        // Pastikan permintaan datang dari AJAX
        if (!$request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.', 'redirect' => false]);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Hapus foto terkait terlebih dahulu
            $photos = $this->photoModel->where('id_koordinat', $id)->findAll();
            foreach ($photos as $photo) {
                $file_path = FCPATH . $photo['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $this->photoModel->delete($photo['id_photo']);
            }

            // Hapus keterangan terkait
            $this->isiKeteranganModel->where('id_koordinat', $id)->delete();

            // Hapus marker utama
            if ($this->koordinatModel->delete($id)) {
                $db->transCommit();
                // Mengembalikan respons JSON yang sukses
                return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
            } else {
                $db->transRollback();
                // Mengembalikan respons JSON yang gagal
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Delete failed: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // FUNGSI EXPORT KML (Disesuaikan untuk Multi-Filter)
    // =========================================================================
    public function exportKML()
    {
        // --- AWAL LOGIKA MULTIPLE FILTER ---
        $sumber_ids_raw = $this->request->getGet('sumber_data_id');
        $sumber_ids = $sumber_ids_raw ? array_map('intval', explode(',', $sumber_ids_raw)) : null;
        // --- AKHIR LOGIKA MULTIPLE FILTER ---

        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');

        $builder = $this->koordinatModel->select('koordinat.*, sumber_data.nama_sumber, isi_keterangan.isi_keterangan, isi_keterangan.id_koordinat')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('isi_keterangan', 'isi_keterangan.id_koordinat = koordinat.id_koordinat', 'left');

        if ($sumber_ids) {
            // Ini adalah inti dari filter berganda. whereIn() mencari data yang cocok dengan salah satu ID dalam array.
            $builder->whereIn('koordinat.id_sumberdata', $sumber_ids);
        }
        if ($id_kotakab) {
            $builder->where('koordinat.id_kotakab', $id_kotakab);
        }
        if ($id_kec) {
            $builder->where('koordinat.id_kec', $id_kec);
        }
        if ($id_kel) {
            $builder->where('koordinat.id_kel', $id_kel);
        }

        $koordinatData = $builder->findAll();

        $kmlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $kmlContent .= '<kml xmlns="http://www.opengis.net/kml/2.2">';
        $kmlContent .= '<Document>';

        foreach ($koordinatData as $item) {
            $kmlContent .= '<Placemark>';
            $kmlContent .= '<name>' . htmlspecialchars($item['nama_sumber'] . ' - ID ' . $item['id_koordinat']) . '</name>';
            $kmlContent .= '<description><![CDATA[<b>Keterangan:</b> ' . htmlspecialchars($item['keterangan_lokasi'] ?? 'N/A') . '<br><b>Koordinat:</b> ' . $item['latitude'] . ', ' . $item['longitude'] . ']]></description>';
            $kmlContent .= '<Point>';
            $kmlContent .= '<coordinates>' . $item['longitude'] . ',' . $item['latitude'] . ',0</coordinates>';
            $kmlContent .= '</Point>';
            $kmlContent .= '</Placemark>';
        }

        $kmlContent .= '</Document>';
        $kmlContent .= '</kml>';

        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/vnd.google-earth.kml+xml')
            ->setHeader('Content-Disposition', 'attachment; filename="peta_data.kml"')
            ->setBody($kmlContent);
    }

    // =========================================================================
    // FUNGSI EXPORT EXCEL (Disesuaikan untuk Multi-Filter)
    // =========================================================================
    public function exportExcel()
    {
        ini_set('max_execution_time', 300);

        // --- AWAL LOGIKA MULTIPLE FILTER ---
        $sumber_ids_raw = $this->request->getGet('sumber_data_id');
        $sumber_ids = $sumber_ids_raw ? array_map('intval', explode(',', $sumber_ids_raw)) : null;
        // --- AKHIR LOGIKA MULTIPLE FILTER ---

        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');

        $koordinatBuilder = $this->koordinatModel->getDataKoordinatQuery();
        
        if ($sumber_ids) {
            // Ini adalah inti dari filter berganda. whereIn() mencari data yang cocok dengan salah satu ID dalam array.
            $koordinatBuilder->whereIn('koordinat.id_sumberdata', $sumber_ids);
        }
        if ($id_kotakab) {
            $koordinatBuilder->where('koordinat.id_kotakab', $id_kotakab);
        }
        if ($id_kec) {
            $koordinatBuilder->where('koordinat.id_kec', $id_kec);
        }
        if ($id_kel) {
            $koordinatBuilder->where('koordinat.id_kel', $id_kel);
        }
        
        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));

        $uniqueKoordIds = array_column($koordinatData, 'id_koordinat');
        $allKeterangan = $this->isiKeteranganModel
            ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan, isi_keterangan.id_koordinat')
            ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
            ->whereIn('isi_keterangan.id_koordinat', $uniqueKoordIds)
            ->findAll();

        $keteranganGrouped = [];
        foreach ($allKeterangan as $keterangan) {
            $keteranganGrouped[$keterangan['id_koordinat']][$keterangan['jdl_keterangan']] = $keterangan['isi_keterangan'];
        }

        $allJudulKeterangan = $this->judulKeteranganModel
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $dynamicHeaders = array_column($allJudulKeterangan, 'jdl_keterangan');

        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $rowData = $koordinat;
            $keteranganMap = $keteranganGrouped[$koordinat['id_koordinat']] ?? [];
            foreach ($dynamicHeaders as $header) {
                $rowData[$header] = $keteranganMap[$header] ?? 'N/A';
            }
            $processedData[] = $rowData;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $staticHeaders = ['Sumber Data', 'Kota/Kabupaten', 'Kecamatan', 'Kelurahan', 'Latitude', 'Longitude'];
        $allHeaders = array_merge($staticHeaders, $dynamicHeaders);
        $sheet->fromArray($allHeaders, NULL, 'A1');

        $rowNumber = 2;
        foreach ($processedData as $dataRow) {
            $sheet->setCellValue('A' . $rowNumber, $dataRow['nama_sumber']);
            $sheet->setCellValue('B' . $rowNumber, $dataRow['nama_kotakab']);
            $sheet->setCellValue('C' . $rowNumber, $dataRow['nama_kec']);
            $sheet->setCellValue('D' . $rowNumber, $dataRow['nama_kel']);
            $sheet->setCellValue('E' . $rowNumber, $dataRow['latitude']);
            $sheet->setCellValue('F' . $rowNumber, $dataRow['longitude']);

            $colIndex = 'G';
            foreach ($dynamicHeaders as $header) {
                $sheet->setCellValue($colIndex . $rowNumber, $dataRow[$header] ?? 'N/A');
                $colIndex++;
            }
            $rowNumber++;
        }

        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_peta_data.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    // =========================================================================
    // FUNGSI EXPORT PDF (Disesuaikan untuk Multi-Filter)
    // =========================================================================
    public function exportPDF()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        // --- AWAL LOGIKA MULTIPLE FILTER ---
        $sumber_ids_raw = $this->request->getGet('sumber_data_id');
        $sumber_ids = $sumber_ids_raw ? array_map('intval', explode(',', $sumber_ids_raw)) : null;
        // --- AKHIR LOGIKA MULTIPLE FILTER ---

        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');

        $koordinatBuilder = $this->koordinatModel
            ->select('koordinat.*, sumber_data.nama_sumber, kota_kab.nama_kotakab, kecamatan.nama_kec, kelurahan.nama_kel')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left');

        if ($sumber_ids) {
            // Ini adalah inti dari filter berganda. whereIn() mencari data yang cocok dengan salah satu ID dalam array.
            $koordinatBuilder->whereIn('koordinat.id_sumberdata', $sumber_ids);
        }
        if ($id_kotakab) {
            $koordinatBuilder->where('koordinat.id_kotakab', $id_kotakab);
        }
        if ($id_kec) {
            $koordinatBuilder->where('koordinat.id_kec', $id_kec);
        }
        if ($id_kel) {
            $koordinatBuilder->where('koordinat.id_kel', $id_kel);
        }

        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $uniqueKoordIds = array_column($koordinatData, 'id_koordinat');
        $allKeterangan = $this->isiKeteranganModel
            ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan, isi_keterangan.id_koordinat')
            ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
            ->whereIn('isi_keterangan.id_koordinat', $uniqueKoordIds)
            ->findAll();

        $keteranganGrouped = [];
        foreach ($allKeterangan as $keterangan) {
            $keteranganGrouped[$keterangan['id_koordinat']][$keterangan['jdl_keterangan']] = $keterangan['isi_keterangan'];
        }

        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));
        $allJudulKeterangan = $this->judulKeteranganModel
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $data['dynamicHeaders'] = array_column($allJudulKeterangan, 'jdl_keterangan');

        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $rowData = $koordinat;
            $keteranganMap = $keteranganGrouped[$koordinat['id_koordinat']] ?? [];
            foreach ($data['dynamicHeaders'] as $header) {
                $rowData[$header] = $keteranganMap[$header] ?? 'N/A';
            }
            $processedData[] = $rowData;
        }
        $data['processedData'] = $processedData;

        $html = view('pdf/laporan_peta', $data);
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        $pdf->stream('laporan_peta_data.pdf', ['Attachment' => true]);
        exit();
    }
}
