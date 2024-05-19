<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Otp extends Model
{
    protected $table = Constants::TABLE_OTPS;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'code','method','receiver_name', 'expiration_date', 'status', 'verified_at'
    ];

}
