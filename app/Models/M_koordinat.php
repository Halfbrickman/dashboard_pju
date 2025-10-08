<?php

namespace App\Models;

use CodeIgniter\Model;

class M_koordinat extends Model
{
    protected $table = 'koordinat';
    protected $primaryKey = 'id_koordinat';
    
    // AKTIFKAN Timestamps karena kolomnya ADA di DB
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    
    // Aktifkan Fitur Soft Delete
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at'; 
    
    // ** PENTING: TAMBAHKAN created_at dan updated_at ke allowedFields **
    // Ini memastikan Model tidak mengabaikan kolom waktu meskipun ada hook kustom.
    protected $allowedFields = [
        'id_kec', 'id_kel', 'id_sumberdata', 'id_jdlketerangan', 'id_kotakab', 
        'nomor_gardu', 'tipe_gardu', 'daya_gardu', 'nomor_tiang', 'nomor_pju', 
        'nomor_pelanggan', 'latitude', 'longitude', 'keterangan_lokasi', 
        'kondisi_pju', 'daya_pju', 
        // Kolom Waktu
        'created_at', 
        'updated_at',
        // Kolom User
        'created_by', 'updated_by', 'deleted_by' 
    ];
    
    // HOOKS
    protected $beforeUpdate = ['setUpdatedBy'];
    protected $beforeInsert = ['setCreatedBy'];
    
    /**
     * Set the creator user ID/name before inserting
     */
    protected function setCreatedBy(array $data)
    {
        // CI4 akan otomatis menambahkan created_at/updated_at. Kita tambahkan created_by.
        if (!isset($data['data']['created_by'])) {
            $data['data']['created_by'] = session()->get('nama') ?? 'System/Guest';
        }
        return $data;
    }

    /**
     * Set the updated user ID/name before updating.
     */
    protected function setUpdatedBy(array $data)
    {
        // Hanya set updated_by jika ini BUKAN operasi soft delete
        if (!isset($data['data']['deleted_at'])) {
            $data['data']['updated_by'] = session()->get('nama') ?? 'System/Guest';
        }
        return $data;
    }
    
    // Fungsi untuk membangun kueri JOIN. Soft Delete otomatis ditambahkan.
    public function getDataKoordinatQuery()
    {
        return $this->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left');
    }

    public function getDataKoordinat()
    {
        return $this->getDataKoordinatQuery()
            ->findAll(); 
    }

    public function getFilteredMarkers($sumber_data_id, $id_kotakab, $id_kec, $id_kel)
    {
        $builder = $this->getDataKoordinatQuery();
        
        // Logika filter
        if (!empty($sumber_data_id)) {
            $builder->whereIn('koordinat.id_sumberdata', is_array($sumber_data_id) ? $sumber_data_id : [$sumber_data_id]);
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

        return $builder->findAll(); 
    }
}
