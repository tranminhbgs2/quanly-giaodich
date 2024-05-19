<?php

namespace App;

use App\Helpers\Constants;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvatarAttribute($value)
    {
        if (empty($value)) {
            $value = Constants::DEFAULT_AVATAR_IMAGE;
        } else {
            $value = 'storage/' . $value;
        }

        return asset($value);
    }

    /**
     * Định dạng lại ngày sinh theo dd/mm/yyyy
     *
     * @param $value
     * @return false|string|null
     */
    public function getBirthdayAttribute($value)
    {
        if ($value) {
            return date('d/m/Y', strtotime($value));
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
}
