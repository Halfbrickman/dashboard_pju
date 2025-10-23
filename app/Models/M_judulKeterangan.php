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

    /**
     * Mengambil data judul keterangan dengan filter berdasarkan user login
     * - Jika user memiliki id_sumberdata: tampilkan hanya data miliknya
     * - Jika user tidak memiliki id_sumberdata (null): tampilkan semua data aktif
     * 
     * @return array
     */
    public function getJudulKeterangan()
    {
        $session = session();
        $userIdSumberdata = $session->get('id_sumberdata');
        
        $builder = $this->select('judul_keterangan.*, sumber_data.nama_sumber')
                        ->join('sumber_data', 'sumber_data.id_sumberdata = judul_keterangan.id_sumberdata');
        
        // Filter berdasarkan id_sumberdata user
        if ($userIdSumberdata !== null && $userIdSumberdata !== '') {
            // User memiliki id_sumberdata spesifik, tampilkan hanya data miliknya
            $builder->where('judul_keterangan.id_sumberdata', $userIdSumberdata);
        }
        // Jika id_sumberdata null/kosong, tidak ada filter tambahan
        // (akan menampilkan semua data yang tidak di-soft delete)
        
        // useSoftDeletes = true akan otomatis menambahkan where('deleted_at IS NULL')
        return $builder->findAll();
    }
}