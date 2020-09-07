<?php


namespace App\Http\Controllers\Jwt;


class JwtController {

    // 签名认证算法
    private $alg = "sha1";

    /**
     * 加密对象需要实现 Jwt 接口
     * @param Jwt $encryptObj
     */
    public function encrypt(Jwt $encryptObj) {
        $header = $this->header();
        $payload = $this->payload($encryptObj);

    }


    /**
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

    private function payload(Jwt $encryptObj) {

        $addTime = $encryptObj->tokenExpire() * 60 * 60;

        $payload = [
            'exp' => time() + $addTime,
            'sub' => $encryptObj->primaryKey()
        ];

        return base64_encode(json_encode($payload));
    }


}
