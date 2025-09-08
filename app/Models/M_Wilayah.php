<?php 
namespace App\Models;

use CodeIgniter\Model;

class M_Wilayah extends Model
{

    // Tambahkan properti $table dan $primaryKey
    // Ini penting agar CodeIgniter dapat bekerja dengan benar.
    protected $table = 'kota_kab'; 
    protected $primaryKey = 'id_kotakab'; 
    protected $returnType     = 'array';
    
    public function getKotaKab($where = false)
    {
        $builder = $this->db->table('kota_kab');
        if ($where) {
            $builder->where($where);
        }
        return $builder->get()->getResultArray();
    }

    public function getKecamatan($where = false)
    {
        $builder = $this->db->table('kecamatan');
        if ($where) {
            $builder->where($where);
        }
        return $builder->get()->getResultArray();
    }

    public function getKelurahan($where = false)
    {
        $builder = $this->db->table('kelurahan');
        if ($where) {
            $builder->where($where);
        }
        return $builder->get()->getResultArray();
    }
}
?>