# sso 单点登录

**单点登录的思想：一次验证，多次使用。**
**有点类似授权的思想，在一个网站上登录成功后，可以在其他相关网站上不用再登录。**

## 思路

### 同一台服务器

对于同一台服务器的不同网站，可以使用 Session 或者 Redis 存储信息机制，不同网站访问
同一个 Session 或者 Redis ，获取信息并验证。

### 不同服务器

不同服务器也可以通过 Session 共享或者 Redis 共享机制去验证，但使用 JWT 是一个很不错的选择。


根据 JWT 的原理（签名认证算法），制作了一个简单的签名认证，方便理解 JWT 原理，生产环境还是
用 JWT 官方包较为合适。   

> JWT 跨平台认证原理：   
> JWT 使用签名给认证算法,将用户的唯一信息,比如自增 id ,以及设定过期时间等信息(统称客户端的信息),   
> 将这些客户端的信息和服务端复杂并且唯一的私钥，一起使用散列函数进行运算,得到唯一值(签名),   
> 然后将这些信息(客户端信息)和签名一起发送给服务端,服务通过私钥和客户端信息再次使用同样的散列函数进行运算   
> 将运算的结果和客户端的签名比对是否一样,如果一样,则表明客户端的信息并没有被篡改,因此可以通过用户唯一信息查出用户身份      
> 如果信息被篡改,则验证将不会通过。因此,用户在登录成功后,得到服务端认证成功的令牌,便可以进行登录操作      
> 同时只需要知道服务端的私钥,和加密方法,这个令牌将可以在任何地方被解密,因此如果需要在不同服务器上验证,则只需要将私钥和加密方法设成一致即可    

[JWT Laravel版本 Github源码](https://github.com/tymondesigns/jwt-auth)   
[JWT Laravle版本 Composer文档](https://packagist.org/packages/tymon/jwt-auth)  

### 登录

- 账号密码登录

- 关注公众号登录

- 微信公众号登录

- 微信小程序登录

### 账号密码登录

依赖函数: password_hash  password_verify
```
# 加密
$hash = password_hash('123456', PASSWORD_BCRYPT);

# 验证
password_verify('123456', $hash)
```
这两个函数在 Laravel 中被封装成: 
```
# 加密
$hash = bcrypt('123456');
## 或者
$hash = Hash::make('123456');


# 验证
Hash::check('123456', $hash);
``` 

PHP 中这两个函数的加密和验证过程，可以参考这个文章：[浅谈密码验证](https://github.com/mushanwb/casual_write/issues/19)

登录接口:
```
api/account/login
```
登录流程:
> 用户需要先通过注册接口，将密码用 bcrypt 函数进行加密，存入数据库   
> 在用户登录的时候，通过用户输入的账号，拿到数据库中的密文密码   
> 在通过 Hash::check 验证用户输入的明文密码和数据库中的密文密码是否匹配   
> 如果验证通过，则将用户的信息生成 token 输出

### 微信公众号登录

依赖工具: EasyWeChat
```
# Laravel < 5.8
composer require "overtrue/laravel-wechat:~4.0"

# Laravel >= 5.8
composer require "overtrue/laravel-wechat:~5.0"
```

授权接口:
```
api/wechat/wechatAuth
```
授权流程:
> 当用户访问一个需要授权页面时，如 baidu.com   
> 前端需要调用该授权接口进行授权，授权完后微信会调用回调接口   
> 在回调接口里面保存用户信息，并且重定向到用户要访问的页面：baidu.com   
> 当获取到用户的信息后，需要生成 token，并且将生成的 token 拼接到重定向的 url 后   
> 重定向到前端的页面后，前端需要将 url 上的 token 截取下来，放入 header 中，而不应该再放在 url 中 

### 微信小程序登录

授权接口:
```
api/mini/auth
```

授权流程:

> 前端会传入 3 个重要的参数，code, iv, encryptedData   
> 首先需要通过 code 获取 session_key   
> 将 session_key, iv, encryptedData 三个参数经行解密,得到用户信息即可

注意: 公众号和小程序需要绑定开放平台才会有 unionId，并且这两个获取的 openid 是不同的，
但是如果绑定到同一个主体上，unionId 是一样的，因此如果有必要的话，可以通过 unionId 判断是否同一个用户
