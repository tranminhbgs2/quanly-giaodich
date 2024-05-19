<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Announcement\AnnounDeleteRequest;
use App\Http\Requests\Announcement\AnnounGetDetailRequest;
use App\Http\Requests\Announcement\AnnounGetListingRequest;
use App\Http\Requests\Announcement\AnnounStoreRequest;
use App\Http\Requests\Announcement\AnnounUnreadCounterRequest;
use App\Models\AnnouncementUser;
use App\Repositories\Announcement\AnnouncementRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    protected $notice;

    public function __construct(AnnouncementRepo $announcementRepo)
    {
        $this->notice = $announcementRepo;
    }

    /**
     * API lấy thông báo ở trang chủ
     * URL: {{url}}/api/v1/announcements
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(AnnounGetListingRequest $request)
    {
        $params['is_listing'] = request('is_listing', 0);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        //
        $params['page_index'] = ($params['page_index'] > 0) ? $params['page_index'] : 1;
        $params['page_size'] = ($params['page_size'] > 0) ? $params['page_size'] : 10;

        $total = 0;
        if ($params['is_listing'] == 1) {
            $data = $this->notice->getListing([], false, false);
            $total = $this->notice->getListing([], false, true);
            // Nếu đã login
            if (Auth::id()) {
                $arrRead = AnnouncementUser::select('announcement_id')->distinct()->where('user_id', Auth::id())->pluck('announcement_id')->toArray();
                $data = collect($data)->map(function ($notice) use ($arrRead){
                    $notice['is_read'] = in_array($notice['id'], $arrRead) ? 1 : 0;
                    return $notice;
                })->all();
            }
        } else {
            $data = $this->notice->getListing([], true, false);
            $total = $this->notice->getListing([], true, true);
        }

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => ($params['is_listing'] == 1) ? 'Danh sách thông báo' : 'Thông báo ở trang chủ',
                'data' => $data,
                'meta' => [
                    'page_index' => $params['page_index'],
                    'page_size' => $params['page_size'],
                    'records' => $total,
                    'pages' => ceil($total / $params['page_size'])
                ]
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);
    }

    /**
     * API lấy thông tin chi tiết thông báo
     * URL: {{url}}/api/v1/announcements/detail/1
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(AnnounGetDetailRequest $request, $id)
    {
        if (is_numeric($id) && $id > 0) {
            $params['id'] = request('id', null);
            $data = $this->notice->getDetail($params);
        } else {
            $data = [
                'code' => 422,
                'error' => 'Id phải là số nguyên dương, nhỏ nhất là 1',
                'data' => null
            ];
        }

        return response()->json($data);
    }

    /**
     * API lấy số lượng tin chưa đọc
     * URL: {{url}}/api/v1/announcements/unread-counter
     *
     * @param AnnounUnreadCounterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCounter(AnnounUnreadCounterRequest $request)
    {
        $data = $this->notice->unreadCounter([], Auth::user());

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Tổng số thông báo chưa đọc',
                'data' => [
                    'total_unread' => $data
                ]
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);
    }

    /**
     * API thêm mới thông báo
     * URL: {{url}}/api/v1/announcements/store
     *
     * @param AnnounStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AnnounStoreRequest $request)
    {
        $params['app_id'] = $request->input('app_id');
        $params['school_id'] = $request->input('school_id');
        $params['name'] = $request->input('name');
        $params['summary'] = $request->input('summary');
        $params['start_date'] = $request->input('start_date');
        $params['end_date'] = $request->input('end_date');
        $params['content'] = $request->input('content');
        $params['notes'] = $request->input('notes');
        $params['status'] = $request->input('status', 1);
        //
        $response = $this->notice->store($params);
        if ($response) {
            return response()->json([
                'code' => 200,
                'error' => 'Tạo mới thông báo thành công',
                'data' => $response
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);

    }

    /**
     * API xóa vật lý thông báo
     * URL: {{url}}/api/v1/announcements/delete/1
     *
     * @param AnnounDeleteRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(AnnounDeleteRequest $request, $id)
    {
        if ($id) {
            $params['id'] = $id;

            $response = $this->notice->delete($params);
            if ($response) {
                return response()->json([
                    'code' => 200,
                    'error' => 'Xóa thông báo thành công',
                    'data' => null
                ]);
            }

            return response()->json([
                'code' => 400,
                'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
                'data' => null
            ]);

        } else {
            return response()->json([
                'code' => 422,
                'error' => 'Truyền thiếu SSC-ID',
                'data' => null
            ]);
        }
    }

}
