<?php

namespace App\Controllers;

class CsrfController extends BaseController
{
    public function token()
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setJSON(['token' => csrf_hash()]);
    }
}
