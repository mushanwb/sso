<?php


namespace App\Http\Controllers\Jwt;


use App\User;

class JwtController {

    // 签名认证算法
    private static $alg = "sha256";
    private static $jwtToken;

    /**
     * JwtController constructor.
     * @param $jwtToken
     */
    public function __construct() {
        self::$jwtToken = env("JWT_TOKEN");
    }


    /**
     * 加密对象需要实现 Jwt 接口
     * 生成 token 主要由三部分组成
     * header(头部) + payload(负载) + signatures(签名)
     * signatures 主要是对前两部分 以及服务端的 key 进行签名认证，防止数据被篡改
     * @param Jwt $encryptObj
     * @return string
     * @param Jwt $encryptObj
     */
    public static function encrypt(Jwt $encryptObj) {
        $header = self::header();
        $payload = self::payload($encryptObj);

        // 简单的拼接，如果要复杂的话，可以带上符号或者加盐等等。
        // 另外 JWT_TOKEN 需要复杂一点，不然容易被暴力破解，建议 32 位,用 hash 函数
        $str = $header . "+" . $payload . "+" . self::$jwtToken;
        $signatures = hash(self::$alg, $str);
        $token = $header . "." . $payload . "." . $signatures;

        return $token;
    }


    /**
     * token 的头部，无重要信息，主要是标识加密的函数
     * 采用 base64 进行编码
     * token 的头部，采用 base64 进行编码
     * @return string
     */
    private static function header() {
        $header = [
            'alg' => self::$alg,
            'type' => 'JWT'
        ];

        return base64_encode(json_encode($header));
    }

    /**
     * 信息部分，主要包含过期时间和用户主键
     * （JWT 包中还包含其他东西，我在这里没做那些）
     * @param Jwt $encryptObj
     * @return string
     */
    private static function payload(Jwt $encryptObj) {

        $addTime = $encryptObj::tokenExpire() * 60 * 60;
        $primaryKey = $encryptObj::primaryKey();

        $payload = [
            'exp' => time() + $addTime,
            'iat' => time(),
            'sub' => $encryptObj->$primaryKey
        ];

        return base64_encode(json_encode($payload));
    }

    /**
     * 解密，验证 token 是否被篡改，
     * 没有的话，则验证通过查询用户信息
     * @param $token
     * @return bool
     */
    public static function decrypt($token) {

        $data = explode('.' , $token);

        if (count($data) != 3) {
            return false;
        }

        $header = $data[0];
        $payload = $data[1];
        $signatures = $data[2];
        $str = $header . "+" . $payload . "+" . self::$jwtToken;

        $sign = hash(self::$alg, $str);

        // 签名验证不通过
        if ($sign != $signatures) {
            return false;
        }

        $payload = json_decode(base64_decode($payload), true);

        // 已过期
        if (time() > $payload['exp']) {
            return false;
        }

        // 通过模型查询用户信息，这里的 User 模型是写死的，
        // 可以在 payload 里面添加签发人，指定查询模型
        $info = User::where(User::primaryKey(), $payload['sub'])->first();
        return $info;
    }

}
