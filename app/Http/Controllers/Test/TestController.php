<?php


namespace App\Http\Controllers\Test;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\Jwt;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use Illuminate\Http\Request;

class TestController extends Controller {


    /**
     * 测试：token 生成
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request) {
        $userInfo = User::where('id',83)->first();

        if (!$userInfo) {
            return response()->json(['code' => 201, 'msg' => '用户信息不存在', 'data' => []]);
        }

        $jwt = new JwtController();

        $data['token'] = $jwt->encrypt($userInfo);

        return response()->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 测试 token 验证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifica(Request $request) {

        $token = $request->header('token');

        $jwt = new JwtController();

        $info = $jwt->decrypt($token);

        if (!$info) {
            return response()->json(['code' => 201, 'msg' => '验证失败，token 信息错误', 'data' => []]);
        }

        return response()->json(['code' => 200, 'msg' => '验证成功', 'data' => $info]);
    }


}
