<?php

namespace App\Repositories\Announcement;

use App\Models\Announcement;
use App\Models\AnnouncementUser;
use App\Repositories\BaseRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AnnouncementRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getListing($params, $is_homepage = true, $is_counting = false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $status = isset($params['status']) ? $params['status'] : -1;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $keyword = translateKeyWord($keyword);
        $offset = ($page_index - 1) * $page_size;
        //
        $query = Announcement::select(['id', 'name', 'summary', 'image']);

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('summary', 'LIKE', "%" . $keyword . "%");
            });
        });

        if ($status >= 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', 1);
        }

        $query->orderBy('id', 'DESC');

        if ($is_homepage) {
            if ($is_counting) {
                return $query->count();
            } else {
                return $query->take(1)->first();
            }
        } else {
            if ($is_counting) {
                return $query->count();
            } else {
                if ($page_size > 0 && $offset >= 0) {
                    $query->take($page_size)->skip($offset);
                }
            }
        }

        return $query->get()->toArray();
    }

    /**
     * Hàm lấy thông tin chi tiết thông báo
     *
     * @param $params
     * @return array
     */
    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;

        if ($id) {
            $notice = Announcement::find($id);

            if ($notice) {
                // Cập nhật log đã đọc khi user login
                if (Auth::id() > 0) {
                    $notice->users()->attach(Auth::id());
                }
                //
                unset($notice->app_id);
                unset($notice->school_id);
                unset($notice->status);
                unset($notice->created_at);
                unset($notice->updated_at);

                $notice->is_read = 1;   // Đánh dấu là đã đọc

                return [
                    'code' => 200,
                    'error' => 'Thông tin chi tiết thông báo',
                    'data' => $notice
                ];
            } else {
                return [
                    'code' => 404,
                    'error' => 'Không tìm thấy thông tin chi tiết thông báo',
                    'data' => $notice
                ];
            }
        }

        return [
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng, thử lại sau.',
            'data' => null
        ];
    }

    /**
     * Hàm lấy số lượng tin chưa đọc
     *
     * @param $params
     * @param $user_obj
     * @return int
     */
    public function unreadCounter($params, $user_obj)
    {
        return rand(1, 10);
    }

    /**
     * Xử lý thêm mới thông báo
     *
     * @param $params
     * @return Announcement|bool
     */
    public function store($params)
    {
        $notice = new Announcement();

        $params['start_date'] = (isset($params['start_date']) && $params['start_date']) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $params['start_date']))) : null;
        $params['end_date'] = (isset($params['end_date']) && $params['end_date']) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $params['end_date']))) : null;

        $notice->fill($params);

        if ($notice->save()) {
            unset($notice->created_at);
            unset($notice->updated_at);
            return $notice;
        }

        return false;
    }

    /**
     * Xử lý xóa thông báo, xóa vật lý
     *
     * @param $params
     * @return bool
     */
    public function delete($params)
    {
        $id = (isset($params['id']) && $params['id']) ? $params['id'] : null;

        if ($id > 0) {
            $notice = Announcement::where('id', $id)->first();
            if ($notice) {
                $notice->status = 3;
                $notice->save();
                $notice->delete();
                return true;
            }
        }

        return false;
    }



}
