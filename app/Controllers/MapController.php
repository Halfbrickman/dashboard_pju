<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan; // Tambahkan model M_isiKeterangan
use CodeIgniter\Controller;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MapController extends Controller
{
    public function index()
    {
        $modelKoordinat = new M_koordinat();
        $modelSumberData = new M_sumberData();
        $modelJudulKeterangan = new M_judulKeterangan();

        $data['koordinat'] = $modelKoordinat->getDataKoordinat();
        $data['sumber_data'] = $modelSumberData->findAll();
        $data['judul_keterangan'] = $modelJudulKeterangan->findAll();

        return view('Template/header', $data)
            . view('Template/sidebar')
            . view('maps/maps', $data)
            . view('Template/footer');
    }

    public function getMarkerData()
    {
        $request = \Config\Services::request();
        $sumber_id = $request->getGet('sumber_data_id');
        $koordinat_id = $request->getGet('id_koordinat'); // Tambahkan ini

        $modelKoordinat = new M_koordinat();
        $modelIsiKeterangan = new M_isiKeterangan();
        $modelJudulKeterangan = new M_judulKeterangan();

        // Cek jika filter koordinat_id diterapkan
        if ($koordinat_id) {
            $dataKoordinat = $modelKoordinat->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_koordinat', $koordinat_id) // Filter berdasarkan id_koordinat
                ->findAll();
        } elseif ($sumber_id) {
            $dataKoordinat = $modelKoordinat->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
                ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
                ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
                ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
                ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
                ->where('koordinat.id_sumberdata', $sumber_id)
                ->findAll();
        } else {
            $dataKoordinat = $modelKoordinat->getDataKoordinat();
        }

        foreach ($dataKoordinat as &$koordinat) {
            $koordinat['keterangan'] = $modelIsiKeterangan
                ->select('isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->where('isi_keterangan.id_koordinat', $koordinat['id_koordinat'])
                ->findAll();
        }

        return $this->response->setJSON($dataKoordinat);
    }

    public function exportKML()
    {
        $sumber_id = $this->request->getGet('sumber_data_id');
        $modelKoordinat = new M_koordinat();

        $builder = $modelKoordinat->select('koordinat.*, sumber_data.nama_sumber, isi_keterangan.isi_keterangan')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('isi_keterangan', 'isi_keterangan.id_koordinat = koordinat.id_koordinat', 'left');

        if ($sumber_id) {
            $builder->where('koordinat.id_sumberdata', $sumber_id);
        }
        $koordinatData = $builder->findAll();

        // Proses pembuatan string KML (tidak ada yang berubah di sini)
        $kmlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $kmlContent .= '<kml xmlns="http://www.opengis.net/kml/2.2">';
        $kmlContent .= '<Document>';

        foreach ($koordinatData as $item) {
            $kmlContent .= '<Placemark>';
            // Gunakan htmlspecialchars untuk keamanan data
            $kmlContent .= '<name>' . htmlspecialchars($item['nama_sumber'] . ' - ID ' . $item['id_koordinat']) . '</name>';
            $kmlContent .= '<description><![CDATA[<b>Keterangan:</b> ' . htmlspecialchars($item['keterangan_lokasi'] ?? 'N/A') . '<br><b>Koordinat:</b> ' . $item['latitude'] . ', ' . $item['longitude'] . ']]></description>';
            $kmlContent .= '<Point>';
            $kmlContent .= '<coordinates>' . $item['longitude'] . ',' . $item['latitude'] . ',0</coordinates>';
            $kmlContent .= '</Point>';
            $kmlContent .= '</Placemark>';
        }

        $kmlContent .= '</Document>';
        $kmlContent .= '</kml>';

        // --- BAGIAN YANG DIPERBAIKI ---
        // Menggunakan Response Object dari CodeIgniter untuk memaksa download
        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/vnd.google-earth.kml+xml')
            ->setHeader('Content-Disposition', 'attachment; filename="peta_data.kml"')
            ->setBody($kmlContent);
    }

    // FUNGSI BARU UNTUK EKSPOR EXCEL
    public function exportExcel()
    {
        ini_set('max_execution_time', 300);

        $sumber_id = $this->request->getGet('sumber_data_id');
        $modelKoordinat = new M_koordinat();
        $modelIsiKeterangan = new M_isiKeterangan();
        $modelJudulKeterangan = new M_judulKeterangan();

        // LANGKAH 1: Ambil data koordinat utama terlebih dahulu
        $koordinatBuilder = $modelKoordinat->getDataKoordinatQuery();
        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
        }
        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            // Jika tidak ada data, hentikan proses dan beri pesan
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        // LANGKAH 2: Ambil ID Sumber Data unik dari data yang akan diekspor
        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));

        // LANGKAH 3: Ambil header dinamis HANYA untuk sumber data yang relevan
        $allJudulKeterangan = $modelJudulKeterangan
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $dynamicHeaders = array_column($allJudulKeterangan, 'jdl_keterangan');

        // LANGKAH 4: Proses dan gabungkan data seperti sebelumnya
        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $keteranganItems = $modelIsiKeterangan
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

        // --- Pembuatan Excel (Tidak ada perubahan di bagian ini) ---
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

    // FUNGSI BARU UNTUK EKSPOR PDF
    public function exportPDF()
    {
        ini_set('max_execution_time', 300);

        $sumber_id = $this->request->getGet('sumber_data_id');
        $modelKoordinat = new M_koordinat();
        $modelIsiKeterangan = new M_isiKeterangan();
        $modelJudulKeterangan = new M_judulKeterangan();

        // LANGKAH 1: Ambil data koordinat utama terlebih dahulu
        $koordinatBuilder = $modelKoordinat->getDataKoordinatQuery();
        if ($sumber_id) {
            $koordinatBuilder->where('koordinat.id_sumberdata', $sumber_id);
        }
        $koordinatData = $koordinatBuilder->findAll();

        if (empty($koordinatData)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        // LANGKAH 2: Ambil ID Sumber Data unik dari data yang akan diekspor
        $uniqueSumberIds = array_unique(array_column($koordinatData, 'id_sumberdata'));

        // LANGKAH 3: Ambil header dinamis HANYA untuk sumber data yang relevan
        $allJudulKeterangan = $modelJudulKeterangan
            ->whereIn('id_sumberdata', $uniqueSumberIds)
            ->orderBy('id_jdlketerangan', 'ASC')
            ->findAll();
        $data['dynamicHeaders'] = array_column($allJudulKeterangan, 'jdl_keterangan');

        // LANGKAH 4: Proses dan gabungkan data keterangan untuk setiap koordinat
        $processedData = [];
        foreach ($koordinatData as $koordinat) {
            $keteranganItems = $modelIsiKeterangan
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

        // --- Pembuatan PDF ---
        $html = view('pdf/laporan_peta', $data);
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        $pdf->stream('laporan_peta_data.pdf', ['Attachment' => true]);
        exit();
    }
}
