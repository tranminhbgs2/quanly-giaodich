<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlow extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_CASH_FLOW;
    public $timestamps = true;

    protected $fillable = [
        'type',
        'acc_bank_id',
        'note',
        'price',
        'time_payment',
        'status',
        'created_by',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function managerBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Danh sách các tài khoản ngân hàng của agent
     */
    public function bankAccounts()
    {
        return $this->belongsTo(BankAccounts::class, 'acc_bank_id', 'id');
    }
}
