<?php

namespace App\Repositories;

use App\Models\Otp;
use Carbon\Carbon;

class OtpRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
        //

    }

    /**
     * Hàm check số lần gửi OTP
     *
     * @param $receiver_name
     * @param $type
     * @return mixed
     */
    public function checkCountSent($receiver_name, $type)
    {
        $otp = Otp::where('receiver_name', $receiver_name)
            ->where('type', $type)
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->where('created_at', '<=', date('Y-m-d 23:59:59'))
            ->count();

        return $otp;
    }

    /**
     * Hàm thêm mới OTP
     *
     * @param $params
     * @param false $check
     * @return bool
     */
    public function store($params, $check=false)
    {
        $save = Otp::create([
            'type' => isset($params['type']) ? $params['type'] : null,
            'code' => isset($params['code']) ? $params['code'] : null,
            'method' => isset($params['method']) ? $params['method'] : null,
            'receiver_name' => isset($params['receiver_name']) ? $params['receiver_name'] : null,
            'expiration_date' => isset($params['expiration_date']) ? $params['expiration_date'] : null,
            'status' => isset($params['status']) ? $params['status'] : 0
        ]);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hàm check xem OTP gửi lên có hợp lệ không (đúng và trong thời gian còn hiệu lực không)
     *
     * @param $type
     * @param $otp
     * @param $receiver_name
     * @return bool
     */
    public function verifyOtp($type, $otp, $receiver_name)
    {
        if ($type && $otp && $receiver_name) {
            $otp = Otp::where('code', $otp)
                ->where('type', $type)
                ->where('receiver_name', $receiver_name)
                ->where('expiration_date', '>=', Carbon::now())
                ->where('status', 0)
                ->orderByDesc('id')
                ->first();

            if ($otp) {
                $otp->verified_at = Carbon::now();
                $otp->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Hàm check OTP trước khi update mk
     *
     * @param $otp
     * @param $receiver_name
     * @return bool
     */
    public function verifyOtpBeforeUpdatePassword($otp, $receiver_name)
    {
        if ($otp && $receiver_name) {
            $otp = Otp::where('code', $otp)
                ->where('receiver_name', $receiver_name)
                ->where('expiration_date', '>=', Carbon::now())
                ->where('status', 0)
                ->whereNotNull('verified_at')
                ->orderByDesc('id')
                ->first();

            if ($otp) {
                $otp->status = 1;
                $otp->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
