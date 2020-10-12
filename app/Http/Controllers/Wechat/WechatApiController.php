<?php


namespace App\Http\Controllers\Wechat;


use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
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
                    if ($message['Event'] == 'subscribe') {      //关注
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
}
