<?php

use Illuminate\Database\Seeder;

class AppGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current_time = \Illuminate\Support\Carbon::now();

        $app_list = [
            'SCHOOL' => [
                'APP_HOC_PHI' => [ 'name' => 'Học phí', 'icon' => 'storage/uploads/app-utils/app-hoc-phi.png', 'sort' => 1 ],
                'APP_DIEM_DANH' => [ 'name' => 'Điểm danh', 'icon' => 'storage/uploads/app-utils/app-diem-danh.png', 'sort' => 2 ],
                'APP_HOC_BA' => [ 'name' => 'Học bạ', 'icon' => 'storage/uploads/app-utils/app-hoc-ba.png', 'sort' => 3 ],
                'APP_THU_VIEN' => [ 'name' => 'Thư viện', 'icon' => 'storage/uploads/app-utils/app-thu-vien.png', 'sort' => 4 ],
                'APP_BAO_HIEM' => [ 'name' => 'Bảo hiểm', 'icon' => 'storage/uploads/app-utils/app-bao-hiem.png', 'sort' => 5 ],
                'APP_DUA_DON' => [ 'name' => 'Đưa đón', 'icon' => 'storage/uploads/app-utils/app-dua-don.png', 'sort' => 6 ],
                'APP_ELEARNING' => [ 'name' => 'E-Learning', 'icon' => 'storage/uploads/app-utils/app-elearning.png', 'sort' => 7 ],
                'APP_EBOOK' => [ 'name' => 'Ebook', 'icon' => 'storage/uploads/app-utils/app-ebook.png', 'sort' => 8 ]
            ],
            'SERVICE' => [
                'APP_HOC_TIENG_ANH' => [ 'name' => 'Học tiếng Anh', 'icon' => 'storage/uploads/app-utils/app-hoc-tieng-anh.png', 'sort' => 1],
                'APP_DAY_KEM' => [ 'name' => 'Dạy kèm', 'icon' => 'storage/uploads/app-utils/app-day-kem.png', 'sort' => 2 ],
                'APP_MUA_SACH' => [ 'name' => 'Mua sách', 'icon' => 'storage/uploads/app-utils/app-mua-sach.png', 'sort' => 3],
                'APP_DAT_XE' => [ 'name' => 'Đặt xe', 'icon' => 'storage/uploads/app-utils/app-dat-xe.png', 'sort' => 4 ],
                'APP_VE_XEM_PHIM' => [ 'name' => 'Vé xem phim', 'icon' => 'storage/uploads/app-utils/app-ve-xem-phim.png', 'sort' => 5 ],
                'APP_VE_MAY_BAY' => [ 'name' => 'Vé máy bay', 'icon' => 'storage/uploads/app-utils/app-ve-may-bay.png', 'sort' => 6 ],
                'APP_THE_DIEN_THOAI' => [ 'name' => 'Thẻ điện thoại', 'icon' => 'storage/uploads/app-utils/app-the-dien-thoai.png', 'sort' => 7 ],
                'APP_DAU_TU' => [ 'name' => 'Đầu tư', 'icon' => 'storage/uploads/app-utils/app-dau-tu.png', 'sort' => 8 ]
            ]
        ];

        /**
         * Nhóm sản phẩm học đường -------------------------------------------------------------------------------------
         */
        $app_group_one = \App\Models\AppGroup::updateOrCreate(
            [
                'name' => 'Ứng dụng học đường',
                'code' => 'APP_SCHOOL',
            ],
            [
                'icon' => null,
                'status' => 1,
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        if (isset($app_group_one->id) && $app_group_one->id > 0) {
            foreach ($app_list['SCHOOL'] as $key => $app) {
                \App\Models\App::updateOrCreate(
                    [
                        'app_group_id' => $app_group_one->id,
                        'name' => $app['name'],
                        'code' => $key,
                    ],
                    [
                        'description' => $app['name'],
                        'icon' => $app['icon'],
                        'is_public' => 1,
                        'sort' => $app['sort'],
                        'status' => 1,
                        'created_at' => $current_time,
                        'updated_at' => $current_time
                    ]
                );
            }
        }

        /**
         * Nhóm sản phẩm dịch vụ, dịch vụ VAS --------------------------------------------------------------------------
         */
        $app_group_two = \App\Models\AppGroup::updateOrCreate(
            [
                'name' => 'Dịch vụ',
                'code' => 'APP_SERVICE',
            ],
            [
                'icon' => null,
                'status' => 1,
                'created_at' => $current_time,
                'updated_at' => $current_time
            ]
        );

        if (isset($app_group_two->id) && $app_group_two->id > 0) {
            foreach ($app_list['SERVICE'] as $key => $app) {
                \App\Models\App::updateOrCreate(
                    [
                        'app_group_id' => $app_group_two->id,
                        'name' => $app['name'],
                        'code' => $key,
                    ],
                    [
                        'description' => $app['name'],
                        'icon' => $app['icon'],
                        'is_public' => 1,
                        'sort' => $app['sort'],
                        'status' => 1,
                        'created_at' => $current_time,
                        'updated_at' => $current_time
                    ]
                );
            }
        }
    }
}
