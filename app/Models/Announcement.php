<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = Constants::TABLE_ANNOUNCEMENTS;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id',
        'school_id',
        'name',
        'summary',
        'image',
        'start_date',
        'end_date',
        'content',
        'notes',
        'status',
        'created_by',
        'deleted_at',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            $value = Constants::DEFAULT_APP_ICON;
        }

        return asset($value);
    }

    /**
     * Tạo liên kết với bảng trung gian
     *
     * @return $this
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\User',
            Constants::TABLE_ANNOUNCEMENT_USER,
            'announcement_id',
            'user_id'
        )->withTimestamps();
    }
}
