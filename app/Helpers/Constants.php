<?php

namespace App\Helpers;

class Constants
{
    const CRM_DOMAIN = 'https://crm.dcvinvest.com';

    // Khai báo bảng CSDL
    const TABLE_ANNOUNCEMENT_USER = 'announcement_user';
    const TABLE_ANNOUNCEMENTS = 'announcements';
    const TABLE_BANKS = 'banks';
    const TABLE_CRONTJOBS = 'crontjobs';
    const TABLE_DEPARTMENTS = 'departments';
    const TABLE_DEVICES = 'devices';
    const TABLE_DISTRICTS = 'districts';
    const TABLE_FAILED_JOBS = 'failed_jobs';
    const TABLE_JOBS = 'jobs';
    const TABLE_LOG_ACTIONS = 'log_actions';
    const TABLE_LOG_APIS = 'log_apis';
    const TABLE_LOG_AUTHS = 'log_auths';
    const TABLE_MIGRATIONS = 'migrations';
    const TABLE_PASSWORD_RESETS = 'password_resets';
    const TABLE_POSITIONS = 'positions';
    const TABLE_PROVINCES = 'provinces';
    const TABLE_STUDENTS = 'students';
    const TABLE_USERS = 'users';
    const TABLE_VERSIONS = 'versions';
    const TABLE_WARDS = 'wards';
    const TABLE_TRANSACTION = 'transactions';
    const TABLE_MONEY_COMES_BACK = 'money_comes_back';
    const TABLE_TRANSFERS = 'transfers';
    const TABLE_WITHDRAW_POS = 'withdraw_pos';
    const TABLE_POS = 'pos';
    const TABLE_AGENCY = 'agency';
    const TABLE_HO_KINH_DOANH = 'ho_kinh_doanh';
    const TABLE_CATEGORIES = 'categories';
    const TABLE_BANK_ACCOUNTS = 'bank_accounts';
    const TABLE_CASH_FLOW = 'cash_flow';



    const ADMIN_GROUP = 1; //Khách hàng
    const ACCOUNTANT_GROUP = 2; //Khách hàng
    const CUSTOMER_GROUP = 3; //Khách hàng
    const STAFF_GROUP = 4; //Khách hàng

    const ADMIN_ROLE = [
        0 => 'NORMAL_ADMIN',
        1 => 'SUPER_ADMIN',
        2 => 'ACCOUNTANT_ADMIN'
    ];

    const LANGUAGE_LIST = 'ENGLISH,VIETNAMESE';

    const USER_STATUS_NEW = 0;
    const USER_STATUS_ACTIVE = 1;
    const USER_STATUS_LOCKED = 2;
    const USER_STATUS_DELETED = 3;
    const USER_STATUS_DRAFT = 4;

    const SCHOOL_STATUS_NEW = 0;
    const SCHOOL_STATUS_ACTIVE = 1;
    const SCHOOL_STATUS_LOCKED = 2;

    const PLATFORM = 'ios,iOS,android,Android,web,Web';
    const ACCOUNT_TYPE = 'CUSTOMER,SYSTEM,STAFF';
    const APP_LOCK_STATUS = 'LOCK,UNLOCK';

    const ACCOUNT_TYPE_CUSTOMER = 'CUSTOMER';
    const ACCOUNT_TYPE_SYSTEM = 'SYSTEM';
    const ACCOUNT_TYPE_STAFF = 'STAFF';
    const ACCOUNT_TYPE_AGENCY = 'AGENCY';
    const ACCOUNT_TYPE_ACCOUNTANT = 'ACCOUNTANT';

    const OPT_TYPE = 'FORGOT_PASSWORD,CUSTOMER_REQUEST_DEPOSIT,REQUEST_WITHDRAW,CUSTOMER_VERIFY,CREATE_OTP_PASSWORD,VERIFY_OTP_PASSWORD,CUSTOMER_REGISTER';

    const NOTICE_ACTION_TYPE_ALL = 'ALL';
    const NOTICE_ACTION_TYPE_CUSTOMER_APPROVAL_DEPOSIT = 'CUSTOMER_APPROVAL_DEPOSIT';
    const NOTICE_ACTION_TYPE_CUSTOMER_CREATE_CQG = 'CUSTOMER_CREATE_CQG';
    const NOTICE_ACTION_TYPE_CUSTOMER_CREATED_CQG = 'CUSTOMER_CREATED_CQG';
    const NOTICE_ACTION_TYPE_CUSTOMER_DEPOSIT = 'CUSTOMER_DEPOSIT';
    const NOTICE_ACTION_TYPE_CUSTOMER_NEWS = 'CUSTOMER_NEWS';
    const NOTICE_ACTION_TYPE_CUSTOMER_REGISTERED = 'CUSTOMER_REGISTERED';
    const NOTICE_ACTION_TYPE_CUSTOMER_REQUEST_DEPOSIT = 'CUSTOMER_REQUEST_DEPOSIT';
    const NOTICE_ACTION_TYPE_CUSTOMER_VERIFIED = 'CUSTOMER_VERIFIED';
    const NOTICE_ACTION_TYPE_CUSTOMER_VERIFY = 'CUSTOMER_VERIFY';
    const NOTICE_ACTION_TYPE_CUSTOMER_VERIFY_CANCEL = 'CUSTOMER_VERIFY_CANCEL';
    const NOTICE_ACTION_TYPE_CUSTOMER_WITHDRAW = 'CUSTOMER_WITHDRAW';
    const NOTICE_ACTION_TYPE_DEPOSIT = 'DEPOSIT';
    const NOTICE_ACTION_TYPE_REQUEST_WITHDRAW = 'REQUEST_WITHDRAW';

