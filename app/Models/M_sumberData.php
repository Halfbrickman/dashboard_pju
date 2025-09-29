<?php

namespace App\Models;

use CodeIgniter\Model;

class M_sumberData extends Model
{
    protected $table            = 'sumber_data';
    protected $primaryKey       = 'id_sumberdata';
    protected $useAutoIncrement = true;
    // Tambahkan created_at dan updated_at ke allowedFields
    protected $allowedFields    = ['nama_sumber', 'warna', 'created_at', 'updated_at', 'deleted_at'];

    // Aktifkan Timestamps
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime'; // Sesuaikan dengan tipe data kolom
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Aktifkan Soft Deletes
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
}