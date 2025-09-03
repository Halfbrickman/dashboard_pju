<?php

namespace App\Models;

use CodeIgniter\Model;

class M_koordinat extends Model
{
    protected $table = 'koordinat';
    protected $primaryKey = 'id_koordinat';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_kec',
        'id_kel',
        'id_sumberdata',
        'id_jdlketerangan',
        'id_kotakab',
        'nomor_gardu',
        'tipe_gardu',
        'daya_gardu',
        'nomor_tiang',
        'nomor_pju',
        'nomor_pelanggan',
        'latitude',
        'longitude',
        'keterangan_lokasi',
        'kondisi_pju',
        'daya_pju'
    ];
    
    // Tambahkan metode ini untuk mengambil data koordinat
    public function getDataKoordinat()
    {
        return $this->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
            ->findAll();
    }
}