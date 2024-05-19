<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Crontjob extends Model
{
    protected $table = Constants::TABLE_CRONTJOBS;

    public $timestamps = true;

    protected $fillable = ['id', 'message'];


}
