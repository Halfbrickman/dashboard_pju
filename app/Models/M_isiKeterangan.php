<?php

namespace App\Models;

use CodeIgniter\Model;

class M_isiKeterangan extends Model
{
    // Nama tabel
    protected $table = 'isi_keterangan'; 
    // Sesuaikan primary key, saya asumsikan 'id_isiketerangan' berdasarkan kode Anda
    protected $primaryKey = 'id_isiketerangan';
    
    // Field yang diizinkan untuk diisi (berdasarkan log query sebelumnya)
    protected $allowedFields = ['id_jdlketerangan', 'isi_keterangan', 'id_koordinat'];

    // PENTING: Matikan Timestamps secara eksplisit 
    // karena tabel 'isi_keterangan' TIDAK memiliki kolom created_at/updated_at.
    protected $useTimestamps = false;
    protected $createdField  = 'created_at'; // Ditinggalkan meskipun useTimestamps false,
    protected $updatedField  = 'updated_at'; // tapi tidak akan digunakan.
    
    // Tidak menggunakan Soft Delete (Hard Delete)
    protected $useSoftDeletes = false;
    // $deletedField tidak didefinisikan
}
