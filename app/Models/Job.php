<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = Constants::TABLE_JOBS;

    public $timestamps = false;

    protected $fillable = ['queue', 'payload', 'attempts', 'reserved_at', 'available_at'];
}
