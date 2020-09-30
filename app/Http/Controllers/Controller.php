<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //接口统一返回格式
    public function _apiExit(int $code, $data = '', $message = '' )
    {
        return response()->json([
            'code'    => $code,
            'message' => empty($message) ? config('errorcode.code')[$code] : $message,
            'data'    => empty($data) ? null : $data,
        ]);
    }

}
