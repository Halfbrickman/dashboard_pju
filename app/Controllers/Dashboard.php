<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;

class Dashboard extends BaseController
{
    public function index()
    {
        $modelKordinat = new M_koordinat();
        $modelSumberData = new M_sumberData();
        $koordinatData = $modelKordinat->getDataKoordinat();
        
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