    const TRANSACTION_STATUS = '-1,0,1,2';

    const SEND_OTP_BY_EMAIL = 'EMAIL';
    const SEND_OTP_BY_FIREBASE = 'FIREBASE';
    const SEND_OTP_BY_SMS = 'SMS';

    const EMAIL_TYPE_FORGOT_PASSWORD = 'FORGOT_PASSWORD';
    const EMAIL_TYPE_RESET_PASSWORD = 'RESET_PASSWORD';
    const EMAIL_TYPE_OTP = 'OTP';
    const EMAIL_TYPE_NOTI = 'NOTI';
    const EMAIL_TYPE_REGISTER = 'REGISTER';
    const EMAIL_CC_DEFAULT = 'doanpv@dcv.vn';

    const MAX_RECEIVED_OTP = 15;
    const OTP_EXPIRATION_MINUTE = 5;

    const BANK_NO = '970423,970418,970418,970436,970415,970403,970407,970405,970429,970406,970414,970416,970434,970440,970441,970437,970443,970432,970431,970428,970426,970425,970422,970409,970412,970419,970438,970448,970430,970452,970408,970427,970454,970449,970439,970433,970442,970457,970421,970424,970458';

    const UPLOAD_VERIFY_USER_PATH = 'public/uploads/verify_user';
    const UPLOAD_AVATAR = 'public/uploads/user_avatar';
    const UPLOAD_LOGO = 'public/uploads/logo';
    const UPLOAD_ICON = 'public/uploads/icon';
    const UPLOAD_FEEDBACK_ATTACK_IMAGE = 'public/uploads/feedback_attach';
    const UPLOAD_SEND_REQUEST_IMAGE = 'public/uploads/send_request';
    const UPLOAD_DCV_ASSET = 'public/uploads/dcv_asset';
    const UPLOAD_DCV_CHECK_IN = 'public/uploads/dcv_check_in';
    const UPLOAD_SLIDE = 'public/uploads/slides';
    const UPLOAD_BANNER = 'public/uploads/banner';

    const MXV_IMAGE_BASE_URL = 'https://mxvnews.com/storage/';

    const SPONSOR_URL = self::CRM_DOMAIN . '/customer-register?ref=';
    const CRM_ADMIN_LOGIN_URL = self::CRM_DOMAIN . '/admin';

    const YOUTUBE_ID_LENGTH = 11;   // Độ dài chuỗi ID của video trên Youtube
    const URL_CQG_DEMO = 'https://mdemo.cqg.com/cqg/desktop/demorequest';

    const REQUEST_WITHDRAW_AMOUNT_MIN = 1000000;    // Số tiền tối thiểu cần rút

    const DEFAULT_POST_IMAGE = 'storage/common/default-no-image.png';    // Ảnh mặc định ở ds tin tức
    const DEFAULT_AVATAR_IMAGE = 'storage/common/user-avatar-male.jpg';    // Ảnh mặc định của KH
    const APP_PUBLIC_KEY_FILE = 'storage/key/app_public.key';           // Key để mã hóa data gửi cho client

    const API_PRIVATE_KEY_FILE = 'key/api_private.key';         // Key để api giải mã data do client gửi lên
    const POSTS_SOURCE_MXV = 'MXV';

    const POSTS_SOURCE_VINANET = 'VINANET';

    const DEFAULT_STUDENT_AVATAR = 'storage/common/graduated.png';      // Ảnh mặc định của học sinh
    const DEFAULT_APP_ICON = 'storage/common/app-default.png';          // Ảnh mặc định của app

    const SCOPE_APP = 'APP';    // Nguồn từ app
    const SCOPE_WEB = 'WEB';    // Nguồn từ web/cms

    const LOGIN_STATUS_SUCCESS = 'SUCCESS';
    const LOGIN_STATUS_FAILED = 'FAILED';
    const LOG_TYPE_LOGIN = 'LOGIN';
    const LOG_TYPE_LOGOUT = 'LOGOUT';



}
