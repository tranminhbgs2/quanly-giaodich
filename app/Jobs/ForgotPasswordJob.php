<?php

namespace App\Jobs;

use App\Helpers\Constants;
use App\Services\Email\MailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ForgotPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailer;
    protected $to_email;
    protected $subject;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to_email, $subject, $message)
    {
        $this->to_email = $to_email;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailerService $mailer)
    {
        $this->mailer = $mailer;
        if ($this->to_email && $this->subject && $this->message) {
            $type = Constants::EMAIL_TYPE_FORGOT_PASSWORD;
            $params = [
                'email' => $this->to_email,
                'content' => $this->message,
                'url' => Constants::CRM_DOMAIN,
            ];
            $this->mailer->sendSingle($this->to_email, $this->subject, getEmailBody($type, $params));
        }
    }
}
