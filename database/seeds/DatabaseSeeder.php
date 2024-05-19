<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Cmd: php artisan db:seed
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $this->call([
            UserSeeder::class,
            AppGroupSeeder::class,
            AdsSeeder::class,
        ]);
    }
}
