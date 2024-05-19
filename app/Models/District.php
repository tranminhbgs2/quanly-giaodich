<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = Constants::TABLE_DISTRICTS;

    public $timestamps = true;

    protected $fillable = [
        'code',
        'name',
        'type',
        'province_id', 'province_code',
        'admission_code',
        'deleted_at'
    ];

    public function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }
}
