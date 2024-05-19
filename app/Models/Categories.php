<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = Constants::TABLE_CATEGORIES;
    public $timestamps = true;
    protected $fillable = [
        'code',
        'fee',
        'name',
        'note',
        'status',
    ];
}
