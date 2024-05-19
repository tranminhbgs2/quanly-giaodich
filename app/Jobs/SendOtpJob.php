<?php

namespace App\Jobs;

use App\Helpers\Constants;
use App\Services\Email\MailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $method;

    protected $mailer;

    protected $to_email;
    protected $subject;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($method, $params)
    {
        $this->method = $method;

        $this->to_email = isset($params['to_email']) ? $params['to_email'] : null;
        $this->subject = isset($params['subject']) ? $params['subject'] : null;
        $this->message = isset($params['message']) ? $params['message'] : null;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailerService $mailer)
    {
        $this->mailer = $mailer;

        switch ($this->method) {
            case Constants::SEND_OTP_BY_SMS: break;
            case Constants::SEND_OTP_BY_EMAIL:
                if ($this->to_email && $this->subject && $this->message) {
                    $type = Constants::EMAIL_TYPE_OTP;
                    $params = [
                        'email' => $this->to_email,
                        'content' => $this->message,
                        'url' => Constants::CRM_DOMAIN,
                    ];
                    $this->mailer->sendSingle($this->to_email, $this->subject, getEmailBody($type, $params));
                }
                break;
        }



    }
}
