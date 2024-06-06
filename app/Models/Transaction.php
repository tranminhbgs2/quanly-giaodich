<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_TRANSACTION;
    public $timestamps = true;
    protected $appends = ['method_name', 'status_fee', 'fee_remain'];

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
        'original_fee',
        'fee_cashback',
        'note',
        'fee_paid',
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
    public function getMethodNameAttribute()
    {
        $name = '';
        switch ($this->method) {
            case 'DAO_HAN':
                $name = 'Đáo hạn';
                break;
            case 'RUT_TIEN_MAT':
                $name = 'Rút tiền mặt';
                break;
            case 'ONLINE':
                $name = 'Online';
                break;
        }
        // Định dạng dữ liệu của method tại đây
        return $name; // Ví dụ: chuyển thành chữ hoa
    }

    public function getTotalPaymentCashbackAttribute()
    {
        $pos = $this->pos;
        if ($pos) {
            return $this->total_price_rut * $pos->fee_cashback / 100;
        }
        return 0;
    }

    public function getStatusFeeAttribute()
    {
        if ($this->fee_paid == $this->price_fee) {
            return true;
        }
        return false;
    }

    public function getFeeRemainAttribute()
    {
        return $this->price_fee - $this->fee_paid;
    }
}
