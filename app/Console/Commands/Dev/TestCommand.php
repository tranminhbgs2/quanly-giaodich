<?php

namespace App\Console\Commands\Dev;

use App\Helpers\NewRSA;
use App\Models\MxvnewsPost;
use App\Services\Email\MailerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCommand extends Command
{
    protected $mailer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmd:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(MailerService $mailer)
    {
        //$this->test();die();
        $this->test2();die();
        //$this->test3();die();


    }

    private function test3()
    {
        $files = Storage::disk('sftp')->files('INCOMING');
        print_r($files);

    }

    private function test2()
    {
        /*Storage::disk('sftp')->copy(
            'storage/app/public/common/graduated.png',
            'storage/app/public/common/graduated_'. time() .'.png'
        );*/

        $content = Storage::disk('sftp')->get('storage/app/public/common/graduated.png');
        //print_r($content);
        file_put_contents('storage/app/public/sftp/download.png', $content);

        //$files = Storage::disk('sftp')->files('storage/app/public/common/');
        //print_r($files);

    }

    private function test()
    {
        $rsa = new NewRSA();
        $rsa->setPrivateKey('storage/app/public/key/app_core_private_key.pem');
        $rsa->setPublicKey('storage/app/public/key/app_core_public_key.pem');
        //
        $data = [
            'RequestId' => uniqid(),
            'SSCId' => '1202112817000308'
        ];

        echo $data_base64_encode = base64_encode(json_encode($data));
        echo "\n";
        //echo $rsa->sign('eyJSZXF1ZXN0SWQiOiI2Mzc2MjAyNjM2ODU1MTIzMzUiLCJTU0NJZCI6IjEyMDIxMTI4MTcwMDAzMDgifQ==');
        echo $sign = $rsa->sign($data_base64_encode);

        // Call api truy vấn học sinh
        $input = [
            "cmd" =>  "FindStudentById",
            "partnercode" => "SSC1",
            "data" => $data_base64_encode,
            "signature" => $sign
        ];

        $url = 'https://ssc-billgw-sb.payoo.vn/billing/api.json';

        $res = sendRequest($url, $input, 'POST', true, false);

        echo "\n";
        echo '<pre>';
        print_r($res);
        echo '</pre>';


    }
}
