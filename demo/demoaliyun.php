<?php
/**
 * 发送阿里云短信demo
 */
require_once __DIR__ . '/common.php';
$smsConfig = require_once __DIR__ . '/config/sms.php';
$aliyunConfig = $smsConfig['aliyun'];

$tplCodeInfo = [
    'channel' => '{"code": "SMS_173425248"}',
    'has_param' => '1',
    'param' => '{"code": ""}'
];
$phone = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
if(!$phone || !CjsSms\Util::checkPhone($phone)) {
    exit("手机号不正确" . PHP_EOL);
}

$code = mt_rand(111, 99999);
$smsLog = [
    'msg_id'=>CjsSms\Util::uuid($phone),
    'tpl_snapshoot'=>CjsSms\Util::json_encode($tplCodeInfo),
    'sign'=>'xx商城',
    'phone'=>$phone,
    'params'=>CjsSms\Util::json_encode(['code'=>$code]),//接口传进来的变量
];
echo '短信验证码：' . $code . PHP_EOL;
$isOk = CjsSms\Aliyun\AliyunSms::newInstance($aliyunConfig)->setSmsLog($smsLog)->sendSms();

var_dump($isOk);
echo PHP_EOL;
