<?php

namespace App\Services\SSC;

use App\Helpers\Constants;
use App\Services\RSA\RSAService;

class SscService
{
    private $sscid;
    private $month;

    private $school;
    private $clazz;
    private $student;

    protected $rsa_service;

    public function __construct(RSAService $RSAService)
    {
        $this->rsa_service = $RSAService;
    }

    /**
     * @return mixed
     */
    public function getSscid()
    {
        return $this->sscid;
    }

    /**
     * @param mixed $sscid
     */
    public function setSscid($sscid): void
    {
        $this->sscid = $sscid;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month): void
    {
        $this->month = $month;
    }

    /**
     * @return mixed
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @param mixed $school
     */
    public function setSchool($school): void
    {
        $this->school = $school;
    }

    /**
     * @return mixed
     */
    public function getClazz()
    {
        return $this->clazz;
    }

    /**
     * @param mixed $clazz
     */
    public function setClazz($clazz): void
    {
        $this->clazz = $clazz;
    }

    /**
     * @return mixed
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param mixed $student
     */
    public function setStudent($student): void
    {
        $this->student = $student;
    }

    /**
     * Lấy danh sách trường kết nối
     */
    public function getSchools()
    {
        $school_list = null;

        $data = [
            'RequestId' => uniqid()
        ];

        $data_base64_encode = base64_encode(json_encode($data));
        $sign = $this->rsa_service->sign($data_base64_encode);

        // Call api truy vấn ds trường học chấp nhận thanh toán
        $input = [
            "cmd" =>  Constants::SSC_CMD_GETSCHOOLS,
            "partnercode" => "SSC1",
            "data" => $data_base64_encode,
            "signature" => $sign
        ];

        $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 120);

        if ($res_str) {
            $res_obj = json_decode($res_str);
            if (isset($res_obj->Schools) && $res_obj->Schools) {
                foreach ($res_obj->Schools as $key => $school) {
                    $school_list[] = [
                        'school_code' => isset($school->SchoolCode) ? $school->SchoolCode : null,
                        'school_name' => isset($school->SchoolName) ? $school->SchoolName : null,
                        'province' => isset($school->Province) ? $school->Province : null,
                        'address' => isset($school->Address) ? $school->Address : null,
                    ];
                }
            }
        }

