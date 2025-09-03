<?php

namespace App\Models;

use CodeIgniter\Model;

class SumberDataModel extends Model
{
    protected $table = 'db_pju.sumber_data';
    protected $primaryKey = 'id_sumberdata';
    protected $allowedFields = ['nama_sumber', 'warna'];
}