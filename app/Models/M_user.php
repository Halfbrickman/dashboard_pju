<?php

namespace App\Models;

use CodeIgniter\Model;

class M_user extends Model
{
    protected $DBGroup           = 'default';
    protected $table             = 'users';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array';
    
    // Aktifkan Soft Deletes
    protected $useSoftDeletes    = true;
    
    protected $protectFields     = true;
    // Tambahkan kolom _by ke allowedFields
    protected $allowedFields     = ['username', 'nama', 'password', 'role_id', 'id_sumberdata', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by']; // <-- DIREVISI

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Definisikan kolom untuk Soft Deletes

    // Hooks untuk otomatis mengisi kolom _by
    protected $beforeInsert = ['setCratedBy'];
    protected $beforeUpdate = ['setUpdatedBy'];
    protected $beforeDelete = ['setDeletedBy']; 

    // Function untuk mendapatkan nama user dari sesi
    private function getCurrentUserNama()
    {
        return session()->get('nama') ?? 'System/Guest';
    }

    // Hook: Sebelum Insert, set 'created_by'
    protected function setCratedBy(array $data)
    {
        $data['data']['created_by'] = $this->getCurrentUserNama();
        return $data;
    }

    // Hook: Sebelum Update, set 'updated_by'
    protected function setUpdatedBy(array $data)
    {
        $data['data']['updated_by'] = $this->getCurrentUserNama();
        return $data;
    }

    // Hook: Sebelum Delete (Soft Delete), set 'deleted_by'
    protected function setDeletedBy(array $data)
    {
        if ($this->useSoftDeletes && !empty($data['id'])) {
            $this->update($data['id'], ['deleted_by' => $this->getCurrentUserNama()]);
        }
        return $data;
    }

    // Validation (dikosongkan)
    protected $validationRules       = [];
    protected $validationMessages    = [];
    protected $skipValidation        = false;

    // Ubah method ini untuk mengambil data pengguna yang BELUM terhapus (soft delete)
    public function getAllUsersWithRoleAndSource()
    {
        return $this->select('users.*, roles.nama_roles, sumber_data.nama_sumber')
                     ->join('roles', 'roles.id = users.role_id')
                     ->join('sumber_data', 'sumber_data.id_sumberdata = users.id_sumberdata', 'left') 
                     ->where('users.deleted_at', null) // Sudah ada, tapi diperjelas di Model lain.
                     ->findAll();
    }
}