<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan;
use App\Models\M_Wilayah;
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MapController extends BaseController
{
    protected $koordinatModel;
    protected $sumberDataModel;
    protected $wilayahModel;
    protected $judulKeteranganModel;
    protected $isiKeteranganModel;

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
    }

    public function index()
    {
        // Ambil data dari model dan siapkan untuk view
        $data = [
            'sumber_data' => $this->sumberDataModel->findAll(),
            'kotakab' => $this->wilayahModel->getKotaKab(), // Perbaikan: getKotaKab()
            'kecamatan' => $this->wilayahModel->getKecamatan(), // Perbaikan: getKecamatan()
            'kelurahan' => $this->wilayahModel->getKelurahan(), // Perbaikan: getKelurahan()
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

        if ($koordinat_id) {
            $dataKoordinat = $this->koordinatModel->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_koordinat', $koordinat_id)
                ->findAll();
        } elseif ($sumber_id) {
            $dataKoordinat = $this->koordinatModel->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_sumberdata', $sumber_id)
                ->findAll();
        } else {
            $dataKoordinat = $this->koordinatModel->getDataKoordinat();
        }

        foreach ($dataKoordinat as &$koordinat) {
            $koordinat['keterangan'] = $this->isiKeteranganModel
                ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->where('isi_keterangan.id_koordinat', $koordinat['id_koordinat'])
                ->findAll();
        }

        return $this->response->setJSON($dataKoordinat);
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

    public function deleteMarker($id)
    {
        if ($this->koordinatModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
    }

    // Metode exportKML, exportExcel, dan exportPDF
    // ...
    public function exportKML()
    {
        $sumber_id = $this->request->getGet('sumber_data_id');
        $builder = $this->koordinatModel->select('koordinat.*, sumber_data.nama_sumber, isi_keterangan.isi_keterangan')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('isi_keterangan', 'isi_keterangan.id_koordinat = koordinat.id_koordinat', 'left');

        if ($sumber_id) {
            $builder->where('koordinat.id_sumberdata', $sumber_id);
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
        $koordinatBuilder = $this->koordinatModel->getDataKoordinatQuery();
        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
        }
        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));
        $allJudulKeterangan = $this->judulKeteranganModel
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $dynamicHeaders = array_column($allJudulKeterangan, 'jdl_keterangan');

        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $keteranganItems = $this->isiKeteranganModel
                ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->where('isi_keterangan.id_koordinat', $koordinat['id_koordinat'])
                ->findAll();

            $keteranganMap = array_column($keteranganItems, 'isi_keterangan', 'jdl_keterangan');
            $rowData = $koordinat;
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
        ini_set('max_execution_time', 300);

        $sumber_id = $this->request->getGet('sumber_data_id');
        $koordinatBuilder = $this->koordinatModel->getDataKoordinatQuery();
        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
        }
        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));
        $allJudulKeterangan = $this->judulKeteranganModel
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $data['dynamicHeaders'] = array_column($allJudulKeterangan, 'jdl_keterangan');

        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $keteranganItems = $this->isiKeteranganModel
                ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->where('isi_keterangan.id_koordinat', $koordinat['id_koordinat'])
                ->findAll();

            $keteranganMap = array_column($keteranganItems, 'isi_keterangan', 'jdl_keterangan');
            $rowData = $koordinat;
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
