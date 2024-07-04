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
    protected $appends = ['profit', 'status_ket_toan'];

    protected $fillable = [
        'agent_id',
        'pos_id',
        'hkd_id',
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
        'note',
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

    public function hkd()
    {
        return $this->belongsTo(HoKinhDoanh::class, 'hkd_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getProfitAttribute()
    {
        return $this->payment - $this->payment_agent;
    }

    public function getTimeEndAttribute()
    {
        if (empty($this->attributes['time_end'])) {
            return null;
        }
        //format time_end to Y/m/d H:i:s
        return date('Y/m/d H:i:s', strtotime($this->attributes['time_end']));
    }

    public function getStatusKetToanAttribute()
    {
        if (!empty($this->time_end)) {
            return 'Đã kết toán';
        }
        return 'Chưa kết toán';
    }

    public function getTotalPriceAttribute()
    {
        if (!empty($this->attributes['total_price'])) {
            return (int)$this->attributes['total_price'];
        }
    }

    public function getPaymentAttribute()
    {
        if (!empty($this->attributes['payment'])) {
            return (int)$this->attributes['payment'];
        }
    }

    public function getPaymentAgentAttribute()
    {
        if (!empty($this->attributes['payment_agent'])) {
            return (int)$this->attributes['payment_agent'];
        }

    }
}
