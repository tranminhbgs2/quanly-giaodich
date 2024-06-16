<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $table = Constants::TABLE_USERS;

    protected $appends = ['status_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_type',
        'username',
        'sscid',
        'fullname',
        'birthday',
        'gender',
        'avatar',
        'address',
        'email',
        'phone',
        'display_name',
        'email_verified_at',
        'password',
        'remember_token',
        'notes',
        'last_login',
        'last_logout',
        'identifier',
        'issue_date',
        'issue_place',
        'department_id',
        'status',
        'balance',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at',
        'created_at', 'updated_at', 'deleted_at',
    ];


    /**
     * Tính xem user này thuộc phòng/ban nào
     */
    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id');
    }

    /**
     * Hàm trả về đường dẫn full của avatar
     *
     * @param $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        if (empty($value)) {
            $value = Constants::DEFAULT_AVATAR_IMAGE;
        } else {
            $value = 'storage/' . $value;
        }

        return asset($value);
    }

    public function getBirthdayAttribute($value)
    {
        if ($value) {
            return date('d/m/Y', strtotime($value));
        }

        return $value;
    }

    public function getPhoneAttribute($value)
    {
        if ($value) {
            //kiểm tra 2 số đầu nếu = 84 thì đổi thành 0
            if (substr($value, 0, 2) == '84') {
                $value = '0' . substr($value, 2);
            }
            return $value;
        }

        return $value;
    }

    public function getIssueDateAttribute($value)
    {
        if ($value) {
            return date('d/m/Y', strtotime($value));
        }

        return $value;
    }

    public function getLastLoginAttribute($value)
    {
        if ($value) {
            return date('d/m/Y H:i:s', strtotime($value));
        }

        return $value;
    }

    public function getStatusNameAttribute()
    {
        return statusUser($this->status);
    }

    public function userPermissions()
    {
        return $this->belongsToMany(Position::class, 'user_position', 'user_id', 'position_id');
    }

    public function getBalanceAttribute($value)
    {
        return (int) $value;
    }
}
