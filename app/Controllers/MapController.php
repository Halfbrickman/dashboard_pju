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
        $sumber_id = $request->getGet('sumber_data_id');
        $koordinat_id = $request->getGet('id_koordinat');
        $id_kotakab = $request->getGet('id_kotakab');
        $id_kec = $request->getGet('id_kec');
        $id_kel = $request->getGet('id_kel');

        // Menggunakan nama model yang benar sesuai skema Anda
        $photoModel = new \App\Models\M_photo(); 

        if ($koordinat_id) {
            $dataKoordinat = $this->koordinatModel->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_koordinat', $koordinat_id)
                ->findAll();
        } else {
            $dataKoordinat = $this->koordinatModel->getFilteredMarkers($sumber_id, $id_kotakab, $id_kec, $id_kel);
        }

        if (empty($dataKoordinat)) {
            return $this->response->setJSON([]);
        }

        $koordinatIds = array_column($dataKoordinat, 'id_koordinat');
        
        $allKeterangan = $this->isiKeteranganModel
            ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan, isi_keterangan.id_koordinat')
            ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
            ->whereIn('isi_keterangan.id_koordinat', $koordinatIds)
            ->findAll();

        $allPhotos = $photoModel->whereIn('id_koordinat', $koordinatIds)->findAll();

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
        $id = $this->request->getVar('id_koordinat');
        $data = [
            'id_sumberdata' => $this->request->getVar('id_sumberdata'),
            'id_kotakab' => $this->request->getVar('id_kotakab'),
            'id_kec' => $this->request->getVar('id_kec'),
            'id_kel' => $this->request->getVar('id_kel'),
            'latitude' => $this->request->getVar('latitude'),
            'longitude' => $this->request->getVar('longitude'),
        ];

        $keteranganData = $this->request->getVar('keterangan');

        if (empty($id) || empty($data['latitude']) || empty($data['longitude'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        }

        // Mulai transaksi untuk memastikan kedua update berhasil
        $this->koordinatModel->db->transBegin();

        // Update data di tabel koordinat
        $updateKoordinat = $this->koordinatModel->update($id, $data);

        $updateKeterangan = true;
        if ($keteranganData) {
            // Hapus keterangan lama untuk ID koordinat ini
            $this->isiKeteranganModel->where('id_koordinat', $id)->delete();

            // Simpan keterangan baru
            foreach ($keteranganData as $idJdlKeterangan => $isiKeterangan) {
                if (!empty($isiKeterangan)) { // Hanya simpan jika isinya tidak kosong
                    $dataKeterangan = [
                        'id_koordinat' => $id,
                        'id_jdlketerangan' => $idJdlKeterangan,
                        'isi_keterangan' => $isiKeterangan,
                    ];
                    if (!$this->isiKeteranganModel->insert($dataKeterangan)) {
                        $updateKeterangan = false;
                        break;
                    }
                }
            }
        }

        if ($updateKoordinat && $updateKeterangan) {
            $this->koordinatModel->db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
        } else {
            $this->koordinatModel->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
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
                    if ($photo->isValid() && !$photo->hasMoved()) {
                        $newName = $photo->getRandomName();
                        $photo->move(FCPATH . 'uploads/photos', $newName);
                        $data_photo = [
                            'id_koordinat' => $koordinat_id, // Gunakan ID marker yang baru
                            'file_name' => $newName
                        ];
                        $this->photoModel->insert($data_photo);
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
        if ($this->koordinatModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
    }

    public function exportKML()
    {
        $sumber_id = $this->request->getGet('sumber_data_id');
        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');
        
        $builder = $this->koordinatModel->select('koordinat.*, sumber_data.nama_sumber, isi_keterangan.isi_keterangan, isi_keterangan.id_koordinat')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('isi_keterangan', 'isi_keterangan.id_koordinat = koordinat.id_koordinat', 'left');

        if ($sumber_id) {
            $builder->where('koordinat.id_sumberdata', $sumber_id);
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

    public function exportExcel()
    {
        ini_set('max_execution_time', 300);

        $sumber_id = $this->request->getGet('sumber_data_id');
        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');

        $koordinatBuilder = $this->koordinatModel->getDataKoordinatQuery();
        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
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

    public function exportPDF()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $sumber_id = $this->request->getGet('sumber_data_id');
        $id_kotakab = $this->request->getGet('id_kotakab');
        $id_kec = $this->request->getGet('id_kec');
        $id_kel = $this->request->getGet('id_kel');

        $koordinatBuilder = $this->koordinatModel
            ->select('koordinat.*, sumber_data.nama_sumber, kota_kab.nama_kotakab, kecamatan.nama_kec, kelurahan.nama_kel')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left');

        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
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