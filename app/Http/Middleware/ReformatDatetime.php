<?php

namespace App\Http\Middleware;

use Closure;

class ReformatDatetime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Lấy toàn bộ request
        $input = $request->all();

        // Tạo mảng mặc định nếu user chưa config
        $reformat_default = [
            'created_at',
            'updated_at',
            'start_date',
            'end_date',
            'birthday',
            'start_time',
            'end_time',
            'time_working',
        ];

        // Duyệt từng phần tử để thay thế định dạng sang kiểu mặc định của MySQL
        $reformat = config('middleware.reformat_datetime', $reformat_default);
        if (is_array($reformat) && count($reformat) > 0) {
            foreach ($reformat as $value) {
                if (isset($input[$value])) {
                    $datetime = str_replace(['/', '_'], ['-', '-'], $input[$value]);
                    //$input[$value] = date('Y-m-d H:i:s', strtotime($datetime));
                    $input[$value] = $datetime;
                }
            }
        }

        // Thay thế giá trị mới trong request
        $request->replace($input);

        return $next($request);
    }
}
