<?php

namespace App\Repositories\Setting;

use App\Models\Version;
use App\Repositories\BaseRepo;

class VersionRepo extends BaseRepo
{
    public function version($platform)
    {
        if (in_array($platform, ['android', 'ios'])) {
            $query = Version::select('*')
                ->where('platform', $platform)
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get();

            return $query;
        }

        return false;
    }
}
