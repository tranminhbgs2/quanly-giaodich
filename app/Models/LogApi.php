<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class LogApi extends Model
{
    protected $table = Constants::TABLE_LOG_APIS;
    public $timestamps = true;

    protected $fillable = [
        'request_at',
        'device_id',
        'client_id',
        'client_ip',
        'uri',
        'request_data',
        'response_data',
    ];
}
