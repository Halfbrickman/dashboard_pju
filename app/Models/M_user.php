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
    // Kolom yang diizinkan sesuai struktur DB Anda, TIDAK ADA kolom _by.
    protected $allowedFields     = ['username', 'nama', 'password', 'role_id', 'id_sumberdata']; 

    // Dates (Hanya menggunakan kolom yang ada di DB Anda)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at'; // Kolom ini ada
    protected $updatedField  = 'updated_at'; // Kolom ini ada
    protected $deletedField  = 'deleted_at'; // Kolom ini ada

    // Hooks: Hanya untuk Password dan Logika Role Superadmin
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPasswordIfPresent', 'handleSuperadminRole'];
    protected $beforeDelete = []; // Tidak ada deleted_by, jadi kosongkan

    // ******************************************************************
    // HOOKS LOGIKA KHUSUS
    // ******************************************************************
    
    // 1. Hook: Hash password saat insert
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    // 2. Hook: Hash password HANYA jika diisi saat update (jika kosong, jangan ubah)
    protected function hashPasswordIfPresent(array $data)
    {
        // Jika field password ada di data yang dikirim
        if (isset($data['data']['password'])) {
            if (empty($data['data']['password'])) {
                 // Jika kosong, hapus dari data yang akan diupdate agar password lama dipertahankan
                unset($data['data']['password']); 
            } else {
                 // Jika diisi, hash password sebelum update
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    // 3. Hook: Mengabaikan role_id=1 dari data yang akan disimpan (Solusi final untuk error role_id)
    protected function handleSuperadminRole(array $data)
    {
        // Cek jika ini adalah operasi UPDATE DAN role_id yang dikirim adalah 1 (Superadmin)
        if (isset($data['id']) && isset($data['data']['role_id']) && $data['data']['role_id'] == 1) {
            
            // Hapus 'role_id' dari data yang akan diupdate.
            // Database akan mempertahankan nilai role_id lama.
            unset($data['data']['role_id']);
        }
        return $data;
    }

    // ******************************************************************
    // VALIDATION & UTILS
    // ******************************************************************

    // Aturan validasi (dapat digunakan oleh Controller atau Model)
    protected $validationRules      = [
        'role_id'     => 'permit_empty|integer', 
        'password'    => 'permit_empty', 
    ];
    protected $validationMessages    = [];
    protected $skipValidation        = false;

    // Ubah method ini untuk mengambil data pengguna yang BELUM terhapus (soft delete)
    public function getAllUsersWithRoleAndSource()
    {
        return $this->select('users.*, roles.nama_roles, sumber_data.nama_sumber')
                     ->join('roles', 'roles.id = users.role_id')
                     ->join('sumber_data', 'sumber_data.id_sumberdata = users.id_sumberdata', 'left') 
                     ->where('users.deleted_at', null)
                     ->findAll();
    }
}