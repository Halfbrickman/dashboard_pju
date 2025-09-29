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
    public function getKecamatanByKotakabId($idKotakab)
    {
        $builder = $this->db->table('kecamatan');
        $builder->where('id_kotakab', $idKotakab);
        return $builder->get()->getResultArray();
    }

    public function getKelurahanByKecId($idKec)
    {
        $builder = $this->db->table('kelurahan');
        $builder->where('id_kec', $idKec);
        return $builder->get()->getResultArray();
    }

    public function getKotaKabById(int $id)
    {
        return $this->db->table('kota_kab')->where('id_kotakab', $id)->get(1)->getRowArray();
    }

    public function getKecamatanById(int $id)
    {
        return $this->db->table('kecamatan')->where('id_kec', $id)->get(1)->getRowArray();
    }

    public function getKelurahanById(int $id)
    {
        return $this->db->table('kelurahan')->where('id_kel', $id)->get(1)->getRowArray();
    }

}
?>