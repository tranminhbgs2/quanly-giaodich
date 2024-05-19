<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = Constants::TABLE_VERSIONS;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'version_name',
        'build',
        'platform',
        'is_active',
        'apply_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
