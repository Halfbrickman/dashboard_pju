<?php

namespace App\Controllers;

use App\Models\M_koordinat;
use App\Models\M_sumberData;
use App\Models\M_judulKeterangan;
use App\Models\M_isiKeterangan; // Tambahkan model M_isiKeterangan
use CodeIgniter\Controller;

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
}
