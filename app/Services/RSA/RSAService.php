<?php

namespace App\Services\RSA;

use App\Helpers\NewRSA;

class RSAService
{
    private $rsa;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->rsa = new NewRSA();
        $this->rsa->setPrivateKey('storage/key/app_core_private_key.pem');
        $this->rsa->setPublicKey('storage/key/app_core_public_key.pem');

        //$this->rsa->setPrivateKey('storage/key/api_fsc_private.pem');
        //$this->rsa->setPublicKey('storage/key/api_fsc_public.pem');
    }

    public function sign($data, $code = 'base64')
    {
        return $this->rsa->sign($data);
    }

}
