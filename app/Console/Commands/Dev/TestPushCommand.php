<?php

namespace App\Console\Commands\Dev;

use App\Helpers\Constants;
use App\Services\Email\MailerService;
use App\Services\Email\SendgridService;
use App\Services\Notification\FcmService;
use App\Services\Notification\NotificationInterface;
use App\Services\Socket\ClientSocketService;
use Illuminate\Console\Command;

// use ElephantIO\Client;
// use ElephantIO\Engine\SocketIO\Version1X;

class TestPushCommand extends Command
{
    protected $mailer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(FcmService $service, SendgridService $sendgridService)
    {
        /*$this->service = $service;
        //
        $data = [
            'title' => 'Test push',
            'body' => 'Test push',
            'image'
        ];

        $registration_ids = ['fTCA7M_dRGCJcpKjRx1Jpu:APA91bEskrvEJHaTvLyBG-oJHs4nYoI3Vd-QwU0w-rPFQSfa0eaux0_H8S2devwhZiDywFlL5ncqU9jfVRG85lFrbgB-QUWVTByfjaBzBGZGC-4AxRcVv7grEg0JZl6XCs2Uj8cuJzso'];

        $result = $this->service->multiplePusher(
            Constants::ACCOUNT_TYPE_CUSTOMER,
            $data,
            $registration_ids
        );

        print_r($result);*/

        // Test push socket
        /*$client = new Client(new Version1X('//222.252.22.174:8087'));
        $client->initialize();
        // send message to connected clients
        $client->emit('push-update-price', ['type' => 'response-update-price', 'text' => 'Hello There!']);
        $client->close();*/

        // $socket->emit('push-update-price', [
        //     'type' => 'push-update-price',
        //     'text' => 'Hello DoanPV!'
        // ]);

        $mailer = new MailerService(true);
        $result = null;
        //$result = $mailer->sendSingle('tranvanminh30398@gmail.com', 'Test mail', 'Gửi mail cho test');
        $result = $mailer->sendSingle('uyento9090@gmail.com', 'Test mail', 'Gửi mail cho test');
        if($result == 1){
            echo 'Thành công';
        } else {
            echo 'có lỗi xảy ra: '.$result;
        }

        //$sendgridService->sendSingle('uyento9090@gmail.com', 'Test gửi mail', 'Test gửi mail');


    }
}
