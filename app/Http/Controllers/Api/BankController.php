<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bank\BankListingRequest;
use App\Repositories\Bank\BankRepo;
use Illuminate\Http\Request;

class BankController extends Controller
{
    protected $bankRepo;

    public function __construct(BankRepo $bankRepo)
    {
        $this->bankRepo = $bankRepo;
    }

    /**
     * API lấy ds bank sau khi login
     * URL: {{url}}/api/v1/banks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listing(BankListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->bankRepo->listing($params);
        $total = $this->bankRepo->listing($params, true);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Danh sách ngân hàng',
                'data' => [
                    "total_elements" => $total,
                    "total_page" => ceil($total / $params['page_size']),
                    "page_no" => intval($params['page_index']),
                    "page_size" => intval($params['page_size']),
                    "data" => $data
                ],
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin ngân hàng',
            'data' => null
        ]);
    }

    public function getAll()
    {
        $data = $this->bankRepo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách ngân hàng',
            'data' => $data
        ]);
    }

    /**
     * API lấy ds bank không cần login
     * URL: {{url}}/api/v1/banks/no-auth
     *
     * @param BankListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listingNoAuth(BankListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->bankRepo->listing($params);
        $total = $this->bankRepo->listing($params, true);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Danh sách ngân hàng',
                'data' => $data,
                'meta' => [
                    'page_index' => intval($params['page_index']),
                    'page_size' => intval($params['page_size']),
                    'records' => $total,
                    'pages' => ceil($total / $params['page_size'])
                ]
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin ngân hàng',
            'data' => null
        ]);
    }

    /**
     * API nhận biến động số dư từ PVCBank
     *
     * ftType: Loại giao dịch(String)
     * amount: Giá trị (Long)
     * balance: Số dư sau giao dịch (Long)
     * senderBankId: Mã ngân hàng gửi (Dùng bảng mã ngân hàng của PVCB) (String)
     * description: Nội dung (String)
     * tranId: Mã Giao dịch ở ngân hàng (FT) (String)
     * tranDate: Ngày giao dịch (YYYY-MM-DD HH:mm:ss) (String)
     * currency: Tiền tệ (String)
     * tranStatus: Trạng thái giao dịch (String)
     * conAmount: Giá trị qui ra việt nam đồng (Long)
     * numberOfBeneficiary: Số thẻ nhận giao dịch, “0” nếu là gd nhận vào tài khoản. (String)
     *
     * @param Request $request
     */
    public function ipnPvcombank(Request $request)
    {
        $all = request()->all();
        //
        $ftType = request('ftType', null);
        $amount = request('amount', null);
        $balance = request('balance', null);
        $senderBankId = request('senderBankId', null);
        $description = request('description', null);
        $tranId = request('tranId', null);
        $tranDate = request('tranDate', null);
        $currency = request('currency', null);
        $tranStatus = request('tranStatus', null);
        $conAmount = request('conAmount', null);
        $numberOfBeneficiary = request('numberOfBeneficiary', null);
        //



    }

}
