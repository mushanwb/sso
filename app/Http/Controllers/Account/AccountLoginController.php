<?php


namespace App\Http\Controllers\Account;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountLoginController extends Controller {

    /**
     * 账号密码登录：
     * 在用户账号注册的时候，需要将用户的密码进行加密存入数据库
     * 一定不能将密码明文存入数据库
     * 然后再用户登录的时候，验证用户输入的账号密码与数据库中的是否相同
     * 相同则返回用户信息和 token
     * 这里我用的密码验证是 php 自带的验证方式 Hash::check
     * 因此再用户注册的时候，存入到数据中的密码必须使用 php 自带的 bcrypt 加密方法
     * 该加密方法和验证方法原理后期研究一下
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {

        $account = $request->post('account');
        $password = $request->post('password');

        $userInfo = User::where('account', $account)->first();

        if (!$userInfo || !Hash::check($password, $userInfo->password)) {
            return response()->json(['code' => 201, 'msg' => '账号或密码错误', 'data' => []]);
        }

        $jwt = new JwtController();
        $data['token'] = $jwt->encrypt($userInfo);
        return response()->json(['code' => 200, 'msg' => '登录成功', 'data' => $data]);
    }


}
