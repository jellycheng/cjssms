<?php
/**
 * 发送阿里云短信demo
 */
require_once __DIR__ . '/common.php';
$smsConfig = require_once __DIR__ . '/config/sms.php';
$aliyunConfig = $smsConfig['aliyun'];

$aliyunSmsObj = CjsSms\Aliyun\AliyunSms::newInstance($aliyunConfig);
$aliyunSmsObj->sendSms();

