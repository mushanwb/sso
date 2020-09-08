<?php


namespace App\Http\Controllers\Jwt;


use App\User;

class JwtController {

    // 签名认证算法
    private $alg = "sha1";
    private $jwtToken;

    /**
     * JwtController constructor.
     * @param $jwtToken
     */
    public function __construct() {
        $this->jwtToken = env("JWT_TOKEN");
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
    public function encrypt(Jwt $encryptObj) {
        $header = $this->header();
        $payload = $this->payload($encryptObj);

        // 简单的拼接，如果要复杂的话，可以带上符号等等，
        // 另外 JWT_TOKEN 需要复杂一点，不然容易被暴力破解，建议 32 位,用 hash 函数
        $signatures = sha1($header . "+" . $payload . "+" . $this->jwtToken);
        $token = $header . "." . $payload . "." . $signatures;

        return $token;
    }


    /**
     * token 的头部，无重要信息，主要是标识加密的函数
     * 采用 base64 进行编码
     * token 的头部，采用 base64 进行编码
     * @return string
     */
    private function header() {
        $header = [
            'alg' => $this->alg,
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
    private function payload(Jwt $encryptObj) {

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
    public function decrypt($token) {

        $data = explode('.' , $token);

        if (count($data) != 3) {
            return false;
        }

        $sign = sha1($data[0] . "+" . $data[1] . "+" . $this->jwtToken);
        $signatures = $data[2];

        // 签名验证不通过
        if ($sign != $signatures) {
            return false;
        }

        $payload = json_decode(base64_decode($data[1]), true);

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
