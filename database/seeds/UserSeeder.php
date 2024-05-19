<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current_time = \Illuminate\Support\Carbon::now();

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'admin',
                'username' => 'admin',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Admin',
                'display_name' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'tiendh',
                'username' => 'tiendh',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Đặng Hoàng Tiến',
                'display_name' => 'tiendh',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'doanpv',
                'username' => 'doanpv',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Phạm Văn Đoan',
                'display_name' => 'doanpv',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'minhtv',
                'username' => 'minhtv',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Trần Văn Minh',
                'display_name' => 'minhtv',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'trangth',
                'username' => 'trangth',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Trần Huyền Trang',
                'display_name' => 'trangth',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'hunglv',
                'username' => 'hunglv',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Lưu Văn Hưng',
                'display_name' => 'hunglv',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        \App\Models\User::updateOrCreate(
            [
                'sscid' => 'hieund',
                'username' => 'hieund',
            ],
            [
                'account_type' => 'SYSTEM',
                'fullname' => 'Nguyễn Đăng Hiếu',
                'display_name' => 'hieund',
                'password' => \Illuminate\Support\Facades\Hash::make('123456a@A'),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );
    }
}
