<?php


namespace App\Http\Controllers\Wechat;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use EasyWeChat\Factory;

class WechatLoginController extends Controller {

    protected $_appNotify;
    protected $config;
    public function __construct()
    {
        $this->config = config('wechat.official_account.default');
        $this->_appNotify = Factory::officialAccount($this->config);
    }


    /**
     * 公众号授权
     * 流程：
     * 当用户访问一个需要授权页面时，如 baidu.com
     * 前端需要调用该授权接口进行授权，授权完后微信会调用回调接口
     * 在回调接口里面保存用户信息，并且重定向到用户要访问的页面：baidu.com
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function wechatAuth(Request $request) {

        // 获取用户要访问页面的 url
        $url = $request->get('url');
        Log::info('重定向的url：' . $url);

        // 将 该重定向的 url 编码后拼接到回调接口中
        $url = '?url=' . urlencode($url);
        $this->config['oauth']['callback'] .= $url;
        $app = Factory::officialAccount($this->config);

        return $app->oauth->scopes(['snsapi_userinfo'])
            ->redirect();
    }

    /**
     * 授权回调流程：
     * 该接口为授权回调接口，在授权接口中将该接口地址一起给微信（一般写在配置中）
     * 用户点击授权后，微信将通过该回调地址将用户的 code 传过来
     * 由于在授权接口中将用户访问的页面 url 拼接到回调地址后面
     * 因此微信调用 回调接口的时候，后面会带有 ?url=xxx 等参数
     * 这些参数可以在回调接口中获取到
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request) {
        Log::info('获取用户的code:  ' . $request->get('code'));

        //从回调地址中获取需要重定向的 url 参数
        $targetUrl = $request->get('url','/');

        // 重定向的 url 上也有可能带有参数，因此需要用 & 判断
        // 我们需要将生成的 token 放到重定向的 url 中
        // 前端将会获取 url 中的 token，并且截取下来，放入 header 中
        // 并且指定 key 为 token （这个值随便，方式需要在取的时候一致），
        // 方便后端验证 token 的时候取值
        if (count(explode('&', $targetUrl)) > 1) {
            $targetUrl = $targetUrl . '&token=';
        } else {
            $targetUrl = $targetUrl . '?token=';
        }

        try {
            $wechatUserInfo = $this->_appNotify->oauth->user();
            $wechatUserInfo = $wechatUserInfo->original;
            Log::info('微信返回得用户信息：' . json_encode($wechatUserInfo));

            // 这里使用 unionid 判断用户是否注册
            $userInfo = User::where('unionid',$wechatUserInfo['unionid'])->first();
            // 如果用户已经存在，则返回 token 直接登录
            if ($userInfo) {
                // 通过公众号 openid 判断是否在公众号中授权
                if (!$userInfo->official_account_openid) {
                    // 没有在公众号中授权过，记录公众号的 openid
                    User::where('unionid',$wechatUserInfo['unionid'])->update(['official_account_openid' =>  $wechatUserInfo['openid']]);
                }
                return redirect()->to($targetUrl . JwtController::encrypt($userInfo));
            }

            // 没有则添加用户信息
            $save = [
                'official_account_openid' => $wechatUserInfo['openid'],
                'nickname' => $wechatUserInfo['nickname'],
                'headimgurl' => $wechatUserInfo['headimgurl'],
                'unionid' => $wechatUserInfo['unionid'],
                'sex' => $wechatUserInfo['sex'],
                'city' => $wechatUserInfo['city'],
                'province' => $wechatUserInfo['province'],
                'country' => $wechatUserInfo['country'],
                'created_at' => time(),
                'updated_at' => time()
            ];

            $id = DB::table('users')->insertGetId($save);
            $userInfo = User::where('id',$id)->first();
            return redirect()->to($targetUrl . JwtController::encrypt($userInfo));
        } catch (\Exception $e) {
            Log::error('用户授权出错：' . $e->getMessage());
            return redirect()->to($targetUrl);
        }

    }


}
