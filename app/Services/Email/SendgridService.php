<?php

namespace App\Services\Email;

class SendgridService implements EmailInterface
{

    /**
     * @inheritDoc
     */
    public function getMailer()
    {
        // TODO: Implement getMailer() method.
    }

    /**
     * @inheritDoc
     */
    public function sendSingle($toEmail, $subject, $message, $email_cc = '', $attachFile = null)
    {
        // TODO: Implement sendSingle() method.
        $email = new \SendGrid\Mail\Mail();

        $email->setFrom("support@dcv.vn", "DCVINVEST.COM");
        $email->addTo($toEmail, $toEmail);

        //$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent("text/html", $message);

        $email->addDynamicTemplateData('subject', $subject);
        $email->addDynamicTemplateData("email", $toEmail);
        $email->addDynamicTemplateData("content", $message);

        $email->setTemplateId(config('customize.sendgrid.template_id'));

        $sendgrid = new \SendGrid(config('customize.sendgrid.api_key'));

        try {
            $response = $sendgrid->send($email);
            //print $response->statusCode() . "\n";
            //print_r($response->headers());
            //print $response->body() . "\n";

            if ($response->statusCode() == 202) {
                //echo "\n Gửi mail thành công";
                return 1;
            } else {
                //echo "\n" . $response->statusCode();
                return 2;
            }
        } catch (\Exception $e) {
            //echo 'Caught exception: '. $e->getMessage() ."\n";
            return -1;
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMultiple(array $toEmails, $subject, $message, $attachFile = null)
    {
        // TODO: Implement sendMultiple() method.
    }
}
