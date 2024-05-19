<?php

namespace App\Services\Email;

interface EmailInterface
{
    /**
     * Lấy đối tượng mailer
     *
     * @return mixed
     */
    public function getMailer();

    /**
     * Gửi một nội dung mail cho một người
     *
     * @param $toEmail
     * @param $subject
     * @param $message
     * @param $email_cc
     * @param $attachFile
     *
     * @return mixed
     */
    public function sendSingle($toEmail, $subject, $message, $email_cc='', $attachFile=null);

    /**
     * Gửi một nội dung mail cho nhiều người
     *
     * @param $toEmails
     * @param $subject
     * @param $message
     * @param $attachFile
     *
     * @return mixed
     */
    public function sendMultiple(array $toEmails, $subject, $message, $attachFile=null);
}
