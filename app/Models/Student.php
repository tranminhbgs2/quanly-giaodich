<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = Constants::TABLE_STUDENTS;
    public $timestamps = true;
    protected $appends = ['status_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'class_id',
        'fullname',
        'sscid',
        'birthday',
        'gender',
        'avatar',
        'address',
        'email',
        'phone',
        'school_name',
        'class_name',
        'va_id',
        'va_card',
        'va_account',
        'va_owner',
        'va_bank_name',
        'va_bank_code',
        'va_branch',
        'va_balance',
        'va_created',
        'va_updated',
        'created_by',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function parent()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo('App\Models\School', 'school_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo('App\Models\Clazz', 'class_id', 'id');
    }

    public function va()
    {
        return $this->belongsTo('App\Models\Va', 'va_id', 'id');
    }

    public function getAvatarAttribute($value)
    {
        if (empty($value)) {
            $value = Constants::DEFAULT_STUDENT_AVATAR;
        } else {
            $value = 'storage/' . $value;
        }

        return asset($value);
    }

    public function getStatusNameAttribute()
    {
        return statusStudent($this->status);
    }
}
