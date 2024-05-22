<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class LogAction extends Model
{
    protected $table = Constants::TABLE_LOG_ACTIONS;
    public $timestamps = true;

    protected $fillable = [
        'actor_id',
        'username',
        'action',
        'description',
        'model',
        'table',
        'record_id',
        'ip_address',
        'data_old',
        'data_new',
    ];

    /**
     * Lấy thông tin user, thông tin nhân viên
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'actor_id', 'id');
    }
}
