<?php

namespace App\Models;

use CodeIgniter\Model;

class KoordinatModel extends Model
{
    protected $table = 'db_pju.koordinat';
    protected $primaryKey = 'id_koordinat';
    protected $allowedFields = ['latitude', 'longitude', 'id_kotakab', 'id_kec', 'id_kel', 'id_sumberdata'];
    protected $useTimestamps = true;
    protected $dateFormat = 'date';
}