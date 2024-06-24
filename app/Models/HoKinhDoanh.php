<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HoKinhDoanh extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_HO_KINH_DOANH;
    public $timestamps = true;
    protected $appends = ['total_balance'];

    protected $fillable = [
        'name',
        'surrogate',
        'phone',
        'address',
        'status',
        'balance',
        'amount_old',
    ];


    public function getBalanceAttribute($value)
    {
        return (int) $value;
    }
    public function getAmountOldAttribute($value)
    {
        return (int) $value;
    }

    public function getTotalBalanceAttribute()
    {
        return (int)($this->balance + $this->amount_old);
    }
}
