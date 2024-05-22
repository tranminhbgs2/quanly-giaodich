<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyComesBack extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_MONEY_COMES_BACK;
    public $timestamps = true;

    protected $fillable = [
        'agent_id',
        'pos_id',
        'lo_number',
        'time_end',
        'time_process',
        'created_by',
        'fee',
        'total_price',
        'payment',
        'balance',
        'status',
        'fee_agent',
        'payment_agent',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function agency()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function pos()
    {
        return $this->belongsTo(Pos::class, 'pos_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
