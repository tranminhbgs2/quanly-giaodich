<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = Constants::TABLE_TRANSACTION;
    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'customer_id',
        'customer_name',
        'bank_card',
        'method',
        'pos_id',
        'lo_number',
        'fee',
        'price_nop',
        'price_rut',
        'price_fee',
        'price_transfer',
        'profit',
        'price_repair',
        'time_payment',
        'status',
        'created_by',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function pos()
    {
        return $this->belongsTo(Pos::class, 'pos_id', 'id');
    }
}
