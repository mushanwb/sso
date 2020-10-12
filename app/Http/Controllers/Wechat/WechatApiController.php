<?php


namespace App\Http\Controllers\Wechat;


use App\Http\Controllers\Controller;
use App\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WechatApiController extends Controller {

    protected $_appNotify;
    public function __construct()
    {
        $this->_appNotify = Factory::officialAccount(config('wechat.official_account.default'));
    }

    //公众号事件入口
    public function entrance(Request $request)
    {
        Log::info('公众号事件入口');
        $this->_appNotify->server->push(function ($message){
            Log::info('入口事件信息： ', $message);
            switch ($message['MsgType']) {
                case 'event':

                    // 事件的 key 就是生成二维码时设定的参数
                    // 如果用户没有关注，则在设定的参数前面加上 qrscene_
                    // 因此该 if 判断前面表名用户已经关注过了，后面标识没有关注现在才开始关注
                    // 对于这两种情况，用户扫码后都需要给他登录
                    if ($message['EventKey'] == "qrcode_login" || $message['EventKey'] == "qrscene_qrcode_login") {
                        // 用户扫码后，将用户信息放入缓存中
                        $wechatScanLogin = new WechatScanLogin();
                        $wechatScanLogin->scanLogin($message);
                        $str = config('wechatmessage.login');
                    }

                    // EventKey 为 null 表名用户并不是通过扫码过来后关注的
                    // 因此回复的文案应该不同
                    if ($message['Event'] == 'subscribe' && $message['EventKey'] == 'null') {
                        $str = config('wechatmessage.subscribe');
                    }
                    $this->_appNotify->customer_service->message($str)->to($message['FromUserName'])->send();
                    break;
                case 'text':
                    $msg = trim($message['Content']);
                    return config('wechatmessage.keyWord.keyWord1');
            }
        });
        $response = $this->_appNotify->server->serve();
        return $response;
    }


}
