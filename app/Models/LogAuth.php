<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class LogAuth extends Model
{
    protected $table = Constants::TABLE_LOG_AUTHS;
    public $timestamps = true;

    protected $fillable = [
        'account_type',
        'session_id',
        'user_id',
        'action_type',
        'logged_in_at',
        'account_input',
        'logged_out_at',
        'user_agent',
        'duration',
        'ip_address',
        'error_code',
        'result',
    ];

    /**
     * Lấy thông tin user, thông tin người dùng login
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function getLoggedInAtAttribute($value)
    {
        if ($value) {
            return date('d/m/Y H:i:s', strtotime($value));
        }

        return $value;
    }

    public function getLoggedOutAtAttribute($value)
    {
        if ($value) {
            return date('d/m/Y H:i:s', strtotime($value));
        }

        return $value;
    }
}
