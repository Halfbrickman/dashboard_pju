<?php

namespace App\Models;

use CodeIgniter\Model;

class M_notifikasi extends Model
{
    protected $table            = 'notifikasi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['pesan', 'nama_file', 'path_file', 'tipe', 'created_at'];

    /**
     * Mengambil notifikasi terbaru.
     * @param int $limit Jumlah notifikasi yang ingin ditampilkan.
     * @return array
     */
    public function getRecentNotifications($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
