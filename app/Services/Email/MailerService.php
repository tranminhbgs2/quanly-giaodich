<?php

namespace App\Services\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService implements EmailInterface
{
    protected $mailer = null;
    protected $debug = false;

    public function __construct($debug=true)
    {
        $this->debug = $debug;
        $this->mailServerSettings();
    }

    public function getMailer()
    {
        // TODO: Implement getMailer() method.
        return $this->mailer;
    }

    /**
     * @inheritDoc
     */
    public function sendSingle($toEmail, $subject, $message, $ccEmail = null, $attachFile = null)
    {
        // TODO: Implement sendSingle() method.
        if (!empty($this->mailer)) {
            try {
                // Đặt mức độ debug là 0 để tắt ghi log chi tiết
                $this->mailer->SMTPDebug = 0;
                
                $this->mailer->addAddress($toEmail);
                if($ccEmail){
                    $this->mailer->addCC($ccEmail);
                }
                $this->mailer->Subject = $subject;
                $this->mailer->Body = $message;
                if ($attachFile) {
                    $this->mailer->addAttachment($attachFile);
                }
                $this->mailer->send();

                $this->mailer->clearAllRecipients();
                $this->mailer->smtpClose();

                return 1;

            } catch (Exception $e) {
                return -2;
            }
        } else {
            return -3;
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMultiple(array $toEmails, $subject, $message, $attachFile = null)
    {
        // TODO: Implement sendMultiple() method.
    }

    /**
     * @return null|\PHPMailer\PHPMailer\PHPMailer
     */
    private function mailServerSettings()
    {
        $this->mailer = new PHPMailer(false);

        $this->mailer->SMTPDebug = ($this->debug) ? 2 : 0;
        $this->mailer->isSMTP();
        $this->mailer->CharSet = 'utf-8';
        $this->mailer->SMTPKeepAlive = true;
        $this->mailer->Timeout = 30;
        $this->mailer->getSMTPInstance()->Timelimit = 30;
        $this->mailer->Host = config('mail.mailers.smtp.host');
        $this->mailer->SMTPAuth = true;
        $this->mailer->SMTPSecure = config('mail.mailers.smtp.encryption');
        $this->mailer->Username = config('mail.mailers.smtp.username');
        $this->mailer->Password = config('mail.mailers.smtp.password');
        $this->mailer->Port = config('mail.mailers.smtp.port');
        $this->mailer->SMTPOptions = [
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        ];
        $this->mailer->isHTML(true);
        $this->mailer->setFrom(config('mail.from.address'), config('mail.from.name'));
        //$this->mailer->addCC('doanpv@dcv.vn');
        $this->mailer->Subject = '[DCV - CÔNG ĐOÀN] Chúc mừng sinh nhật';

        return $this->mailer;
    }
}
