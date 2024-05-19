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
        $this->rsa->setPrivateKey('storage/key/api_private.key');
        $this->rsa->setPublicKey('storage/key/app_public.key');

        //$this->rsa->setPrivateKey('storage/key/api_fsc_private.pem');
        //$this->rsa->setPublicKey('storage/key/api_fsc_public.pem');
    }

    public function sign($data, $code = 'base64')
    {
        return $this->rsa->sign($data);
    }

}
