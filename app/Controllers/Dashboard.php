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
        $modelKordinat = new M_koordinat();
        $modelSumberData = new M_sumberData();
        $modelIsiKeterangan = new M_isiKeterangan();
        $modelNotifikasi = new M_notifikasi();

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
        
        $sumberData = $modelSumberData->findAll();
        
        $data = [
            'title' => 'Dashboard',
            'koordinat_json' => json_encode($koordinatData),
            'dataKordinat' => count($koordinatData),
            'dataPerSumber' => [],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [], // Inisialisasi datasets di sini
            'notifikasi' => $modelNotifikasi->getRecentNotifications(5),
        ];
        
        foreach ($sumberData as $sumber) {
            $jumlah = $modelKordinat->where('id_sumberdata', $sumber['id_sumberdata'])->countAllResults();
            $data['dataPerSumber'][] = ['nama' => $sumber['nama_sumber'], 'jumlah' => $jumlah];
            
            $queryBulanan = $modelKordinat->select("MONTH(created_at) AS bulan, COUNT(*) AS jumlah")
                ->where('id_sumberdata', $sumber['id_sumberdata'])
                ->groupBy('bulan')
                ->get();

            $hasilBulanan = $queryBulanan->getResultArray();
            $dataPerBulan = array_fill(1, 12, 0);
            
            foreach ($hasilBulanan as $h) {
                $dataPerBulan[$h['bulan']] = $h['jumlah'];
            }
            
            $dataBulanan = array_values($dataPerBulan);
            
            // Tambahkan setiap dataset ke dalam array $data['datasets']
            $data['datasets'][] = [
                'label' => $sumber['nama_sumber'],
                'fill' => false,
                'backgroundColor' => $sumber['warna'],
                'borderColor' => $sumber['warna'],
                'data' => $dataBulanan
            ];
        }

        // Encode data untuk chart
        $data['labels'] = json_encode($data['labels']);
        $data['datasets'] = json_encode($data['datasets']);
        
        $userRoleId = session()->get('role_id');

        if ($userRoleId == 1) { // Jika role_id adalah Admin
            echo view('Template/header', $data);
            echo view('Template/sidebar');
            echo view('dashboard', $data);
            echo view('Template/assetDashboard');
            echo view('Template/footer');
        } else { // Jika role_id adalah User biasa
            echo view('Template/header', $data);
            echo view('Template/sidebar');
            echo view('dashboard', $data);
            echo view('Template/assetDashboard');
            echo view('Template/footer');
        }
    }

    public function downloadNotificationFile($id)
    {
        $modelNotifikasi = new M_notifikasi();
        $notif = $modelNotifikasi->find($id);

        if ($notif && file_exists($notif['path_file'])) {
            // Menggunakan helper download dari CodeIgniter
            return $this->response->download($notif['path_file'], null)->setFileName($notif['nama_file']);
        } else {
            // Jika file tidak ditemukan, redirect atau tampilkan error
            return redirect()->back()->with('error', 'File tidak ditemukan atau telah dihapus.');
        }
    }
}