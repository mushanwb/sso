<?php


namespace App\Http\Controllers\Jwt;


interface Jwt {

    /**
     * 通过主键查找用户信息
     * @return mixed
     */
    public function primaryKey();

    /**
     * token 到期时间(单位：小时）
     * @return mixed
     */
    public function tokenExpire();



}
