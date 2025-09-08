<?php namespace App\Controllers\Api;

use App\Models\KoordinatModel;
use CodeIgniter\API\Controller\ResourceController;

class Koordinat extends ResourceController
{
    protected $model;

    public function __construct()
    {
        $this->model = new KoordinatModel();
    }

    public function update($id = null)
    {
        // Ambil data JSON dari body request
        $json = $this->request->getRawInput();
        $data = $json['json_string'] ?? $json;

        if (empty($data)) {
            return $this->failValidationError('Tidak ada data yang dikirim.');
        }

        // Ambil ID dari URI
        $id = $this->request->getUri()->getSegment(4); // Biasanya segmen ke-4 adalah ID

        // Periksa apakah ID valid
        if (!$id) {
            return $this->fail('ID tidak ditemukan di URL.', 400);
        }

        // Lakukan update data
        if ($this->model->update($id, $data)) {
            return $this->respondUpdated(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
        } else {
            // Jika ada error, kembalikan pesan yang lebih detail (jika ada)
            $errors = $this->model->errors();
            if (!empty($errors)) {
                return $this->failValidationErrors($errors);
            }
            return $this->failServerError('Gagal memperbarui data.');
        }
    }
}