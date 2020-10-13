<?php


namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WechatScanLogin extends Controller {

    protected $_appNotify;
    public function __construct()
    {
        $this->_appNotify = Factory::officialAccount(config('wechat.official_account.default'));
    }

    /**
     * 生成关注公众号登录的二维码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeLoginQrcode(Request $request) {

        // 通过扫码进入公众号中的事件参数，来生成二维码，这里事件参数为 qrcode_login，二维码过期时间为 6 分钟
        $result = $this->_appNotify->qrcode->temporary('qrcode_login', 10*60);

        $url = $this->_appNotify->qrcode->url($result['ticket']);

        // 将用户扫码事件参数存入 session 中
        $request->session()->put('user',$result['ticket']);
        Log::info('session存储信息：' . json_encode($request->session()->all()));

        $data['url'] = $url;
        return $this->_apiExit(200, $data);
    }


    /**
     * 用户扫码关注公众后需要将用户信息存储到数据库中
     * 同时需要将用户信息和当前二维码的信息放在缓存中
     * @param $message
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function scanLogin($message) {

        $wechatUserInfo = $this->_appNotify->user->get($message['FromUserName']);
        Log::info('获取用户扫码关注的信息：' . json_encode($wechatUserInfo));

        //用户是否注册，还是使用 unionid 来判断是否是同一个用户
//        $userInfo = User::where('unionid',$wechatUserInfo['unionid'])->first();
        $userInfo = User::where('official_account_openid',$wechatUserInfo['openid'])->first();
        Log::info('用户信息：' . json_encode($userInfo));

        // 用户不存在，添加用该用户信息
        if (!$userInfo) {
            Log::info('来了: ' . $wechatUserInfo['openid']);
            $save = [
                'official_account_openid' => $wechatUserInfo['openid'],
                'nickname' => $wechatUserInfo['nickname'],
                'headimgurl' => $wechatUserInfo['headimgurl'],
//                'unionid' => $wechatUserInfo['unionid'],
                'sex' => $wechatUserInfo['sex'],
                'city' => $wechatUserInfo['city'],
                'province' => $wechatUserInfo['province'],
                'country' => $wechatUserInfo['country'],
                'created_at' => time(),
                'updated_at' => time()
            ];

            Log::info('插入用户数据：   ' . json_encode($save));

            $id = DB::table('users')->insertGetId($save);
            Log::info('用户id：   ' . $id);
            $userInfo = User::where('id', $id)->first();
        } else {
            Log::info('到这来了来了');
            // 如果用户已经存在，查看用户是否有公众号的 openid
            if (!$userInfo->official_account_openid) {
                // 没有公众号的 openid，记录公众号的 openid
                User::where('official_account_openid',$wechatUserInfo['openid'])->update(['official_account_openid' =>  $wechatUserInfo['openid']]);
            }
        }

        // 将用户信息存入缓存中，使用用户扫码的 Ticket 唯一标识做为 key，用户信息为 value，过期时间为 6 分钟
        Cache::put($message['Ticket'], $userInfo, 6*60);
        Log::info('用户信息缓存：   ' . json_encode(Cache::get($message['Ticket'])));
    }

    /**
     * 该方法用于用户生成二维码后，需要前端轮询请求获取用户信息
     * 如果用户没有扫码关注，则缓存中没有用户信息，获取失败
     * 如果用户扫码了，则在公众号的入口事件中已经将用户信息存入到缓存中
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanLoginUserInfo(Request $request) {
        $ticket = $request->session()->get('user');
        Log::info("ticket:  " . $ticket);

        if (!$ticket) {
            Log::info('查看session中当前用户信息：' . json_encode($request->session()->all()));
            return $this->_apiExit(40102);
        }
        $userInfo = Cache::get($ticket);
        Log::info("用户信息 " . json_encode($userInfo));
        if (!$userInfo) {
            return $this->_apiExit(40403);
        }
        $request->session()->pull('user');
        Cache::forget($ticket);
        $userInfo['token'] = JwtController::encrypt($userInfo);
        $data['isLogin'] = "true";
        $data['user'] = $userInfo;
        return $this->_apiExit(200, $data);

    }


}
