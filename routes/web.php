<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//生成 pc 端登录的二维码
Route::get('/wechat/loginQrcode','Wechat\WechatScanLogin@makeLoginQrcode');
Route::get('/wechat/scanLogin','Wechat\WechatScanLogin@scanLoginUserInfo');
