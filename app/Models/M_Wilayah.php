<?php 
namespace App\Models;

use CodeIgniter\Model;

class M_Wilayah extends Model
{
    
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