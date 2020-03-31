<?php
namespace CjsSms;


class SmsClient extends Sms
{

    /**
     * 验证短信
     * @param $phone 手机号
     * @param $smsCode  短信验证码
     * @param string $tplCode 发短信使用的短信模板代号
     * @return boolean
     */
    public function checkSms($phone, $smsCode,$tplCode='default') {
        $flag = false;


        return $flag;
    }

}