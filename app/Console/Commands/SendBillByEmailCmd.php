<?php

namespace App\Console\Commands;

use App\Models\Fee;
use App\Services\Email\MailerService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBillByEmailCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-bill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $mailerService;

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
     * @return int
     */
    public function handle(MailerService $mailerService)
    {
        $fees = Fee::select(['id', 'data_pdf', 'pdf_file_path', 'total_payment', 'lockup_code', 'paid_at'])
            ->whereNotNull('data_pdf')
            ->whereNotNull('pdf_file_path')
            ->whereNull('send_bill_at')
            ->take(10)
            ->get();

        // Duyệt mảng dữ liệu bill để gửi mail
        if ($fees) {
            foreach ($fees as $key => $val) {
                $message = '<strong>Dear Anh/Chị,</strong><br>';
                $message .= '<p>Hệ thống SSC xin gửi đến quý Anh/chị thông tin học phí và phụ phí như trong file đính kèm.</p>';
                $message .= '<p>Thông tin hóa đơn:</p>';
                $message .= '<p>- Tổng số tiền: '.number_format($val->total_payment, 0, ',', '.').' VNĐ</p>';
                $message .= '<p>- Mã tra cứu: '.$val->lockup_code.'</p>';
                $message .= '<p>- Ngày tạo hóa đơn: '.$val->paid_at.'</p><br>';
                $message .= '<p>Trân trọng thông báo!</p>';
                //
                $attachFile = str_replace(
                    ['http://localhost:8000/storage', 'http://localhost/storage', 'http://ssc.dcv.vn/storage'],
                    ['', '', ''],
                    $val->pdf_file_path
                );
                $attachFile = storage_path('app/public/'.$attachFile);
                //
                $mailerService->sendSingle('bill@dcv.vn', 'Thông báo học phí, phụ phí', $message, 'doan281@gmail.com', $attachFile);
                //
                $val->send_bill_at = Carbon::now();
                $val->save();
            }
        }
        echo "\nCo tat ca so ban ghi: " . count($fees) . "\n";
    }
}
