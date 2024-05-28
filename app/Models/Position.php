<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_POSITIONS;
    public $timestamps = true;

    protected $fillable = [
        'function_id',
        'name',
        'code',
        'description',
        'status',
        'url',
        'is_default',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function groupRule()
    {
        return $this->belongsTo(Department::class, 'function_id', 'id');
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_position', 'position_id', 'user_id');
    }
}
