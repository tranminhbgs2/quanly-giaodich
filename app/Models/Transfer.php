<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_TRANSFERS;
    public $timestamps = true;
    protected $appends = [
        'from_name', 'to_name', 'staff_to',
        'staff_from',
        'agent_from',
        'agent_to'
    ];
    protected $fillable = [
        'acc_bank_from_id',
        'acc_number_from',
        'acc_name_from',
        'acc_bank_to_id',
        'acc_number_to',
        'acc_name_to',
        'bank_to',
        'bank_from',
        'type_to',
        'time_payment',
        'created_by',
        'price',
        'status',
        'type_from',
        'from_agent_id',
        'to_agent_id',
    ];

    public function bankTransferFrom()
    {
        return $this->belongsTo(BankAccounts::class, 'acc_bank_from_id', 'id');
    }

    public function bankTransferTo()
    {
        return $this->belongsTo(BankAccounts::class, 'acc_bank_to_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function fromAgent()
    {
        return $this->belongsTo(Agent::class, 'from_agent_id', 'id');
    }

    public function toAgent()
    {
        return $this->belongsTo(Agent::class, 'to_agent_id', 'id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_agent_id', 'id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_agent_id', 'id');
    }


    public function getFromNameAttribute()
    {
        switch ($this->type_from) {
            case 'AGENCY':
                return 'ĐL-' . optional($this->fromAgent)->name;
            case Constants::ACCOUNT_TYPE_STAFF:
                return 'NV-' . optional($this->fromUser)->fullname;
            case 'MASTER':
                return 'Nguồn';
            default:
                return null;
        }
    }

    public function getToNameAttribute()
    {
        switch ($this->type_to) {
            case 'AGENCY':
                return 'ĐL-' . optional($this->toAgent)->name;
            case Constants::ACCOUNT_TYPE_STAFF:
                return 'NV-' . optional($this->toUser)->fullname;
            case 'MASTER':
                return 'Nguồn';
            default:
                return null;
        }
    }

    public function getStaffToAttribute()
    {
        return optional($this->toUser)->id;
    }

    public function getStaffFromAttribute()
    {
        return optional($this->fromUser)->id;
    }

    public function getAgentFromAttribute()
    {
        return optional($this->fromAgent)->id;
    }

    public function getAgentToAttribute()
    {
        return optional($this->toAgent)->id;
    }

    public function getPriceAttribute($value)
    {
        return (int) $value;
    }
}
