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

    /**
     * Mengambil semua peran kecuali peran Superadmin (ID 1).
     * @param int $excludeRoleId ID peran yang akan dikecualikan (default 1).
     * @return array
     */
    public function getAssignableRoles(int $excludeRoleId = 1)
    {
        // Menggunakan where() untuk memfilter peran berdasarkan ID
        return $this->where('id !=', $excludeRoleId)->findAll();
    }
}