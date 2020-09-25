<?php


namespace App\Http\Controllers\Wechat;


use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use EasyWeChat\Factory;

class WechatLoginController extends Controller {

    protected $_appNotify;
    public function __construct()
    {
        $this->_appNotify = Factory::officialAccount(config('wechat.official_account'));
    }


    public function wechatAuth(Request $request) {

        $url = $request->get('url');
        Log::info('重定向的url：' . $url);

        return $this->_appNotify->oauth
            ->scopes(['snsapi_userinfo'])
            ->setRequest($request)
            ->redirect();
    }

    public function callback(Request $request) {
        Log::info('获取用户的code:  ' . $request->get('code'));
        $targetUrl = $request->get('url');
        Log::info('重定向的url：' . $targetUrl);

        $wechatUserInfo = $this->_appNotify->oauth->user();
        $wechatUserInfo = $wechatUserInfo->original;
        Log::info('微信返回得用户信息：' . json_encode($wechatUserInfo));
    }


}
