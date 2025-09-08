<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\KoordinatModel;

class MarkerController extends ResourceController
{
    protected $modelName = 'App\Models\KoordinatModel';
    protected $format    = 'json';

    public function update($id = null)
    {
        $input = $this->request->getRawInput();
        
        $data = [
            'id_sumberdata' => $input['id_sumberdata'],
            'nama_kotakab'  => $input['nama_kotakab'],
            'nama_kec'      => $input['nama_kec'],
            'nama_kel'      => $input['nama_kel'],
            'latitude'      => $input['latitude'],
            'longitude'     => $input['longitude'],
        ];

        if ($this->model->update($id, $data)) {
            return $this->respond(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
        } else {
            return $this->fail('Gagal memperbarui data', 400);
        }
    }
}