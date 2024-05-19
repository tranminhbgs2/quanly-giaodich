<?php

use Illuminate\Database\Seeder;

class AdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current_time = \Illuminate\Support\Carbon::now();

        \App\Models\Ads::updateOrCreate(
            [ 'name' => 'Slider 01' ],
            [
                'image' => 'storage/uploads/sliders/slider-01.jpg',
                'action_type' => 'EVENT',
                'record_id' => null,
                'redirect_to' => null,
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\Ads::updateOrCreate(
            [ 'name' => 'Slider 02' ],
            [
                'image' => 'storage/uploads/sliders/slider-02.jpg',
                'action_type' => 'EVENT',
                'record_id' => null,
                'redirect_to' => null,
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\Ads::updateOrCreate(
            [ 'name' => 'Slider 03' ],
            [
                'image' => 'storage/uploads/sliders/slider-03.jpg',
                'action_type' => 'EVENT',
                'record_id' => null,
                'redirect_to' => null,
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );
    }
}
