<?php

namespace App\Models;

use CodeIgniter\Model;

class M_koordinat extends Model
{
    protected $table = 'koordinat';
    protected $primaryKey = 'id_koordinat';
    
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';
    
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at'; 
    
    protected $allowedFields = [
        'id_kec', 'id_kel', 'id_sumberdata', 'id_jdlketerangan', 'id_kotakab', 
        'nomor_gardu', 'tipe_gardu', 'daya_gardu', 'nomor_tiang', 'nomor_pju', 
        'nomor_pelanggan', 'latitude', 'longitude', 'keterangan_lokasi', 
        'kondisi_pju', 'daya_pju', 
        'created_at', 'updated_at',
        'created_by', 'updated_by', 'deleted_by' 
    ];
    
    protected $beforeUpdate = ['setUpdatedBy'];
    protected $beforeInsert = ['setCreatedBy'];
    protected $beforeDelete = ['setDeletedBy'];
    
    protected function setCreatedBy(array $data)
    {
        if (!isset($data['data']['created_by'])) {
            $data['data']['created_by'] = session()->get('nama') ?? 'System/Guest';
        }
        return $data;
    }

    protected function setUpdatedBy(array $data)
    {
        if (!isset($data['data']['deleted_at'])) {
            $data['data']['updated_by'] = session()->get('nama') ?? 'System/Guest';
        }
        return $data;
    }
    
    protected function setDeletedBy(array $data)
    {
        $deletedBy = session()->get('nama') ?? 'System/Guest';
        $now = date('Y-m-d H:i:s');
        
        if (!empty($data['id'])) {
            $this->builder()
                 ->whereIn($this->primaryKey, $data['id'])
                 ->set(['deleted_by' => $deletedBy, 'updated_at' => $now]) 
                 ->update();
        }

        return $data; 
    }
    
    /**
     * Menambahkan filter berdasarkan user login
     * 
     * @param mixed $builder Query builder instance
     * @return mixed Query builder dengan filter user
     */
    protected function applyUserFilter($builder = null)
    {
        if ($builder === null) {
            $builder = $this;
        }
        
        $session = session();
        $userIdSumberdata = $session->get('id_sumberdata');
        
        // Filter berdasarkan id_sumberdata user
        if ($userIdSumberdata !== null && $userIdSumberdata !== '') {
            $builder->where('koordinat.id_sumberdata', $userIdSumberdata);
        }
        // Jika id_sumberdata null, tampilkan semua data (sudah otomatis filter soft delete)
        
        return $builder;
    }
    
    /**
     * Fungsi untuk membangun kueri JOIN dengan filter user
     */
    public function getDataKoordinatQuery()
    {
        $builder = $this->select('koordinat.*, kecamatan.nama_kec, kelurahan.nama_kel, sumber_data.nama_sumber, sumber_data.warna, kota_kab.nama_kotakab')
            ->join('kecamatan', 'kecamatan.id_kec = koordinat.id_kec', 'left')
            ->join('kelurahan', 'kelurahan.id_kel = koordinat.id_kel', 'left')
            ->join('sumber_data', 'sumber_data.id_sumberdata = koordinat.id_sumberdata', 'left')
            ->join('kota_kab', 'kota_kab.id_kotakab = koordinat.id_kotakab', 'left');
        
        // Terapkan filter user
        return $this->applyUserFilter($builder);
    }

    public function getDataKoordinat()
    {
        return $this->getDataKoordinatQuery()->findAll(); 
    }

    public function getFilteredMarkers($sumber_data_id, $id_kotakab, $id_kec, $id_kel)
    {
        $builder = $this->getDataKoordinatQuery();
        
        // Logika filter tambahan
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