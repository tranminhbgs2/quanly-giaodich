<?php

/**
 * Deep link: appdcvinvest://screen/posts/4806
 * limit_feedback_per_day: số lần gửi Feedback tối đa trong 1 ngày
 * company_info: thông tin liên hệ công ty
 * customize.data_mode = TEST/REAL
 */
return [
    'data_mode' => 'REAL',
    'zalo_ai' => [
        'api_key_1' => 'UvYfbJW8gutRO3J2OFDvNmbwnr4dDHWI',
        'api_key_2' => 'IJPGpXo7WT1Fu82ZB7glld111xSTSbBs',
        'api_key_3' => 'RWyTtZJuimUlHJhKXwehS27mTtXrhtkA',
    ],

    'posts' => [
        'deep_link' => 'appdcvinvest://screen/posts/',
    ],

    'limit_feedback_per_day' => 3,

    'limit_sendrequest_per_day' => 3,

    'company_info' => [
        'name' => 'CÔNG TY CP TƯ VẤN ĐẦU TƯ DCV',
        'address' => 'Tầng L2, Tòa nhà Mỹ Sơn, Số 62 Nguyễn Huy Tưởng, P.Thanh Xuân Trung, Q.Thanh Xuân, TP.Hà Nội',
        'email' => 'sale@dcv.vn',
        'website' => 'https://dcvinvest.com',
        'hotline' => '024.9999.8669',
        'hotline_call' => '02499998669',
        'office_day' => 'Thứ 2 - Thứ 6',
        'office_hours' => '08:00 - 18:00',
        'office_sat_time' => '10:00 - 14:00',
        'office_sun_time' => '09:00 - 12:00',
    ],

    'sendgrid' => [
        'api_key' => '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',
        'template_id' => 'd-7e25e5faf88c4bffa3821cbf900d339a'
    ],

    /* Type bỏ qua check giải mã OTP */
    'otp' => [
        'skip_decrypt' => [
            'FORGOT_PASSWORD',
            'CREATE_OTP_PASSWORD'
        ]
    ],

    'logs' => [
        'api' => env('SAVE_API_LOG', false),
        'authen' => env('SAVE_AUTHEN_LOG', false),
    ],


];
