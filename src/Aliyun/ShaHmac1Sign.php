<?php
/**
 * HMAC-SHA1 签名
 */

namespace CjsSms\Aliyun;


class ShaHmac1Sign {

    public static function newInstance() {
        static $instance;
        if(!$instance) {
            $instance = new static();
        }
        return $instance;
    }
    //签名方法
    public function getMethod() {
        return 'HMAC-SHA1';
    }

    public function getType() {
        return '';
    }
    //签名版本
    public function getVersion() {
        return '1.0';
    }

    public function sign($string, $accessKeySecret) {
        return base64_encode(hash_hmac('sha1', $string, $accessKeySecret, true));
    }

}
