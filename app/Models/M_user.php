<?php

namespace App\Models;

use CodeIgniter\Model;

class M_user extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'password', 'role_id', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    // Tambahkan method ini untuk mengambil data pengguna bersama nama role
    public function getAllUsersWithRole()
    {
        return $this->db->table('users')
                        ->select('users.*, roles.nama_roles') // Ubah 'role_name' menjadi 'nama_roles'
                        ->join('roles', 'roles.id = users.role_id')
                        ->get()
                        ->getResultArray();
    }
}