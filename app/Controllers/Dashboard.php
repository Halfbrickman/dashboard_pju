<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_isiKeterangan; // <-- [TAMBAHKAN] Panggil model M_isiKeterangan

class Dashboard extends BaseController
{
    public function index()
    {
        $modelKordinat = new M_koordinat();
        $modelSumberData = new M_sumberData();
        $modelIsiKeterangan = new M_isiKeterangan(); // <-- [TAMBAHKAN] Buat instance model

        // 1. Ambil data koordinat utama
        $koordinatData = $modelKordinat->getDataKoordinat();
        
        // **[MODIFIKASI UTAMA DIMULAI DI SINI]**
        // Lakukan langkah-langkah berikut hanya jika ada data koordinat
        if (!empty($koordinatData)) {
            // 2. Kumpulkan semua ID koordinat dari data di atas
            $koordinatIds = array_column($koordinatData, 'id_koordinat');

            // 3. Ambil SEMUA keterangan tambahan yang relevan dalam SATU query
            $allKeterangan = $modelIsiKeterangan
                ->select('isi_keterangan.id_koordinat, isi_keterangan.isi_keterangan, judul_keterangan.jdl_keterangan')
                ->join('judul_keterangan', 'judul_keterangan.id_jdlketerangan = isi_keterangan.id_jdlketerangan')
                ->whereIn('isi_keterangan.id_koordinat', $koordinatIds) // Gunakan whereIn untuk efisiensi
                ->findAll();

            // 4. Petakan keterangan ke ID koordinatnya agar mudah diakses
            $keteranganMap = [];
            foreach ($allKeterangan as $keterangan) {
                $id = $keterangan['id_koordinat'];
                // Hapus id_koordinat dari array agar tidak mubazir
                unset($keterangan['id_koordinat']); 
                $keteranganMap[$id][] = $keterangan;
            }

            // 5. Gabungkan data keterangan ke dalam data koordinat utama
            foreach ($koordinatData as &$item) { // Gunakan '&' agar bisa memodifikasi array asli
                $id = $item['id_koordinat'];
                // Tambahkan key baru 'keterangan_tambahan'
                $item['keterangan_tambahan'] = $keteranganMap[$id] ?? []; // Jika tidak ada, beri array kosong
            }
        }
        // **[MODIFIKASI SELESAI]**
        
        $sumberData = $modelSumberData->findAll();
        
        $data = [
            'title' => 'Dashboard',
            'koordinat_json' => json_encode($koordinatData), // Encode data koordinat menjadi JSON
            'dataKordinat' => count($koordinatData),
            'dataPerSumber' => [],
            'datasets' => [],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
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
            
            $datasets[] = [
                'label' => $sumber['nama_sumber'],
                'fill' => false,
                'backgroundColor' => $sumber['warna'],
                'borderColor' => $sumber['warna'],
                'data' => $dataBulanan
            ];
        }

        // Kirim semua data ke view
        $data['labels'] = json_encode($data['labels']);
        $data['datasets'] = json_encode($data['datasets']);

        $userRoleId = session()->get('role_id');

        if ($userRoleId == 1) { // Jika role_id adalah Admin
            echo view('Template/header', $data);
            echo view('Template/sidebar');
            echo view('dashboard', $data); // Tampilkan view khusus admin
            echo view('Template/assetDashboard');
            echo view('Template/footer');
        } else { // Jika role_id adalah User biasa
            echo view('Template/header', $data);
            echo view('Template/sidebar');
            echo view('dashboard', $data); // Tampilkan view dashboard biasa
            echo view('Template/assetDashboard');
            echo view('Template/footer');
        }
    }
}
