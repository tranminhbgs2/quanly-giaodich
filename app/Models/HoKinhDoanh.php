<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class HoKinhDoanh extends Model
{
    protected $table = Constants::TABLE_POS;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'surrogate',
        'phone',
        'address',
        'status',
    ];

}
