<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class WithdrawPos extends Model
{
    protected $table = Constants::TABLE_WITHDRAW_POS;
    public $timestamps = true;

    protected $fillable = [
        'pos_id',
        'hkd_id',
        'time_withdraw',
        'account_bank_id',
        'price_withdraw',
        'status',
        'created_by',
    ];

    public function pos()
    {
        return $this->belongsTo(Pos::class, 'pos_id', 'id');
    }

    public function hokinhdoanh()
    {
        return $this->belongsTo(HoKinhDoanh::class, 'hkd_id', 'id');
    }

    public function accountBank()
    {
        return $this->belongsTo(BankAccounts::class, 'account_bank_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

}
