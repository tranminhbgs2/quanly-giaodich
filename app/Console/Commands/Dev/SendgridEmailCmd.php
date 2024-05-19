<?php

namespace App\Console\Commands\Dev;

use App\Services\Email\SendgridService;
use Illuminate\Console\Command;

class SendgridEmailCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:sendgrid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $sendgrid;

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
    public function handle(SendgridService $sendgridService)
    {
        $star_time = microtime(true) * 1000;

        $this->sendgrid = $sendgridService;

        $subject = 'Mã xác thực OTP';
        $toEmail = 'doan281@gmail.com';
        $message = strval(rand(1000, 9999));

        $result = $this->sendgrid->sendSingle($toEmail, $subject, $message);
        if ($result == 1) {
            echo "\n Gửi mail thành công";
        } else {
            echo "\n Gửi mail KHÔNG thành công";
        }

        echo "\n Time: " . (microtime(true) * 1000 - $star_time);
    }
}
