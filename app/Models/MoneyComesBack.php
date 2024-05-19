<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class MoneyComesBack extends Model
{
    protected $table = Constants::TABLE_MONEY_COMES_BACK;
    public $timestamps = true;

    protected $fillable = [
        'agency_id',
        'pos_id',
        'lo_number',
        'time_end',
        'created_by',
        'fee',
        'total_price',
        'payment',
        'balance',
        'status',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function agency()
    {
        return $this->belongsTo(Agent::class, 'agency_id', 'id');
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
