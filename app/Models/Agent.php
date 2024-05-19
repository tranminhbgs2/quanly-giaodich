<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes; // Thêm dòng này để sử dụng Soft Deletes
    protected $table = Constants::TABLE_AGENCY;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'surrogate',
        'address',
        'phone',
        'manager_id',
        'status',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function managerBy()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }
}
