<?php

namespace App\Models;

use CodeIgniter\Model;

class M_sumberData extends Model
{
    protected $table            = 'sumber_data';
    protected $primaryKey       = 'id_sumberdata';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id_sumberdata','nama_sumber','warna'];
}