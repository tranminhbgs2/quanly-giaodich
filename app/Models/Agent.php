<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
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
