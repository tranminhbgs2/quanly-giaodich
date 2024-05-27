<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
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
    public function actionsFunc()
    {
        return $this->hasMany(Position::class, 'function_id', 'id');
    }
/**
     * Get the is_default attribute.
     *
     * @return string
     */
    public function getIsDefaultAttribute()
    {
        return $this->attributes['is_default'] == 1 ? 'true' : 'false';
    }
}
