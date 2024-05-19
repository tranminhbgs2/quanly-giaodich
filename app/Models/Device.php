<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = Constants::TABLE_DEVICES;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'channel',
        'token',
        'platform',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}
