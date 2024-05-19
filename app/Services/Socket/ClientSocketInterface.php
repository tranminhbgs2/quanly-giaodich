<?php

namespace App\Services\Socket;

interface ClientSocketInterface
{
    public function emit($channel, $data=null);
}
