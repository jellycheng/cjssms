<?php
require_once __DIR__ . '/common.php';

//db配置
$dbConfig = require_once __DIR__ . '/config/db.php';
\CjsToken\MysqlDbConfig::getInstance()->setDbConfig($dbConfig);
//初始化短信配置
$smsServerObj = CjsSms\SmsServer::getInstance();
$smsServerObj->initSmsServer(include __DIR__ . '/config/sms.php');

$phone = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
if(!$phone || !CjsSms\Util::checkPhone($phone)) {
    exit("手机号不正确" . PHP_EOL);
}
$smsTplCode = 'COMMON_VERIFY_CODE';
$param = ['code'=>mt_rand(1111, 9999)];
$isOk = $smsServerObj->sendSms($phone,$smsTplCode,$param);//发短信
var_dump($isOk);
echo PHP_EOL;
