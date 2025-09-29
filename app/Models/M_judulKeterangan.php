<?php

namespace App\Models;

use CodeIgniter\Model;

class M_judulKeterangan extends Model
{
    protected $table = 'judul_keterangan';
    protected $primaryKey = 'id_jdlketerangan';
    
    // Aktifkan timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Aktifkan Soft Delete
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = ['id_sumberdata', 'jdl_keterangan'];

    public function getJudulKeterangan()
    {
        // Karena useSoftDeletes = true, findAll() secara otomatis
        // hanya akan mengambil data yang deleted_at-nya NULL (data aktif).
        return $this->select('judul_keterangan.*, sumber_data.nama_sumber')
                    ->join('sumber_data', 'sumber_data.id_sumberdata = judul_keterangan.id_sumberdata')
                    ->findAll();
    }
}