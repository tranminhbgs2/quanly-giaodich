<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = Constants::TABLE_PROVINCES;

    public $timestamps = true;
}
