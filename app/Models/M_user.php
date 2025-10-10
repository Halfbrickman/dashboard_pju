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
    // Catatan: 'password' ada di sini
    protected $allowedFields     = ['username', 'nama', 'password', 'role_id', 'id_sumberdata']; 

    // Dates (Hanya menggunakan kolom yang ada di DB Anda)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at'; // Kolom ini ada
    protected $updatedField  = 'updated_at'; // Kolom ini ada
    protected $deletedField  = 'deleted_at'; // Kolom ini ada

    // --- PERBAIKAN KRITIS: Hapus semua hook hashing. Hashing dilakukan di Controller. ---
    protected $beforeInsert = []; 
    protected $beforeUpdate = ['handleSuperadminRole']; 
    protected $beforeDelete = []; 

    // ******************************************************************
    // HOOKS LOGIKA KHUSUS
    // ******************************************************************
    
    // Catatan: Fungsi hashPassword dan hashPasswordIfPresent DIBUANG.

    // Hook: Mengabaikan role_id=1 dari data yang akan disimpan (Hanya untuk UPDATE)
    protected function handleSuperadminRole(array $data)
    {
        // Cek jika ini adalah operasi UPDATE DAN role_id yang dikirim adalah 1 (Superadmin)
        if (isset($data['id']) && isset($data['data']['role_id']) && $data['data']['role_id'] == 1) {
            
            // Hapus 'role_id' dari data yang akan diupdate.
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
