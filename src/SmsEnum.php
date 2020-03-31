<?php
namespace CjsSms;

class SmsEnum
{

    //短信所有表is_delete字段常量值  ==================
    //正常
    const IS_DELETE_NORMAL = 0;
    //删除
    const IS_DELETE_DEL = 1;

    //t_sms_tpl.is_locked字段常量值 ==================
    //正常
    const IS_LOCKED_NORMAL = 0;
    //锁定
    const IS_LOCKED_LOCK = 1;

    //t_sms_sendlog.status 发送状态,0:等待发送, 1:发送中, 2:等待回执, 3:发送成功, 4:发送失败
    const SEND_STATUS_WAIT_SEND = 0;
    const SEND_STATUS_ING = 1;
    const SEND_STATUS_WAIT_RETURN = 2;
    const SEND_STATUS_SUCCESS = 3;
    const SEND_STATUS_FAIL = 4;

}
