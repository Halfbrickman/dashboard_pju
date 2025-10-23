<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_isiKeterangan;
use App\Models\M_notifikasi;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        $userIdSumberdata = $session->get('id_sumberdata');
        
        $modelKordinat = new M_koordinat();
        $modelSumberData = new M_sumberData();
        $modelIsiKeterangan = new M_isiKeterangan();
        $modelNotifikasi = new M_notifikasi();

        // Ambil data koordinat dengan filter user otomatis dari model
        $koordinatData = $modelKordinat->getDataKoordinat(); 
        
        if (!empty($koordinatData)) {
            $koordinatIds = array_column($koordinatData, 'id_koordinat');
            $allKeterangan = $modelIsiKeterangan
                ->select('isi_keterangan.id_koordinat, isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->whereIn('isi_keterangan.id_koordinat', $koordinatIds)
                ->findAll();

            $keteranganMap = [];
            foreach ($allKeterangan as $keterangan) {
                $id = $keterangan['id_koordinat'];
                unset($keterangan['id_koordinat']); 
                $keteranganMap[$id][] = $keterangan;
            }

            foreach ($koordinatData as &$item) {
                $id = $item['id_koordinat'];
                $item['keterangan_tambahan'] = $keteranganMap[$id] ?? [];
            }
        }
        
        // Filter sumber data berdasarkan user
        $sumberDataQuery = $modelSumberData;
        if ($userIdSumberdata !== null && $userIdSumberdata !== '') {
            $sumberDataQuery = $sumberDataQuery->where('id_sumberdata', $userIdSumberdata);
        }
        $sumberData = $sumberDataQuery->findAll();
        
        $data = [
            'title' => 'Dashboard',
            'koordinat_json' => json_encode($koordinatData),
            'dataKordinat' => count($koordinatData),
            'dataPerSumber' => [],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [],
            'notifikasi' => $modelNotifikasi->getRecentNotifications(5),
        ];
        
        foreach ($sumberData as $sumber) {
            // Penghitungan Total Data Per Sumber (Dengan Filter User)
            $queryJumlah = $modelKordinat
                ->where('id_sumberdata', $sumber['id_sumberdata'])
                ->where('deleted_at', null);
            
            // Tambahkan filter user jika diperlukan
            if ($userIdSumberdata !== null && $userIdSumberdata !== '') {
                $queryJumlah->where('id_sumberdata', $userIdSumberdata);
            }
            
            $jumlah = $queryJumlah->countAllResults();
            $data['dataPerSumber'][] = ['nama' => $sumber['nama_sumber'], 'jumlah' => $jumlah];
            
            // Penghitungan Data Bulanan (Dengan Filter User)
            $queryBulanan = $modelKordinat->select("MONTH(created_at) AS bulan, COUNT(*) AS jumlah")
                ->where('id_sumberdata', $sumber['id_sumberdata'])
                ->where('deleted_at', null);
            
            // Tambahkan filter user jika diperlukan
            if ($userIdSumberdata !== null && $userIdSumberdata !== '') {
                $queryBulanan->where('id_sumberdata', $userIdSumberdata);
            }
            
            $hasilBulanan = $queryBulanan->groupBy('bulan')->get()->getResultArray();

            $dataPerBulan = array_fill(1, 12, 0);
            
            foreach ($hasilBulanan as $h) {
                $dataPerBulan[$h['bulan']] = $h['jumlah'];
            }
            
            $dataBulanan = array_values($dataPerBulan);
            
            $data['datasets'][] = [
                'label' => $sumber['nama_sumber'],
                'fill' => false,
                'backgroundColor' => $sumber['warna'],
                'borderColor' => $sumber['warna'],
                'data' => $dataBulanan
            ];
        }

        $data['labels'] = json_encode($data['labels']);
        $data['datasets'] = json_encode($data['datasets']);
        
        $userRoleId = $session->get('role_id');

        echo view('Template/header', $data);
        echo view('Template/sidebar');
        echo view('dashboard', $data);
        echo view('Template/assetDashboard');
        echo view('Template/footer');
    }

    public function downloadNotificationFile($id)
    {
        $modelNotifikasi = new M_notifikasi();
        $notif = $modelNotifikasi->find($id);

        if ($notif && file_exists($notif['path_file'])) {
            return $this->response->download($notif['path_file'], null)->setFileName($notif['nama_file']);
        } else {
            return redirect()->back()->with('error', 'File tidak ditemukan atau telah dihapus.');
        }
    }
}