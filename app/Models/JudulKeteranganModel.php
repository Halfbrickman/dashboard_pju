<?php

namespace App\Models;

use CodeIgniter\Model;

class JudulKeteranganModel extends Model
{
    protected $table = 'db_pju.judul_keterangan';
    protected $primaryKey = 'id_jdlketerangan';
    protected $allowedFields = ['jdl_keterangan', 'id_sumberdata'];
    public function getJudulKeterangan()
    {
        return $this->select('judul_keterangan.*, sumber_data.nama_sumber')
                    ->join('sumber_data', 'sumber_data.id_sumberdata = judul_keterangan.id_sumberdata')
                    ->findAll();
    }
}