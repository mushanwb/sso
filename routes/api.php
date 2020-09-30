<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// 微信
Route::any('/wechat/entrance','Wechat\WechatApiController@entrance'); // 微信公众号入口

Route::any('/wechat/wechatAuth','Wechat\WechatLoginController@wechatAuth'); // 公众号授权
Route::any('/wechat/callback','Wechat\WechatLoginController@callback'); // 微信授权回调

Route::any('/mini/auth','MiniPrograms\MiniProgramLoginController@auth'); // 小程序授权


// 账号密码登录
Route::post('account/login','Account\AccountLoginController@login');    // 账号登录
Route::post('account/register','Account\AccountLoginController@register');  // 账号注册


// 测试 JWT
Route::get('test/generate','Test\TestController@generate'); // jwt 生成
Route::get('test/verifica','Test\TestController@verifica'); // jwt 验证

Route::Group(['middleware' => ['jwt.login']], function () {
    Route::get('test/user_info','Test\TestController@userInfoNeedLogin');   // 测试获取用户信息
});
