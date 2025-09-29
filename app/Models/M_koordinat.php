<?php

namespace App\Models;

use CodeIgniter\Model;

class M_koordinat extends Model
{
    protected $table = 'koordinat';
    protected $primaryKey = 'id_koordinat';
    
    // Aktifkan Fitur Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Aktifkan Fitur Soft Delete
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    // Tambahkan kolom _by ke allowedFields
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
        'daya_pju',
        'created_by', // <-- BARU
        'updated_by', // <-- BARU
        'deleted_by'  // <-- BARU
    ];
    
    // Hooks untuk otomatis mengisi kolom _by
    protected $beforeInsert = ['setCratedBy'];
    protected $beforeUpdate = ['setUpdatedBy'];
    protected $beforeDelete = ['setDeletedBy']; // Digunakan untuk Soft Delete

    // Function untuk mendapatkan nama user dari sesi
    private function getCurrentUserNama()
    {
        return session()->get('nama') ?? 'System/Guest';
    }

    // Hook: Sebelum Insert, set 'created_by'
    protected function setCratedBy(array $data)
    {
        $data['data']['created_by'] = $this->getCurrentUserNama();
        return $data;
    }

    // Hook: Sebelum Update, set 'updated_by'
    protected function setUpdatedBy(array $data)
    {
        $data['data']['updated_by'] = $this->getCurrentUserNama();
        return $data;
    }

    // Hook: Sebelum Delete (Soft Delete), set 'deleted_by'
    protected function setDeletedBy(array $data)
    {
        // Karena soft delete di CI4 dijalankan dengan update, kita perlu set deleted_by secara eksplisit
        if ($this->useSoftDeletes && !empty($data['id'])) {
            $this->update($data['id'], ['deleted_by' => $this->getCurrentUserNama()]);
        }
        return $data;
    }
    
    // Update query untuk menyertakan filter soft delete
    public function getDataKoordinatQuery()
    {
        return $this->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left')
            ->where('koordinat.deleted_at', null); // <-- Filter Soft Delete
    }

    public function getDataKoordinat()
    {
        return $this->getDataKoordinatQuery()
            ->get() 
            ->getResultArray(); 
    }

    public function getFilteredMarkers($sumber_data_id, $id_kotakab, $id_kec, $id_kel)
    {
        $builder = $this->db->table('koordinat');
        $builder->select('koordinat.*, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab, kecamatan.nama_kec, kelurahan.nama_kel');
        $builder->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left');
        $builder->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left');
        $builder->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left');
        $builder->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left');
        $builder->where('koordinat.deleted_at', null); // <-- Filter Soft Delete

        // ... (Logika filter lainnya tetap sama)
        if (!empty($sumber_data_id) && is_array($sumber_data_id)) {
            $builder->whereIn('koordinat.id_sumberdata', $sumber_data_id);
        } else if (!empty($sumber_data_id)) {
            $builder->where('koordinat.id_sumberdata', $sumber_data_id);
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

        return $builder->get()->getResultArray();
    }
}