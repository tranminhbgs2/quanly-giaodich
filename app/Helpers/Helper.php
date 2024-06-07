<?php

use App\Helpers\Constants;
use App\Models\LogAction;
use App\Models\LogActionModel;
use App\Models\LogLoginModel;
use Illuminate\Support\Facades\Auth;

if (!function_exists('includeRouteFiles')) {

    /**
     * Loops through a folder and requires all PHP files
     * Searches sub-directories as well.
     *
     * @param $folder
     */
    function includeRouteFiles($folder)
    {
        try {
            $rdi = new recursiveDirectoryIterator($folder);
            $it = new recursiveIteratorIterator($rdi);

            while ($it->valid()) {
                if (!$it->isDot() && $it->isFile() && $it->isReadable() && $it->current()->getExtension() === 'php') {
                    require $it->key();
                }

                $it->next();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

if (!function_exists('saveLogToFile')) {
    function saveLogToFile($content)
    {
        if (!in_array(gettype($content), ['String', 'string'])) {
            $content = json_encode($content);
        }
        $content = PHP_EOL . date('d/m/Y H:i:s') . PHP_EOL . $content . PHP_EOL;
        $fields_file = storage_path('app/public/transfers/transfer_log_' . date('Y_m') . '.txt');
        try {
            $file = @fopen($fields_file, "a") or die("Unable to open file!");
            @fclose($file);
            @file_put_contents($fields_file, $content, FILE_APPEND | LOCK_EX);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('addLogAction')) {
    function addLogAction($action, $description, $model, $table, $actor_id = null, $actor_name = null, $record_id = null, $ip_address = null)
    {
        LogAction::create([
            'actor_id' => $actor_id,
            'actor_name' => $actor_name,
            'action' => $action,
            'description' => $description,
            'model' => $model,
            'table' => $table,
            'record_id' => $record_id,
            'ip_address' => $ip_address
        ]);
        return true;
    }
}

if (!function_exists('translateKeyWord')) {
    function translateKeyWord($keyWord)
    {
        if (empty($keyWord)) {
            return $keyWord;
        } else {
            return str_replace(['%'], ['\%'], $keyWord);
        }
    }
}

if (!function_exists('ci_random_string')) {
    /**
     * Create a "Random" String
     *
     * @param string    type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
     * @param int    number of characters
     * @return    string
     */
    function ci_random_string($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }
}

if (!function_exists('getEmailBody')) {
    /**
     * Hàm lấy body để gửi mail cho admin và khách hàng
     *
     * @param string $type
     * @param array $params
     *
     * @return string
     */
    function getEmailBody($type = 'FORGOT_PASSWORD', $params = array())
    {
        $body = '';
        $fullname = isset($params['fullname']) ? $params['fullname'] : null;
        $title = isset($params['title']) ? $params['title'] : null;
        $mobile = isset($params['mobile']) ? $params['mobile'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $content = isset($params['content']) ? $params['content'] : null;
        $address = isset($params['address']) ? $params['address'] : null;
        $company_name = isset($params['company_name']) ? $params['company_name'] : null;
        $city = isset($params['city']) ? $params['city'] : null;
        $url = isset($params['url']) ? $params['url'] : null;

        switch ($type) {
            case Constants::EMAIL_TYPE_FORGOT_PASSWORD:
                $body = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                        <tbody>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                    <tbody>
                                    <tr>
                                        <td bgcolor="#074B80" style="background-color: #1dabe3; font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #2b57a4">SSC-EDUCATION - Yêu cầu lấy lại mật khẩu</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                            <p>Xin chào, <strong>' . $email . '</strong>!</p>
                                            <p>Hệ thống DCVInvest gửi Bạn thông tin mật khẩu.</p>
                                            <p>Mật khẩu mới: ' . $content . '</p>
                                            <p>Link đăng nhập <a href=' . $url . ' > tại đây</a>.</p>
                                            <br>
                                            <p>Trân trọng thông báo!</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p style="text-align:center;color:#aaabbb;font-size:9pt">2021 © By SSC-EDUCATION</p>
                                <br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="yj6qo"></div>
                    <div class="adL"></div>
                </div>
                <div style ="display:none"><img src=""></div>';
                break;
            case Constants::EMAIL_TYPE_RESET_PASSWORD:
                $body = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                        <tbody>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                    <tbody>
                                    <tr>
                                        <td bgcolor="#074B80" style="background-color: #1dabe3; font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #2b57a4">THANG-CREDIT - Thiết lập lại mật khẩu</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                            <p>Xin chào, <strong>' . $email . '</strong>!</p>
                                            <p>Hệ thống Thắng Credit gửi Bạn thông tin mật khẩu.</p>
                                            <p>Mật khẩu mới: ' . $content . '</p>
                                            <br>
                                            <p>Trân trọng thông báo!</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p style="text-align:center;color:#aaabbb;font-size:9pt">2021 © By THANG-CREDIT</p>
                                <br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="yj6qo"></div>
                    <div class="adL"></div>
                </div>
                <div style ="display:none"><img src=""></div>';
                break;
            case Constants::EMAIL_TYPE_OTP:
                $body = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                        <tbody>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                    <tbody>
                                    <tr>
                                        <td bgcolor="#074B80" style="background-color: #1dabe3; font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #2b57a4">SSC-EDUCATION - Mã xác thực OTP</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                            <p>Xin chào, <strong>' . $email . '</strong>!</p>
                                            <p>Hệ thống DCVInvest gửi Bạn thông tin OTP.</p>
                                            <p>Mã OTP: <strong>' . $content . '</strong></p>
                                            <p style="font-style: italic">Lưu ý: mã OTP có hiệu lực trong vòng 5 phút. Bạn không được chia sẻ mã OTP này với bất kỳ ai.</p>
                                            <br>
                                            <p>Trân trọng thông báo!</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p style="text-align:center;color:#aaabbb;font-size:9pt">2021 © By SSC-EDUCATION</p>
                                <br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="yj6qo"></div>
                    <div class="adL"></div>
                </div>
                <div style ="display:none"><img src=""></div>';
                break;
            case Constants::EMAIL_TYPE_NOTI:
                $body = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                        <tbody>
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                        <tbody>
                                            <tr>
                                                <td bgcolor="#074B80" style="font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #fe9703">DCV Invest - '.$title.'</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                                    '.$content.'
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
                <div class="yj6qo"></div><div class="adL"></div></div>';
                break;
            case Constants::EMAIL_TYPE_REGISTER:
                $body = '<div bgcolor="#F1F1F1" style="min-width: 100% !important; margin: 40px 0; padding: 40px 0; background: #f1f1f1; font-size: 13px; font-family: \'Helvetica\',\'Arial\';" >
                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background: #f1f1f1; width: 100%; height: 100%; font-size: 14px; line-height: 1.5; border-collapse: collapse;" >
                    <tbody>
                        <tr>
                            <td>
                            <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background: #ffffff; width: 100%; max-width: 600px">
                                <tbody>
                                <tr>
                                    <td bgcolor="#074B80" style=" font-size: 20px; padding: 20px 40px; color: #ffffff; border-bottom: 5px solid #fe9703; ">
                                    DCV Invest - '.$title.'
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 22px 40px; border: 1px solid #dddddd; border-top: none; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; font-family: verdana, geneva, sans-serif; font-size: 14px; color: #000000; text-align: left;" >
                                    '.$content.'
                                    <p>
                                        Tải app dành cho Android
                                        <a href="https://play.google.com/store/apps/details?id=com.dcv.invest">tại đây</a>.
                                    </p>
                                    <p>
                                        Tải app dành cho IPhone
                                        <a href="https://apps.apple.com/vn/app/dcv-invest/id1556621903#?platform=iphone">tại đây</a>.
                                    </p>
                                    <p>Liên hệ hỗ trợ kỹ thuật: <a href="tel:02499998669">024 9999 8669</a></p>
                                        <div style="text-align: left">
                                    <div>
                                        <span><span><span style="font-size: 14px">-------------</span></span></span>
                                    </div>
                                    <div>
                                        <span><span><span style="font-size: 14px">Thân gửi.</span></span></span>
                                    </div>
                                    <div>
                                        <span><span><span>Đội ngũ<span class="il"> DCV Invest</span>&nbsp;</span></span></span>
                                    </div>
                                    <div>&nbsp;</div>
                                    <div>
                                        <span><span><span><em><span>Đây là email tự động. Bạn vui lòng không gửi phản hồi vào hộp thư này.</span></em></span></span></span>
                                    </div>
                                    <div>&nbsp;</div>

                                    <div style="text-align: center;">
                                        <span>
                                            <span style="font-size: 10px">
                                                <span style="font-weight: 600;background: transparent; font-size: inherit; color: #074B80;">
                                                    <span style="font-style: normal; font-family: Arial; font-size: 10px;">
                                                        <span class="il">DCVINVEST</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                        <span style="font-style: normal; font-variant-ligatures: normal;font-variant-caps: normal; font-weight: 400; font-family: verdana, geneva, sans-serif; font-size: 16px; text-align: center; color: rgb(169, 169, 169);">
                                            <span style="font-size: 10px">
                                                <span style="font-weight: 600; background: transparent; font-size: inherit;color: inherit;">
                                                    <span style="font-style: normal; font-family: Arial;font-size: 10px;">&nbsp;| Tư Vấn, Đầu Tư Sản Phẩm Phái Sinh Hàng Hóa</span>
                                                </span>
                                                <span style="font-style: normal;font-family: Arial;font-size: 10px;">&nbsp;|&nbsp;</span>
                                                <span style="font-style: normal;font-family: Arial;font-size: 10px;">
                                                    <u>
                                                        <a href="https://dcvinvest.com" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://dcvinvest.com&amp;source=gmail&amp;ust=1622381376613000&amp;usg=AFQjCNERpopuJgZPUh4bUgzIqrVOcPsLwQ">https://<span class="il">dcvinvest</span>.com</a>
                                                    </u>
                                                </span>
                                                <br />
                                                <em>
                                                    <span style="font-style: normal; font-family: Arial;font-size: 10px;">Đây là email tự động. Xin bạn vui lòng không gửi phản hồi vào hộp thư này.</span>
                                                </em>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table role="module" border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed">
                                    <tbody>
                                        <tr>
                                            <td style="padding: 0px 0px 0px 0px; background-color: F0F9FB" role="module-content" height="100%" valign="top" bgcolor="F0F9FB">
                                                <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%" height="10px" style="line-height: 10px; font-size: 10px">
                                                <tbody>
                                                    <tr>
                                                    <td style="padding: 0px 0px 10px 0px" bgcolor="F0F9FB"></td>
                                                    </tr>
                                                </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div role="module" style="background-color: #074B80; color: #ffffff; font-size: 12px; line-height: 30px; padding: 16px 16px 16px 16px; text-align: center;">
                                    <div>
                                        <p style="font-family: verdana, geneva, sans-serif; font-size: 12px; line-height: 20px;">
                                        <span class="il">DCVINVEST</span>
                                        </p>
                                        <p style="font-family: verdana, geneva, sans-serif; font-size: 12px; line-height: 20px;">
                                        Tầng L2, toà nhà Mỹ Sơn, Số 62 Nguyễn Huy Tưởng, quận Thanh Xuân, Thành phố Hà Nội.
                                        </p>
                                    </div>
                                    <p style="font-family: verdana, geneva, sans-serif; font-size: 12px; line-height: 30px;"></p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
              <div class="yj6qo"></div>
              <div class="adL"></div>
            </div>';
                break;
            default:
                $body = '<div bgcolor="#F1F1F1" style="min-width:100%!important;margin:40px 0;padding:40px 0;background:#f1f1f1;font-size:13px;font-family:\'Helvetica\',\'Arial\'">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F1F1F1" style="background:#f1f1f1;width:100%;height:100%;font-size:14px;line-height:1.5;border-collapse:collapse">
                        <tbody>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" align="center" style="background:#ffffff;width:100%;max-width:600px">
                                    <tbody>
                                    <tr>
                                        <td bgcolor="#074B80" style="background-color: #1dabe3; font-size:20px;padding:20px 40px;color:#ffffff;border-bottom:5px solid #2b57a4">SSC-EDUCATION - Thông tin liên hệ khách hàng</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:22px 40px;border:1px solid #dddddd;border-top:none">
                                            <p>Hi <strong>Administrator</strong>!</p>
                                            <p>Vừa có một Khách hàng gửi thông tin về ' . $type . '.</p>
                                            <p>Thông tin Khách hàng:</p>
                                            <ul>
                                                <li>Họ và tên: ' . $fullname . '</li>
                                                <li>Số điện thoại: ' . $mobile . '</li>
                                                <li>Tên công ty: ' . $company_name . '</li>
                                                <li>Địa chỉ: ' . $address . ' , ' . $city . '</li>
                                                <li>Email: ' . $email . '</li>
                                                <li>Loại yêu cầu: ' . $type . '</li>
                                                <li>Ghi chú: ' . $content . '</li>
                                            </ul>
                                            <p>Link đăng nhập CMS <a href=' . $url . ' >tại đây</a>.</p>
                                            <br>
                                            <p>Trân trọng thông báo!</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p style="text-align:center;color:#aaabbb;font-size:9pt">2021 © By SSC-EDUCATION</p>
                                <br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="yj6qo"></div>
                    <div class="adL"></div>
                </div>
                <div style ="display:none"><img src=""></div>';
        }
        return $body;
    }
}

if (!function_exists('validateMobile')) {
    function validateMobile($mobile)
    {
        if ($mobile == "" || $mobile == null) return false;

        $start_pattern = "/";
        $end_pattern = "/";

        $viettel_pattern = $start_pattern;

        $viettel_pattern .= "^8498\d{7}$|^0?98\d{7}$|^98\d{7}$";
        $viettel_pattern .= "|^8497\d{7}$|^0?97\d{7}$|^97\d{7}$";
        $viettel_pattern .= "|^8496\d{7}$|^0?96\d{7}$|^96\d{7}$";
        $viettel_pattern .= "|^8432\d{7}$|^0?32\d{7}$|^32\d{7}$";
        $viettel_pattern .= "|^8433\d{7}$|^0?33\d{7}$|^33\d{7}$";
        $viettel_pattern .= "|^8434\d{7}$|^0?34\d{7}$|^34\d{7}$";
        $viettel_pattern .= "|^8435\d{7}$|^0?35\d{7}$|^35\d{7}$";
        $viettel_pattern .= "|^8436\d{7}$|^0?36\d{7}$|^36\d{7}$";
        $viettel_pattern .= "|^8437\d{7}$|^0?37\d{7}$|^37\d{7}$";
        $viettel_pattern .= "|^8438\d{7}$|^0?38\d{7}$|^38\d{7}$";
        $viettel_pattern .= "|^8439\d{7}$|^0?39\d{7}$|^39\d{7}$";
        $viettel_pattern .= "|^8486\d{7}$|^0?86\d{7}$|^86\d{7}$";

        $vinaphone_pattern = $viettel_pattern;
        $vinaphone_pattern .= "|^8491\d{7}$|^0?91\d{7}$|^91\d{7}$";
        $vinaphone_pattern .= "|^8494\d{7}$|^0?94\d{7}$|^94\d{7}$";
        $vinaphone_pattern .= "|^8481\d{7}$|^0?81\d{7}$|^81\d{7}$";
        $vinaphone_pattern .= "|^8482\d{7}$|^0?82\d{7}$|^82\d{7}$";
        $vinaphone_pattern .= "|^8483\d{7}$|^0?83\d{7}$|^83\d{7}$";
        $vinaphone_pattern .= "|^8484\d{7}$|^0?84\d{7}$|^84\d{7}$";
        $vinaphone_pattern .= "|^8485\d{7}$|^0?85\d{7}$|^85\d{7}$";
        $vinaphone_pattern .= "|^8488\d{7}$|^0?88\d{7}$|^88\d{7}$";

        $mobifone_pattern = $vinaphone_pattern;
        $mobifone_pattern .= "|^8490\d{7}$|^0?90\d{7}$|^90\d{7}$";
        $mobifone_pattern .= "|^8493\d{7}$|^0?93\d{7}$|^93\d{7}$";
        $mobifone_pattern .= "|^8470\d{7}$|^0?70\d{7}$|^70\d{7}$";
        $mobifone_pattern .= "|^8476\d{7}$|^0?76\d{7}$|^76\d{7}$";
        $mobifone_pattern .= "|^8477\d{7}$|^0?77\d{7}$|^77\d{7}$";
        $mobifone_pattern .= "|^8478\d{7}$|^0?78\d{7}$|^78\d{7}$";
        $mobifone_pattern .= "|^8479\d{7}$|^0?79\d{7}$|^79\d{7}$";
        $mobifone_pattern .= "|^8489\d{7}$|^0?89\d{7}$|^89\d{7}$";

        $vietnamobile_pattern = $mobifone_pattern;
        $vietnamobile_pattern .= "|^8492\d{7}$|^0?92\d{7}$|^92\d{7}$";
        $vietnamobile_pattern .= "|^8456\d{7}$|^0?56\d{7}$|^56\d{7}$";
        $vietnamobile_pattern .= "|^8458\d{7}$|^0?58\d{7}$|^58\d{7}$";

        $vietnamobile_pattern .= $end_pattern;

        // $landline_pattern = /^84203\d{8}$|^0?203\d{8}$|^203\d{8}$|^84204\d{8}$|^0?204\d{8}$|^204\d{8}$|^84205\d{8}$|^0?205\d{8}$|^205\d{8}$|^84206\d{8}$|^0?206\d{8}$|^206\d{8}$|^84207\d{8}$|^0?207\d{8}$|^207\d{8}$|^84208\d{8}$|^0?208\d{8}$|^208\d{8}$|^84209\d{8}$|^0?209\d{8}$|^209\d{8}$|^84210\d{8}$|^0?210\d{8}$|^210\d{8}$|^84211\d{8}$|^0?211\d{8}$|^211\d{8}$|^84212\d{8}$|^0?212\d{8}$|^212\d{8}$|^84213\d{8}$|^0?213\d{8}$|^213\d{8}$|^84214\d{8}$|^0?214\d{8}$|^214\d{8}$|^84215\d{8}$|^0?215\d{8}$|^215\d{8}$|^84216\d{8}$|^0?216\d{8}$|^216\d{8}$|^84218\d{8}$|^0?218\d{8}$|^218\d{8}$|^84219\d{8}$|^0?219\d{8}$|^219\d{8}$|^84220\d{8}$|^0?220\d{8}$|^220\d{8}$|^84221\d{8}$|^0?221\d{8}$|^221\d{8}$|^84222\d{8}$|^0?222\d{8}$|^222\d{8}$|^84225\d{8}$|^0?225\d{8}$|^225\d{8}$|^84226\d{8}$|^0?226\d{8}$|^226\d{8}$|^84227\d{8}$|^0?227\d{8}$|^227\d{8}$|^84228\d{8}$|^0?228\d{8}$|^228\d{8}$|^84229\d{8}$|^0?229\d{8}$|^229\d{8}$|^84232\d{8}$|^0?232\d{8}$|^232\d{8}$|^84233\d{8}$|^0?233\d{8}$|^233\d{8}$|^84234\d{8}$|^0?234\d{8}$|^234\d{8}$|^84235\d{8}$|^0?235\d{8}$|^235\d{8}$|^84236\d{8}$|^0?236\d{8}$|^236\d{8}$|^84237\d{8}$|^0?237\d{8}$|^237\d{8}$|^84238\d{8}$|^0?238\d{8}$|^238\d{8}$|^84239\d{8}$|^0?239\d{8}$|^239\d{8}$|^8424\d{8}$|^0?24\d{8}$|^24\d{8}$|^84251\d{8}$|^0?251\d{8}$|^251\d{8}$|^84252\d{8}$|^0?252\d{8}$|^252\d{8}$|^84254\d{8}$|^0?254\d{8}$|^254\d{8}$|^84255\d{8}$|^0?255\d{8}$|^255\d{8}$|^84256\d{8}$|^0?256\d{8}$|^256\d{8}$|^84257\d{8}$|^0?257\d{8}$|^257\d{8}$|^84258\d{8}$|^0?258\d{8}$|^258\d{8}$|^84259\d{8}$|^0?259\d{8}$|^259\d{8}$|^84260\d{8}$|^0?260\d{8}$|^260\d{8}$|^84261\d{8}$|^0?261\d{8}$|^261\d{8}$|^84262\d{8}$|^0?262\d{8}$|^262\d{8}$|^84263\d{8}$|^0?263\d{8}$|^263\d{8}$|^84269\d{8}$|^0?269\d{8}$|^269\d{8}$|^84270\d{8}$|^0?270\d{8}$|^270\d{8}$|^84271\d{8}$|^0?271\d{8}$|^271\d{8}$|^84272\d{8}$|^0?272\d{8}$|^272\d{8}$|^84273\d{8}$|^0?273\d{8}$|^273\d{8}$|^84274\d{8}$|^0?274\d{8}$|^274\d{8}$|^84275\d{8}$|^0?275\d{8}$|^275\d{8}$|^84276\d{8}$|^0?276\d{8}$|^276\d{8}$|^84277\d{8}$|^0?277\d{8}$|^277\d{8}$|^8428\d{8}$|^0?28\d{8}$|^28\d{8}$|^84290\d{8}$|^0?290\d{8}$|^290\d{8}$|^84291\d{8}$|^0?291\d{8}$|^291\d{8}$|^84292\d{8}$|^0?292\d{8}$|^292\d{8}$|^84293\d{8}$|^0?293\d{8}$|^293\d{8}$|^84294\d{8}$|^0?294\d{8}$|^294\d{8}$|^84296\d{8}$|^0?296\d{8}$|^296\d{8}$|^84297\d{8}$|^0?297\d{8}$|^297\d{8}$|^84299\d{8}$|^0?299\d{8}$|^299\d{8}$/;

        if (preg_match($vietnamobile_pattern, $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('formatMobile')) {
    function formatMobile($mobile)
    {
        $res_format = '';
        if (validateMobile($mobile)) {
            switch (strlen($mobile)) {
                case 9:
                    $res_format = '84' . $mobile;
                    break;
                case 10:
                    $res_format = '84' . substr($mobile, 1);
                    break;
                case 11:
                    $res_format = $mobile;
                default:
            }
        }

        return $res_format;
    }
}

if (!function_exists('getNameFromEmail')) {
    /**
     * Hàm tách lấy phần trước @ từ email
     *
     * @param $email
     * @return false|string|null
     */
    function getNameFromEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return substr($email, 0, strpos($email, '@'));
        } else {
            return 'N/A';
        }
    }
}

if (!function_exists('getDom')) {
    function getDom($link)
    {
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);
        $dom = \Sunra\PhpSimple\HtmlDomParser::str_get_html($content);

        return $dom;
    }
}

if (!function_exists('cURL')) {
    function cURL($url, $jwt = null, $params = [])
    {
        $ch = curl_init();
        //
        //$headers = array();
        //$headers[] = 'Authority: mp3.zing.vn';
        //$headers[] = 'Cache-Control: max-age=0';
        //$headers[] = 'Upgrade-Insecure-Requests: 1';
        //$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36';
        //$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
        //$headers[] = 'Accept-Encoding: gzip, deflate, br';
        //$headers[] = 'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7,ja;q=0.6';
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($jwt) {
            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $jwt;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (isset($params['authority']) && $params['authority'] == 'ZALO.AI' && isset($params['apikey']) && $params['apikey']) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apikey: ' . $params['apikey']
            ]);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (isset($params['method']) && strtoupper($params['method']) == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');


        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);

        return $result;
    }
}

if (!function_exists('getTimeAgo')) {
    function getTimeAgo($date)
    {
        $date = str_replace('/', '-', $date);
        $text = 'N/A';

        if ($date) {
            $temp = time() - strtotime($date);
            if ($temp >= 0) {
                $str = 'trước';
            } else {
                $str = 'nữa';
            }
            $temp = abs($temp);

            if (0 <= $temp && $temp < 60) {
                $text = 'Vừa xong';
            } elseif (60 <= $temp && $temp < 3600) {
                $text = floor($temp / 60) . ' phút ' . $str;
            } elseif (3600 <= $temp && $temp < 86400) {
                $text = floor($temp / 3600) . ' giờ ' . $str;
            } elseif (86400 <= $temp && $temp < 604800) {
                $text = floor($temp / 86400) . ' ngày ' . $str;
            } elseif (604800 <= $temp && $temp < 2592000) {
                $text = floor($temp / 604800) . ' tuần ' . $str;
            } elseif (2592000 <= $temp && $temp < 31104000) {
                $text = floor($temp / 2592000) . ' tháng ' . $str;
            } else {
                $text = floor($temp / 31104000) . ' năm ' . $str;
            }
        }

        return $text;

    }
}

if (!function_exists('getImageLoadingBase64')) {
    function getImageLoadingBase64()
    {
        return 'data:image/gif;base64,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';
    }
}

if (!function_exists('getImagePath')) {
    function getImagePath($url)
    {
        if(strpos($url, 'uploads/') !== false){
            return asset('storage/' . $url);
        } else {
            return 'https://crm.dcvinvest.com/' . $url;
        }
    }
}

if (!function_exists('format_yyyymmddhhiiss')) {
    function format_yyyymmddhhiiss($strtime, $separate = '/')
    {
        $strtime = preg_replace("/[^0-9]/", "", $strtime);
        if (strlen($strtime) == 14) {
            return preg_replace(
                "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/",
                "$3$separate$2$separate$1 $4:$5",
                $strtime
            );
        } else {
            return $strtime;
        }
    }
}

if (!function_exists('translateKeyWord')) {
    function translateKeyWord($keyWord)
    {
        if (empty($keyWord)) {
            return $keyWord;
        } else {
            return str_replace(['%'], ['\%'], $keyWord);
        }
    }
}

if (!function_exists('statusUser')) {
    function statusUser($status)
    {
        $name = '';
        switch($status){
            case 0:
                $name = 'Chưa kích hoạt';
                break;
            case 1:
                $name = 'Đã kích hoạt';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
            case 3:
                $name = 'Khóa vĩnh viễn';
                break;
            default:
                $name = 'Tạm khóa';
        }
        return $name;
    }
}

if (!function_exists('statusFee')) {
    function statusFee($status)
    {
        $name = '';
        switch($status){
            case 0:
                $name = 'Đang chờ duyệt';
                break;
            case 1:
                $name = 'Thành công';
                break;
            case 2:
                $name = 'Không thành công';
                break;
            default:
                $name = 'Đang chờ duyệt';
        }
        return $name;
    }
}

if (!function_exists('statusSchool')) {
    function statusSchool($status)
    {
        $name = 'Chưa kích hoạt';
        switch($status){
            case 0:
                $name = 'Chưa kích hoạt';
                break;
            case 1:
                $name = 'Đang hoạt động';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
        }
        return $name;
    }
}

if (!function_exists('statusGroupApp')) {
    function statusGroupApp($status)
    {
        $name = '';
        switch($status){
            case 0:
                $name = 'Đang chờ duyệt';
                break;
            case 1:
                $name = 'Đang hoạt động';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
            default:
                $name = 'Đang chờ duyệt';
        }
        return $name;
    }
}

if (!function_exists('statusApp')) {
    function statusApp($status)
    {
        $name = '';
        switch($status){
            case 0:
                $name = 'Đang chờ duyệt';
                break;
            case 1:
                $name = 'Đang hoạt động';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
            default:
                $name = 'Đang chờ duyệt';
        }
        return $name;
    }
}

if (!function_exists('statusSchoolBank')) {
    function statusSchoolBank($status)
    {
        $name = 'Chưa xác minh';
        switch($status){
            case 0:
                $name = 'Chưa xác minh';
                break;
            case 1:
                $name = 'Đang hoạt động';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
        }
        return $name;
    }
}

if (!function_exists('statusVa')) {
    function statusVa($status)
    {
        $name = 'Không xác định';
        switch($status){
            case 1:
                $name = 'Đang hoạt động';
                break;
            case 2:
                $name = 'Tạm khóa';
                break;
            case 3:
                $name = 'Đã hủy';
                break;
        }
        return $name;
    }
}

if (!function_exists('statusStudent')) {
    function statusStudent($status)
    {
        $name = 'Chưa xác minh';
        switch($status){
            case 1:
                $name = 'Đang học';
                break;
            case 2:
                $name = 'Bảo lưu';
                break;
            case 3:
                $name = 'Đã nghỉ học';
                break;
        }
        return $name;
    }
}

if (!function_exists('reformatDate')) {
    function reformatDate($datetime, $format='Y-m-d')
    {
        if ($datetime && $format) {
            return date($format, strtotime(str_replace('/', '-', $datetime)));
        }

        return null;
    }
}

if (!function_exists('sendRequest')) {
    /**
     * Hàm xử lý request
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @param bool $isJSON
     * @param bool $isAuthen
     * @param null $bearerToken
     * @param int $timeOut
     * @return mixed
     */
    function sendRequest($url, $params = array(), $method = 'POST', $isJSON = true, $isAuthen = true, $bearerToken = null, $timeOut = 15)
    {
        $request = \Ixudra\Curl\Facades\Curl::to($url)
            ->withData($params)
            ->withOption('TIMEOUT', $timeOut)
            ->withOption('CONNECTTIMEOUT', 0)
            ->withOption('SSL_VERIFYPEER', 0)
            ->withContentType('application/json')
            ->withOption('FOLLOWLOCATION', true)
            ->returnResponseObject();

        if ($isJSON) {
            $request->asJsonRequest();
        }

        if($isAuthen){
            $request->withOption('USERPWD', 'admin:weppoHER4352GGErfg');
        }

        if ($bearerToken) {
            $request->withBearer($bearerToken);
        }

        $response = '';
        switch ($method) {
            case 'GET':
                $response = $request->get();
                break;
            case 'POST':
                $response = $request->post();
                break;
            case 'PUT':
                $response = $request->put();
                break;
            case 'PATCH':
                $response = $request->patch();
                break;
            case 'DELETE':
                $response = $request->delete();
                break;
            default:
                break;
        }

        return $response->content;
    }
}

if (!function_exists('unsigned')) {
    function unsigned($str, $strtolower = 0)
    {
        $marTViet = array(
            "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ",
            "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ",
            "ì", "í", "ị", "ỉ", "ĩ",
            "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ",
            "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
            "ỳ", "ý", "ỵ", "ỷ", "ỹ",
            "đ",
            "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
            "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
            "Ì", "Í", "Ị", "Ỉ", "Ĩ",
            "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
            "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
            "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
            "Đ");
        $marKoDau = array(
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
            "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
            "i", "i", "i", "i", "i",
            "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
            "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
            "y", "y", "y", "y", "y",
            "d",
            "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A",
            "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
            "I", "I", "I", "I", "I",
            "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O",
            "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
            "Y", "Y", "Y", "Y", "Y",
            "D");
        if ($strtolower != 0) {
            $str = strtolower(str_replace($marTViet, $marKoDau, $str));
        } else {
            $str = str_replace($marTViet, $marKoDau, $str);
        }
        return $str;
    }
}

?>
