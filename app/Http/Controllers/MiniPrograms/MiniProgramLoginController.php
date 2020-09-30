<?php


namespace App\Http\Controllers\MiniProgram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;

class MiniProgramLoginController extends Controller {

    protected $_appNotify;

    public function __construct() {
        $config = config('wechat.mini_program.default');
        $this->_appNotify = Factory::miniProgram($config);
    }

    public function auth(Request $request) {

        $code = $request->post('code');
        $iv = $request->post('iv');
        $encryptedData = $request->post('encryptedData');
        if (empty($code) || empty($iv) || empty($encryptedData)) {
            return $this->_apiExit(40001);
        }

        try {
            //通过code获取用户的 session_key
            $userInfoByCode = $this->_appNotify->auth->session($code);
            Log::info("微信code获取用户信息：" . json_encode($userInfoByCode));

            // 解密用户信息
            $miniProgramUserInfo = $this->_appNotify->encryptor->decryptData($userInfoByCode['session_key'], $iv, $encryptedData );
            Log::info("解密用户信息：" . json_encode($miniProgramUserInfo));

            // 这里使用 openid 判断用户是否注册，
            // 如果小程序和公众号是同一个主题，则可以用 unionId 判断是否是同一个用户
            $userInfo = User::where('openid',$miniProgramUserInfo['openId'])->first();
            // 如果不用户已经存在，则添加用户信息到数据库
            if (!$userInfo) {

                $save = [
                    'nickname' => $miniProgramUserInfo['nickName'],
                    'headimgurl' => $miniProgramUserInfo['avatarUrl'],
                    'openid' => $miniProgramUserInfo['openId'],
                    'unionid' => $miniProgramUserInfo['unionId'],
                    'sex' => $miniProgramUserInfo['gender'],
                    'city' => $miniProgramUserInfo['city'],
                    'province' => $miniProgramUserInfo['province'],
                    'country' => $miniProgramUserInfo['country']
                ];

                $id = User::insertGetId($save);
                $userInfo = User::where('id',$id)->first();
            }

            $data['token'] = JwtController::encrypt($userInfo);
            return $this->_apiExit(200, $data);

        } catch (\Exception $e) {
            Log::error('用户授权出错：' . $e->getMessage());
            return $this->_apiExit(50002);
        }

    }


}
