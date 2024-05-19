<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class AnnouncementUser extends Model
{
    protected $table = Constants::TABLE_ANNOUNCEMENT_USER;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id',
        'school_id',
        'announcement_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

}
