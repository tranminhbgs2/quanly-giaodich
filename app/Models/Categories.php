<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
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
