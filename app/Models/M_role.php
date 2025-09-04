<?php

namespace App\Models;

use CodeIgniter\Model;

class M_role extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_roles'];

    // Dates
    protected $useTimestamps = false;
}