<?php

namespace App\Services\Socket;

use Carbon\Carbon;
//use ElephantIO\Client;
//use ElephantIO\Engine\SocketIO\Version1X;

class ClientSocketService implements ClientSocketInterface
{
    protected $client;
    protected $url = '//222.252.22.174:8087';

    public function __construct()
    {
        //$this->client = new Client(new Version1X($this->url));
        //$this->client->initialize();
    }

    /**
     * Hàm push đi một thông điệp qua kênh nào và data gì
     *
     * @param $channel
     * @param null $data
     * @return bool
     */
    public function emit($channel, $data=null)
    {
        // TODO: Implement emit() method.
        /*use ElephantIO\Client;
        use ElephantIO\Engine\SocketIO\Version1X;
        $client = new Client(new Version1X('//222.252.22.174:8087'));
        $client->initialize();
        $client->emit('push-update-price', ['type' => 'response-update-price', 'text' => 'Hello There!']);
        $client->close();*/

        echo "\n-- Call me at: " . Carbon::now();

        if ($channel) {
            $this->client->emit($channel, $data);
            $this->client->close();
            return true;
        }
        return false;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if ($this->client) {
            $this->client->close();
        }
    }
}
