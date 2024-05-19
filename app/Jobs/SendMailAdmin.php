<?php

namespace App\Jobs;

use App\Helpers\Constants;
use App\Models\NotificationWait;
use App\Services\Email\MailerService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SendMailAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $waits;
    protected $account_type;
    protected $title;
    protected $mailer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($waits)
    {
        $this->waits = $waits;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailerService $mailer)
    {
        echo "\n- Bắt đầu xử lý queue...";
        $this->mailer = $mailer;

        echo "\n - Queue bắt đầu được gọi và xử lý gửi mail.";
        $wait_ids = [];
        $wait_log = [];
        foreach($this->waits as $item){
            echo "\n - Loai Yeu cau: ". $item->type;
            echo "\n - dang xu ly gui notification..." . $item->id;

            $mess = $item->data;
            switch($item->type){
                case 'CUSTOMER_VERIFY':
                    $title = 'Yêu cầu xác minh tài khoản';
                    break;
                case 'CUSTOMER_VERIFIED':
                    $title = 'Yêu cầu xác minh tài khoản';

                    break;
                case 'CUSTOMER_VERIFY_CANCEL':
                    $title = 'Yêu cầu xác minh tài khoản';
                    break;
                case 'CUSTOMER_REGISTERED':
                    $title = 'Khách hàng đăng ký tài khoản';
                    break;
                case 'REQUEST_WITHDRAW':
                    $title = 'Yêu cầu rút tiền';
                    break;
                case 'CUSTOMER_WITHDRAW':
                    $title = 'Yêu cầu rút tiền';
                    break;
                case 'CUSTOMER_WITHDRAW_CANCEL':
                    $title = 'Yêu cầu rút tiền';
                    break;
                case 'CUSTOMER_DEPOSIT':
                    if($item->level == Constants::ACCOUNT_TYPE_ADMIN){
                        $title = 'Khách hàng nạp tiền qua VNPAY';
                    } else{
                        $title = 'Nạp tiền qua VNPAY';
                    }
                    break;
                case 'CUSTOMER_REQUEST_DEPOSIT':
                    $title = 'Yêu cầu nạp tiền đầu tư';
                    break;
                case 'CUSTOMER_DEPOSIT':
                    $title = 'Khách hàng nạp tiền thành công';
                    break;
                case 'CUSTOMER_APPROVAL_DEPOSIT':
                    $title = 'Yêu cầu nạp tiền đầu tư';
                    break;
                case 'CUSTOMER_CREATE_CQG':
                    $title = 'Yêu cầu mở tài khoản đầu tư';
                    break;
                case 'CUSTOMER_CREATED_CQG':
                    $title = 'Yêu cầu mở tài khoản đầu tư';
                    break;
            }
            echo "\n - Tieu de: " . $title;
            if(in_array($item->type, ['CUSTOMER_VERIFY','CUSTOMER_REGISTERED','CUSTOMER_CREATE_CQG','CUSTOMER_DEPOSIT']) && $item->level == 'ADMIN'){
                echo "\n - Sale";
                $email = 'sale@dcv.vn';
                $email = 'minhtv1@dcv.vn';
                $email_cc = '';
                $level = 'ADMIN';
            } elseif(in_array($item->type, ['CUSTOMER_VERIFIED','CUSTOMER_VERIFY_CANCEL','CUSTOMER_CREATED_CQG','CUSTOMER_REQUEST_DEPOSIT','CUSTOMER_APPROVAL_DEPOSIT', 'CUSTOMER_APPROVAL_DEPOSIT','CUSTOMER_DEPOSIT','CUSTOMER_WITHDRAW','CUSTOMER_WITHDRAW_CANCEL']) && $item->level == 'CUSTOMER'){
                echo "\n - KH";
                $email = $item->customer->email;
                $level = 'CUSTOMER';
                $email_cc = '';
            } elseif(in_array($item->type, ['REQUEST_WITHDRAW', 'CUSTOMER_REQUEST_DEPOSIT']) && $item->level == 'ADMIN'){
                echo "\n - Accounting";
                $email = 'ketoan@dcv.vn';
                $level = 'ADMIN';
                $email_cc = 'sale@dcv.vn';
            }
            $wait_ids[] = $item->id;
            $wait_log[] = [
                'wait_id' => $item->id,
                'level' => $level,
                'type' => $item->type,
                'record_id' => $item->record_id,
                'user_id' => $item->user_id,
                'admin_id' => $item->admin_id,
                'customer_id' => $item->customer_id,
                'title' => $item->title,
                'body' => $item->body,
                'data' => $item->data,
                'platform' => $item->platform,
                'channel' => $item->channel,
                'status' => $item->status,
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            if ($email && $title && $mess) {
                $type = Constants::EMAIL_TYPE_NOTI;
                $params = [
                    'title' => $title,
                    'content' => $mess
                ];
                $message = getEmailBody($type, $params);
                $send_mail = $this->mailer->sendSingle($email, $title, $message, $email_cc);
                if ($send_mail == 1) {
                    // $error_code = 0;
                    echo "\n - Bat dau cap nhat trang thai 1->2.";
                    if (count($wait_ids) > 0) {
                        NotificationWait::whereIn('id',$wait_ids)
                            ->update([
                                'email_status' => 2,
                                'updated_at' => Carbon::now()
                            ]);
                    }
                    echo "\n -> Ket thuc cap nhat trang thai.";
                    echo "\n - Gui mail thanh cong cho: ." . $email;
                    // $mess = 'Mã OTP đã gửi đến Mail của bạn. Vui lòng kiểm tra hộp thư!';
                } else {
                    // $error_code = 1;
                    echo "\n - Gui mail khong thanh cong:. " . $send_mail;
                    // $mess = 'Gửi mã xác nhận thất bại. Vui lòng thử lại!';
                }
            } else {
                echo "\n - Mail không thể gửi.";
            }
            echo "\n - Ket thuc gui mail.\n";
        }

        // Lưu sang bảng log
        if (count($wait_log) > 0) {
            DB::table(Constants::TABLE_NOTIFICATION_LOGS)->insert($wait_log);
        }

    }
}
