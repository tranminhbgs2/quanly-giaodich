<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pos extends Model
{
    protected $table = Constants::TABLE_POS;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'bank_code',
        'method',
        'hkd_id',
        'fee',
        'total_fee',
        'fee_cashback',
        'price_pos',
        'created_by',
        'status',
    ];

    /**
     * Tính xem vị trí này thuộc phòng/ban nào
     */
    public function hokinhdoanh()
    {
        return $this->belongsTo(HoKinhDoanh::class, 'hkd_id', 'id');
    }
    /**
     * Mối quan hệ nhiều-nhiều với Agent thông qua agent_pos
     */
    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_pos')->withPivot('status', 'fee')->withTimestamps();
    }

     /**
     * Deactive tất cả các bản ghi agent_pos liên quan đến pos này
     */
    public function deactivateAgents()
    {
        DB::table('agent_pos')
            ->where('pos_id', $this->id)
            ->update(['status' => Constants::USER_STATUS_LOCKED]);
    }

    /**
     * Thêm một Agent vào Pos sau khi deactive các bản ghi cũ
     *
     * @param int $agentId
     * @param float $fee
     * @return void
     */
    public function addAgentWithDeactivation($agentId, $fee)
    {
        // Deactivate all existing records for this pos
        $this->deactivateAgents();

        // Add the new agent record
        $this->agents()->attach($agentId, ['status' => Constants::USER_STATUS_ACTIVE, 'fee' => $fee, 'created_at' => now(), 'updated_at' => now(), 'created_by' => auth()->id()]);
    }

    /**
     * Lấy các agents đang active
     */
    public function activeAgents()
    {
        return $this->belongsToMany(Agent::class, 'agent_pos')
            ->wherePivot('status', Constants::USER_STATUS_ACTIVE)
            ->withPivot('status', 'fee')
            ->withTimestamps();
    }
}
