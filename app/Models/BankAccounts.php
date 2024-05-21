<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccounts extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_BANK_ACCOUNTS;
    public $timestamps = true;
    protected $fillable = [
        'agent_id',
        'bank_code',
        'account_number',
        'account_name',
        'balance',
        'status',
    ];

    public function agency()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }
}
