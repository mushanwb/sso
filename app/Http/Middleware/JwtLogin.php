<?php


namespace App\Http\Middleware;

use App\Http\Controllers\Jwt\JwtController;
use Closure;
use Illuminate\Http\Request;

class JwtLogin {

    public function handle(Request $request, Closure $next) {

        $token = $request->header('token');

        $jwt = new JwtController();

        $info = $jwt->decrypt($token);

        if (!$info) {
            return response()->json(['code' => 201, 'msg' => '验证失败，token 信息错误', 'data' => []]);
        }
        $user_info = ['user_info'=> $info];

        // 将用户信息通过该方法放入请求体中，方便再控制器中获取用户信息
        // 再控制器中必须使用 $request->get('user_info') 的方法获取用户信息
        // 但是这样可能会和客户端输入的 user_info 参数引起冲突
        // 因此建议再控制器中接收客户端输入的参数时，使用 $request->all() 接收

        $request->attributes->add($user_info);

        return $next($request);
    }

}
