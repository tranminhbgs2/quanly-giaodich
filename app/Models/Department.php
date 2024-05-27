<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = Constants::TABLE_DEPARTMENTS;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'code',
        'description',
        'url',
        'status',
        'is_default',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function actionFunc()
    {
        return $this->belongsToMany(Position::class);
    }
}
