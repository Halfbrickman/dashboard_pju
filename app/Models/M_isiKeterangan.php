<?php

namespace App\Models;

use CodeIgniter\Model;

class M_isiKeterangan extends Model
{
    protected $table = 'db_pju.isi_keterangan';
    protected $primaryKey = 'id_isiketerangan';
    protected $allowedFields = ['id_jdlketerangan', 'isi_keterangan', 'id_koordinat'];
}