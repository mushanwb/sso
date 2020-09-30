<?php


namespace App\Http\Controllers\Test;


use App\Http\Controllers\Controller;
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
        $userInfo = User::where('id',1)->first();

        if (!$userInfo) {
            return $this->_apiExit(40402);
        }

        $data['token'] = JwtController::encrypt($userInfo);

        return $this->_apiExit(200, $data);
    }

    /**
     * 测试 token 验证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifica(Request $request) {

        $token = $request->header('token');

        $info = JwtController::decrypt($token);

        if (!$info) {
            return $this->_apiExit(40101);
        }

        return $this->_apiExit(200, $info);
    }

    /**
     * 通过中间键获取用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInfoNeedLogin(Request $request) {

        // 由于再中间键中将 user_info 作为参数传入进来
        // 如果客户端也传入了 user_info 参数，这样将会覆盖客户端传入的 user_info 参数
        // 因此建议将客户端传入的参数使用 $request->all() 接收
        // 这样就可以使用 $all['user_info'] 来获取客户端的参数
        // 使用 $request->get('user_info') 来获取中间键验证的用户信息

        $all = $request->all();

        // 从中间键中获取用户信息时，这里只能用 get 方法
        $userInfo = $request->get('user_info');

        $data['all'] = $all;

        $data['userInfo'] = $userInfo;

        return $this->_apiExit(200, $data);

    }


}