        return $school_list;
    }

    /**
     * Tìm kiếm học sinh
     */
    public function findStudent($params)
    {
        $student_name = isset($params['student_name']) ? $params['student_name'] : null;
        $year_of_birth = isset($params['year_of_birth']) ? $params['year_of_birth'] : null;
        $school_code = isset($params['school_code']) ? $params['school_code'] : null;

        $array_student = null;

        if ($student_name) {
            $data = [
                'RequestId' => uniqid(),
                'StudentName' => $student_name,
                'YearOfBirth' => $year_of_birth,
                'SchoolCode' => $school_code,
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_FINDSTUDENT,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 60);

            if ($res_str) {
                $res_obj = json_decode($res_str);
                if (isset($res_obj->Students) && $res_obj->Students) {
                    $birthday = isset($res_obj->Birthday) ? $res_obj->Birthday : null;
                    $students = isset($res_obj->Students) ? $res_obj->Students : null;
                    foreach ($students as $key => $student) {
                        $array_student[] = [
                            'account_type' => Constants::ACCOUNT_TYPE_STUDENT,
                            'sscid' => isset($student->SSCId) ? $student->SSCId : null,
                            'fullname' => isset($student->FullName) ? $student->FullName : null,
                            'birthday' => ($birthday) ? date('d/m/Y', strtotime($birthday)) : null,
                            'address' => isset($student->Address) ? $student->Address : null,
                            'phone' => isset($student->Phone) ? $student->Phone : null,
                            //'school_code' => isset($student->SchoolCode) ? $student->SchoolCode : null,
                            //'school_name' => isset($student->SchoolName) ? $student->SchoolName : null,
                            'class_code' => isset($student->ClassCode) ? $student->ClassCode : null,
                            'class_name' => isset($student->ClassName) ? $student->ClassName : null,
                            'father_name' => isset($student->FatherName) ? $student->FatherName : null,
                            'mother_name' => isset($student->MotherName) ? $student->MotherName : null,
                            'avatar' => asset(Constants::DEFAULT_STUDENT_AVATAR)
                        ];
                    }
                }
            }
        }

        return $array_student;
    }

    /**
     * Tìm học sinh qua mã SSCId
     */
    public function findStudentById()
    {
        $student = null;

        if ($this->sscid) {
            $data = [
                'RequestId' => uniqid(),
                'SSCId' => $this->sscid
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_FINDSTUDENTBYID,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 60);

            if ($res_str) {
                $res_obj = json_decode($res_str);
                if (isset($res_obj->SSCId) && $res_obj->SSCId) {
                    $birthday = isset($res_obj->Birthday) ? $res_obj->Birthday : null;

                    $student = [
                        'account_type' => Constants::ACCOUNT_TYPE_STUDENT,
                        'sscid' => isset($res_obj->SSCId) ? $res_obj->SSCId : null,
                        'fullname' => isset($res_obj->FullName) ? $res_obj->FullName : null,
                        'birthday' => ($birthday) ? date('d/m/Y', strtotime($birthday)) : null,
                        'address' => isset($res_obj->Address) ? $res_obj->Address : null,
                        'phone' => isset($res_obj->Phone) ? $res_obj->Phone : null,
                        'display_name' => isset($res_obj->SSCId) ? $res_obj->SSCId : null,
                        'school_code' => isset($res_obj->SchoolCode) ? $res_obj->SchoolCode : null,
                        'school_name' => isset($res_obj->SchoolName) ? $res_obj->SchoolName : null,
                        'class_code' => isset($res_obj->ClassCode) ? $res_obj->ClassCode : null,
                        'class_name' => isset($res_obj->ClassName) ? $res_obj->ClassName : null,
                        'father_name' => isset($res_obj->FatherName) ? $res_obj->FatherName : null,
                        'mother_name' => isset($res_obj->MotherName) ? $res_obj->MotherName : null,
                        'avatar' => asset(Constants::DEFAULT_STUDENT_AVATAR)
                    ];
                }
            }
        }

        return $student;
    }

    /**
     * Tìm kiếm danh sách bill
     *
     * @param false $is_checking
     * @return array|null
     */
    public function findBill($is_checking = false)
    {
        $bill_info = null;

        if ($this->sscid) {
            $data = [
                'RequestId' => uniqid(),
                'SSCId' => $this->sscid
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_FINDBILL,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 120);

            if ($res_str) {
                $res_obj = json_decode($res_str);

                // Nếu chỉ kiểm tra còn nợ học phí không thì ngắt luôn
                if ($is_checking) {
                    if (isset($res_obj->ErrorCode) && ($res_obj->ErrorCode == -3)) {
                        return Constants::SSC_CHECK_BILL_OUT_OF_DEBT;
                    } else {
                        return Constants::SSC_CHECK_BILL_HAS_DEBT;
                    }
                }

                if (isset($res_obj->SSCId) && $res_obj->SSCId) {
                    $bills = isset($res_obj->Bills) ? $res_obj->Bills : null;
                    $bills = collect($bills)->map(function ($bill){
                        $bill->month = isset($bill->Month) ? $bill->Month : null;
                        $bill->amount = isset($bill->Amount) ? $bill->Amount : 0;
                        $bill->hash = isset($bill->Hash) ? $bill->Hash : null;
                        //
                        unset($bill->Month);
                        unset($bill->Amount);
                        unset($bill->Hash);
                        //
                        return $bill;
                    })->all();

                    $bill_info = [
                        'account_type' => Constants::ACCOUNT_TYPE_STUDENT,
                        'sscid' => isset($res_obj->SSCId) ? $res_obj->SSCId : null,
                        'fullname' => isset($res_obj->StudentName) ? $res_obj->StudentName : null,
                        'address' => isset($res_obj->Address) ? $res_obj->Address : null,
                        'school_code' => isset($res_obj->SchoolCode) ? $res_obj->SchoolCode : null,
                        'school_name' => isset($res_obj->SchoolName) ? $res_obj->SchoolName : null,
                        'class_code' => isset($res_obj->ClassCode) ? $res_obj->ClassCode : null,
                        'class_name' => isset($res_obj->ClassName) ? $res_obj->ClassName : null,
                        'bills' => $bills,
                        'total_amount' => isset($res_obj->TotalAmount) ? $res_obj->TotalAmount : 0,
                    ];
                }
            }
        }

        return $bill_info;
    }

    /**
     * Tìm kiếm thông tin chi tiết bill
     */
    public function findBillDetail()
    {
        $bill_detail = null;

        if ($this->sscid && $this->month) {
            $data = [
                'RequestId' => uniqid(),
                'SSCId' => $this->sscid,
                'Month' => $this->month
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_FINDBILLDETAIL,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 120);

            if ($res_str) {
                $res_obj = json_decode($res_str);
                if (isset($res_obj->Bills) && $res_obj->Bills) {
                    $bill_detail = collect($res_obj->Bills)->map(function ($bill){
                        $bill->bill_id = isset($bill->BillId) ? $bill->BillId : null;
                        $bill->month = isset($bill->Month) ? $bill->Month : null;
                        $bill->amount = isset($bill->Amount) ? $bill->Amount : 0;
                        $bill->note = isset($bill->Note) ? $bill->Note : null;
                        //
                        unset($bill->BillId);
                        unset($bill->Month);
                        unset($bill->Amount);
                        unset($bill->Note);
                        //
                        return $bill;
                    })->all();
                }
            }
        }

        return $bill_detail;

    }

    /**
     * Thanh toán bill
     */
    public function payBill($bills)
    {
        // Kiểm tra xem có còn nợ học phí không. Nếu không thì thông báo luôn. Nếu có thì tiến hành gạch nợ
        $check = $this->findBill(true);

        if ($check == Constants::SSC_CHECK_BILL_OUT_OF_DEBT) {
            return [
                'code' => 200,
                'status' => Constants::BILL_STATUS_OUT_OF_DEBIT,
                'error' => 'Bạn không còn nợ học phí',
                'data' => null
            ];
        }

        // Tạo bản tin để gạch nợ hóa đơn cần thanh toán
        $pay_result = [
            'code' => 404,
            'status' => Constants::BILL_STATUS_BILL_NOT_FOUND,
            'error' => 'Không tìm thấy hóa đơn',
            'data' => null
        ];

        if ($this->sscid && $bills) {
            $data = [
                'RequestId' => uniqid(),
                'SSCId' => $this->sscid,
                'Bills' => $bills
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_PAYBILL,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 120);

            if ($res_str) {
                $res_obj = json_decode($res_str);
                if (isset($res_obj->SSCId) && $res_obj->SSCId && isset($res_obj->BankDate) && $res_obj->TotalAmount) {
                    $result = [
                        'sscid' => isset($res_obj->SSCId) ? $res_obj->SSCId : null,
                        'bank_date' => isset($res_obj->BankDate) ? $res_obj->BankDate : null,
                        'total_amount' => isset($res_obj->TotalAmount) ? $res_obj->TotalAmount : null,
                        'ssc_request_id' => isset($res_obj->SSCRequestId) ? $res_obj->SSCRequestId : null,
                        'request_id' => isset($res_obj->RequestId) ? $res_obj->RequestId : null,
                        'fee' => 0
                    ];

                    $pay_result = [
                        'code' => 200,
                        'status' => Constants::BILL_STATUS_PAYMENT_SUCCESSFULLY,
                        'error' => 'Thanh toán hóa đơn thành công',
                        'data' => $result
                    ];

                } else {
                    if (isset($res_obj->ErrorCode) && ($res_obj->ErrorCode == -98)) {
                        // Hóa đơn đã được thanh toán hoặc không còn nợ
                        $pay_result = [
                            'code' => 200,
                            'status' => Constants::BILL_STATUS_SYSTEM_BUSY,
                            'error' => 'Không tìm thấy hóa đơn thanh toán hoặc bạn không còn nợ học phí',
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $pay_result;
    }

    /**
     * Hủy bill
     */
    public function cancelBill($request_id)
    {
        $result = null;

        if ($this->sscid && $request_id) {
            $data = [
                'RequestId' => uniqid(),
                'SSCId' => $this->sscid,
                'CancelRequestId' => $request_id
            ];

            $data_base64_encode = base64_encode(json_encode($data));
            $sign = $this->rsa_service->sign($data_base64_encode);

            // Call api truy vấn học sinh
            $input = [
                "cmd" =>  Constants::SSC_CMD_CANCELBILL,
                "partnercode" => "SSC1",
                "data" => $data_base64_encode,
                "signature" => $sign
            ];

            $res_str = sendRequest(Constants::SSC_BILLGW_URL, $input, 'POST', true, false, null, 120);

            if ($res_str) {
                $res_obj = json_decode($res_str);
                if (isset($res_obj->SSCId) && $res_obj->SSCId) {
                    //
                }
            }
        }

        return $result;
    }

    /**
     * Kiểm tra tình trạng thanh toán
     */
    public function checkPay()
    {

    }


}
