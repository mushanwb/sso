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

// 微信公众号入口
Route::any('/wechat/entrance','Wechat\WechatApiController@entrance');
// 微信授权和回调
Route::any('/wechat/wechatAuth','Wechat\WechatLoginController@wechatAuth');
Route::any('/wechat/callback','Wechat\WechatLoginController@callback');

// 账号密码登录
Route::post('account/login','Account\AccountLoginController@login');

// 测试
Route::get('test/generate','Test\TestController@generate');
Route::get('test/verifica','Test\TestController@verifica');

Route::Group(['middleware' => ['jwt.login']], function () {
    Route::get('test/user_info','Test\TestController@userInfoNeedLogin');
});
