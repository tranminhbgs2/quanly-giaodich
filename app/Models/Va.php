<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Va extends Model
{
    protected $table = Constants::TABLE_VAS;
    public $timestamps = true;
    protected $appends = ['status_name'];

    protected $fillable = [
        'school_id',
        'student_id',
        'customer_id',
        'sscid',
        'bank_id',
        'card_number',
        'account_number',
        'owner',
        'bank_name',
        'bank_code',
        'branch',
        'balance',
        'created_by',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\User', 'customer_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\Student', 'student_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Bank', 'bank_id', 'id');
    }

    public function getStatusNameAttribute()
    {
        return statusVa($this->status);
    }
}
