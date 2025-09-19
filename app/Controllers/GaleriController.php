<?php

namespace App\Controllers;

use App\Models\M_photo;

class GaleriController extends BaseController
{
    public function index()
    {
        $photoModel = new M_photo();

        $data['photos'] = $photoModel->findAll();

        return view('Template/header')
            . view('Template/sidebar')
            . view('galeri/galeri', $data)
            . view('Template/footer');
    }
}