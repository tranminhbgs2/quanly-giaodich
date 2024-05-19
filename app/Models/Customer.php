<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = Constants::TABLE_USERS;
    public $timestamps = true;

    protected $appends = ['status_name', 'cqg_status_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'parent_id',
        'admin_id',
        'lead_id',
        'left',
        'right',
        'depth',
        'cqg_id',
        'cqg_account',
        'cqg_name',
        'mxv_id',
        'mxv_account',
        'mxv_name',
        'user_id',
        'username',
        'email',
        'sponsor_id',
        'language',
        'f_name',
        'l_name',
        'fullname',
        'contract_code',
        'acc_type',
        'bank_no',
        'number_banking',
        'birthday',
        'place_of_issue',
        'cmnd',
        'date_range',
        'is_signed',
        'phone',
        'address',
        'oauth_provider',
        'oauth_uid',
        'gender',
        'locale',
        'picture_url',
        'profile_url',
        'identity_after',
        'identity_before',
        'sign_img',
        'password',
        'password_lar',
        'password_reset_tocken',
        'reg_ip',
        'identity_account',
        'card_type',
        'reason',
        'current_money',
        'current_cqg_money',
        'status',
        'old_status',
        'created',
        'modified',
        'pushed_at',
        'verify_at',
        'verified_at',
        'agency_id',
        'broker_id',
        'collaborator_id',
        'mxv_trans_account',
        'mxv_trans_fullname',
        'mxv_notes',
        'mxv_date_join',
        'mxv_birthday',
        'mxv_passport',
        'mxv_date_of_issue',
        'mxv_place_of_issue',
        'mxv_email',
        'mxv_mobile',
        'mxv_address',
        'mxv_frontend_passport',
        'mxv_backend_passport',
        'mxv_signature',
        'mxv_status',
        'mxv_balance',
        'mxv_synced_at',
        'complete_create_cqg_at',
        'request_create_cqg_at',
        'longitude',
        'latitude',
        'location_info',
        'otp_password',
        'otp_created_at'
    ];

    protected $hidden = [
        'password', 'password_reset_tocken', 'new_password', 'password_lar', 'otp_password'
    ];

    public function getStatusNameAttribute()
    {
        return statusUser($this->status);
    }

    public function getCqgStatusNameAttribute()
    {
        return (($this->cqg_account) ? 'Đã mở' : 'Chưa mở');
    }

    /**
     * Lấy đường dẫn full CMND mặt trước
     *
     * @param $value
     * @return string
     */
    public function getIdentityBeforeAttribute($value)
    {
        if ($value) {
            return getImagePath($value);
        } else {
            return null;
        }
    }

    /**
     * Lấy đường dẫn full CMND mặt sau
     *
     * @param $value
     * @return string
     */
    public function getIdentityAfterAttribute($value)
    {
        if ($value) {
            return getImagePath($value);
        } else {
            return null;
        }
    }

    /**
     * Lấy đường dẫn full chữ ký
     *
     * @param $value
     * @return string
     */
    public function getSignImgAttribute($value)
    {
        if ($value) {
            return getImagePath($value);
        } else {
            return null;
        }
    }

    /**
     * Lấy đường dẫn full avatar của KH
     *
     * @param $value
     * @return string|null
     */
    public function getPictureUrlAttribute($value)
    {
        if ($value) {
            return getImagePath($value);
        } else {
            return asset(Constants::DEFAULT_AVATAR_IMAGE);
        }
    }

    /**
     * Lấy fullname của KH trong một số trường hợp bị thiếu thông tin
     *
     * @param $value
     * @return false|mixed|string|null
     */
    public function getFullnameAttribute($value)
    {
        if (empty($this->attributes['fullname'])) {
            if (empty($this->attributes['l_name']) && empty($this->attributes['l_name'])) {
                $this->attributes['fullname'] = getNameFromEmail($this->attributes['email']);
            } else {
                $this->attributes['fullname'] = $this->attributes['l_name'] . ' ' . $this->attributes['l_name'];
            }
        }

        return $this->attributes['fullname'];
    }

    /**
     * Định dạng lại ngày sinh
     *
     * @param $value
     * @return false|string|null
     */
    public function getBirthdayAttribute($value)
    {
        if ($value) {
            return date('d/m/Y', strtotime(str_replace('/', '-', $value)));
        } else {
            return $value;
        }
    }

    /**
     * Hàm lấy thông tin của người giới thiệu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsor()
    {
        return $this->belongsTo('App\Models\Customer', 'sponsor_id', 'user_id');
    }

    /**
     * Hàm lấy thông tin đối tác của tôi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function partner()
    {
        return $this->hasMany('App\Models\Customer', 'parent_id', 'uid');
    }
}
