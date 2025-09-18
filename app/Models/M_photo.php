<?php namespace App\Models;

use CodeIgniter\Model;

class M_photo extends Model
{
    protected $table = 'photo';
    protected $primaryKey = 'id_photo';
    protected $allowedFields = ['id_koordinat', 'file_path', 'nama_photo'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}