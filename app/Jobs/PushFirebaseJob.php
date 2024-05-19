<?php

namespace App\Jobs;

use App\Helpers\Constants;
use App\Models\NotificationWait;
use App\Services\Notification\FcmService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class PushFirebaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $waits;           // Mảng noti cần gửi
    protected $device_tokens;   // Mảng token thiết bị nhận noti
    protected $fcmService;      // Đối tượng firebase xử lý gửi tin
    protected $receiver_type;   // Nhóm user nhận noti: ADMIN / CUSTOMER

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($waits, $device_tokens, $receiver_type)
    {
        $this->waits = $waits;
        $this->device_tokens = $device_tokens;
        $this->receiver_type = $receiver_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
        echo "\n Queue bắt đầu được gọi và xử lý: " . $this->receiver_type;

        if ($this->waits && $this->device_tokens) {
            $wait_ids = [];
            $wait_log = [];
            foreach ($this->waits as $wait) {
                echo "\n - Đang xử lý gửi notification..." . $wait->id;
                // Lưu mảng ID wait để cập nhật trạng thái đã gửi.
                $wait_ids[] = $wait->id;
                $wait_log[] = [
                    'wait_id' => $wait->id,
                    'level' => $this->receiver_type,
                    'type' => $wait->type,
                    'record_id' => $wait->record_id,
                    'user_id' => $wait->user_id,
                    'admin_id' => $wait->admin_id,
                    'customer_id' => $wait->customer_id,
                    'title' => $wait->title,
                    'body' => $wait->body,
                    'data' => $wait->data,
                    'platform' => $wait->platform,
                    'channel' => $wait->channel,
                    'status' => $wait->status,
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                $data = [
                    'title' => $wait->title,
                    'body' => $wait->body,
                    'image' => null,
                    'action_type' => $wait->type,
                    'record_id' => intval($wait->record_id),
                    'redirect_to' => null
                ];
                // Bắn một nội dung cho từng admin
                $result = $this->fcmService->multiplePusher($this->receiver_type, $data, $this->device_tokens);
                //echo "\n";
                //print_r($result);
                //echo "\n -> Kết thúc xử lý gửi notification..." . $wait->id;

                echo "\n - Bắt đầu cập nhật trạng thái 1->2.";
                if (count($wait_ids) > 0) {
                    NotificationWait::whereIn('id', $wait_ids)
                        ->update([
                            'status' => 2,
                            'updated_at' => Carbon::now()
                        ]);
                }
                echo "\n -> Kết thúc cập nhật trạng thái.";
            }

            // Lưu sang bảng log
            if (count($wait_log) > 0) {
                DB::table(Constants::TABLE_NOTIFICATION_LOGS)->insert($wait_log);
            }

        } else {
            echo "\n - Không có thông tin nào cần gửi thông báo.";
        }

        echo "\n Kết thúc Queue. \n";
    }
}
