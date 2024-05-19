<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = Constants::TABLE_WARDS;

    public $timestamps = true;

    protected $fillable = [
        'code',
        'name',
        'type',
        'province_id', 'province_code',
        'district_id', 'district_code',
        'full_location',
        'deleted_at'
    ];

    public function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo('App\Models\District', 'district_id', 'id');
    }
}
