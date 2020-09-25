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
        $this->_appNotify = Factory::officialAccount(config('wechat.official_account'));
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
}
