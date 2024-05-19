<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPMailer\PHPMailer;

class SendMailWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $mail_from;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $mail_from = null)
    {
        $this->data = $data;
        $this->mail_from = $mail_from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $message = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
         <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
            <tbody>
               <tr>
                  <td>
                     <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                        <tbody>
                           <tr>
                              <td bgcolor="#074B80" style="font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #fe9703">DCV Invest - Thông tin yêu cầu rút tiền</td>
                           </tr>
                           <tr>
                              <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                              <p>Hi <strong>Administrator</strong>!</p>
                              <p>Vừa có một Khách hàng gửi yêu cầu rút tiền.</p>
                              <p>Thông tin Khách hàng yêu cầu rút tiền:</p>
                                 <ul>
                                       <li>Email Khách hàng: '.$this->data['customer_email'].'</li>
                                       <li>Số tài khoản giao dịch: '.$this->data['account_number'].'</li>
                                       <li>Tên tài khoản giao dịch: '.$this->data['account_name'].'</li>
                                       <li>Số tiền cần rút: '.number_format($this->data['amount'], 2, '.', ',').' VND</li>
                                       <li>Loại yêu cầu: Yêu cầu rút tiền</li>
                                       <li>Ghi chú: '.$this->data['comment'].'</li>
                                    </ul>
                                    <p>Link đăng nhập CMS <a href="http://transaction.dcvinvest.com/" >tại đây</a>.</p>
                                    <br>
                                    <p>Trân trọng thông báo!</p>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                     </tr>
                  </tbody>
               </table>
            <div class="yj6qo"></div>
         <div class="adL"></div>
      </div>';
                                    
        $mail             = new PHPMailer\PHPMailer(); // create a n
        $mail->SMTPDebug  = 0; // debugging: 1 = errors and messages, 2 = messages only
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = "smtp.yandex.com";
        $mail->Port       = 465; // or 587
        $mail->IsHTML(true);
        $mail->Username = "noreply@dcvinvest.com";
        $mail->Password = "Dcvinvest@123#";
        $mail->SetFrom("noreply@dcvinvest.com", 'DCVINVEST');
        $mail->AddAddress('dcvinvest.bod@dcv.vn', "Receiver Name");
      //   $mail->AddAddress('minhtv1@dcv.vn', "Receiver Name");
        $mail->addCC('ketoan@dcv.vn');
        $mail->Subject = "Thông báo yêu cầu rút tiền DCVINVEST";
        $mail->Body    = $message;
        if ($mail->send()) {
            $error_code = 0;
            $mess = 'Mã OTP đã gửi đến Mail của bạn. Vui lòng kiểm tra hộp thư!';
        } else {
            $error_code = 1;
            $mess = 'Gửi mã xác nhận thất bại. Vui lòng thử lại!';
        }
    }
}
