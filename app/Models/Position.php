<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = Constants::TABLE_POSITIONS;
    public $timestamps = true;

    protected $fillable = [
        'function_id',
        'name',
        'code',
        'description',
        'status',
        'url',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function groupRule()
    {
        return $this->belongsToMany(Department::class);
    }
}
