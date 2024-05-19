<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use PHPMailer\PHPMailer;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $otp;
    protected $receiver_email;
    protected $action;
    protected $mail_from;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($otp, $receiver_email, $action, $mail_from = null)
    {
        $this->otp = $otp;
        $this->receiver_email = $receiver_email;
        $this->action = $action;
        $this->mail_from = $mail_from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // echo "\n send mail";
        if($this->action != null) {
            if($this->action == 'OWNER'){
                $message = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                <tbody>
                                <tr>
                                    <td bgcolor="#28A745" style="font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #fe9703">Thông báo yêu cầu rút tiền</td>
                                </tr>
                                <tr>
                                    <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                        <p>Hi <strong>'.$this->receiver_email.'</strong>!</p>
                                        <p>Chúng tôi đã nhận được yêu cầu rút tiền về tài khoản cho khách hàng từ tài khoản hệ thống '.$this->mail_from.'!</p>
                                        <p>Nếu yêu cầu này chưa được duyệt vui lòng liên hệ với admin!</p>
                                        <br>
                                        <p>Trân trọng thông báo!</p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p style="text-align:center;color:#aaabbb;font-size:9px">2020 © By DCVINVEST.</p>
                            <br>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="yj6qo"></div>
                <div class="adL"></div>
            </div>';
            } else {
                $message = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                <tbody>
                                <tr>
                                    <td bgcolor="#28A745" style="font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #fe9703">Mã xác nhận yêu cầu rút tiền</td>
                                </tr>
                                <tr>
                                    <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                        <p>Hi <strong>'.$this->receiver_email.'</strong>!</p>
                                        <p>Chúng tôi đã nhận được yêu cầu rút tiền về tài khoản cho khách hàng của bạn!</p>
                                        <p>Mã xác nhận của bạn là: <strong>'.$this->otp.'</strong></p>
                                        <p>Bạn vui lòng điền mã xác nhận vào form rút tiền!</p>
                                        <br>
                                        <p>Trân trọng thông báo!</p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p style="text-align:center;color:#aaabbb;font-size:9px">2020 © By DCVINVEST.</p>
                            <br>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="yj6qo"></div>
                <div class="adL"></div>
                </div>';
            }
        }else {
            $message = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
            <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                <tbody>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                            <tbody>
                            <tr>
                                <td bgcolor="#28A745" style="font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #fe9703">Mã xác nhận</td>
                            </tr>
                            <tr>
                                <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                    <p>Hi <strong>'.$this->receiver_email.'</strong>!</p>
                                    <p>Chúng tôi đã nhận được yêu cầu đăng nhập của bạn!</p>
                                    <p>Mã xác nhận của bạn là: <strong>'.$this->otp.'</strong></p>
                                    <p>Bạn vui lòng điền mã xác nhận vào form đăng nhập!</p>
                                    <br>
                                    <p>Trân trọng thông báo!</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p style="text-align:center;color:#aaabbb;font-size:9px">2020 © By DCVINVEST.</p>
                        <br>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="yj6qo"></div>
            <div class="adL"></div>
        </div>';
        }

        $mail             = new PHPMailer\PHPMailer(); // create a n
        $mail->SMTPDebug  = 0; // debugging: 1 = errors and messages, 2 = messages only
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = env('MAIL_HOST');
        $mail->Port       = env('MAIL_PORT'); // or 587
        $mail->IsHTML(true);
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SetFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        if($this->action != null) {
            if($this->action == 'OWNER'){
                $mail->AddAddress($this->receiver_email, "Receiver Name");
                $mail->addCC('ketoan@dcv.vn');
                $mail->Subject = "Thông báo yêu cầu rút tiền DCVINVEST";
                $mail->Body    = $message;
            } else {
                $mail->AddAddress($this->receiver_email, "Receiver Name");
                $mail->Subject = "Mã xác nhận yêu cầu rút tiền DCVINVEST";
                $mail->Body    = $message;
            }
        } else {
            $mail->Subject = "Mã xác nhận";
            $mail->Body    = $message;
            $mail->AddAddress($this->receiver_email, "Receiver Name");
        }
        if ($mail->send()) {
            $error_code = 0;
            $mess = 'Mã OTP đã gửi đến Mail của bạn. Vui lòng kiểm tra hộp thư!';
        } else {
            $error_code = 1;
            $mess = 'Gửi mã xác nhận thất bại. Vui lòng thử lại!';
        }
    }
}
