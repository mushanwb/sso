# sso 单点登录

**单点登录的思想：一次验证，多次使用。**
**有点类似授权的思想，在一个网站上登录成功后，可以在其他相关网站上不用再登录。**

## 思路

### 同一台服务器

对于同一台服务器的不同网站，可以使用 session 或者 redis 存储信息机制，不同网站访问
同一个 session 或者 redis ，获取信息并验证。

### 不同服务器

不同服务器也可以通过 session 共享或者 redis 共享机制去验证，但使用 JWT 是一个很不错的选择。


根据 JWT 的原理（签名认证算法），制作了一个简单的签名认证，方便理解 JWT 原理，生产环境还是
用 JWT 官方包较为合适。   
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
这两个函数在 laravel 中被封装成: 
```
# 加密
$hash = bcrypt('123456');

# 验证
Hash::check('123456', $hash);
``` 

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

依赖工具: easywechat
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

