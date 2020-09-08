<?php


namespace App\Http\Controllers\Test;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Jwt\Jwt;
use App\Http\Controllers\Jwt\JwtController;
use App\User;
use Illuminate\Http\Request;

class TestController extends Controller {


    public function login(Request $request) {
        $userInfo = User::where('id',83)->first();

        $jwt = new JwtController();

        return $jwt->encrypt($userInfo);
    }


}
