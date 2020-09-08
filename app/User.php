<?php

namespace App;

use App\Http\Controllers\Jwt\Jwt;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements Jwt
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 通过主键查找用户信息
     * @return mixed
     */
    public static function primaryKey()
    {
        // TODO: Implement primaryKey() method.
        return 'id';
    }

    /**
     * token 到期时间(单位：小时）
     * @return mixed
     */
    public static function tokenExpire()
    {
        // TODO: Implement tokenExpire() method.
        return 1;
    }
    
}
