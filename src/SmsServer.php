<?php
namespace CjsSms;

use CjsSms\Aliyun\AliyunSms;
use CjsToken\MysqlPdo;
use CjsToken\MysqlDbConfig;

class SmsServer extends Sms
{

    protected $smsConfig = [];
    protected $sms_db_key = 'db_sms';
    /**
     * 发送短信验证码
     * @param $phone 手机号
     * @param string $tplCode 短信模板ID
     * @param array $param 传给短信模板的参数，['code'=>'短信验证码']
     * @return array
     */
    public function sendSms($phone,$tplCode='default',$param=[]) {
        $ret = ['code'=>1,'msg'=>'发送失败'];
        $time = time();
        //获取模板配置
        $tplCodeInfo = $this->getSmsTplCodeInfo($tplCode);
        if(empty($tplCodeInfo)) {
            $ret['code']=2;
            $ret['msg']='短信模板不存在';
            return $ret;
        }
        if($tplCodeInfo['is_locked'] == SmsEnum::IS_LOCKED_LOCK) {
            $ret['code']=3;
            $ret['msg']='短信模板被锁定，请更换模板代号';
            return $ret;
        }
        //发送短信，插入log
        switch ($tplCodeInfo['channel_type']) {
            case 1:
                $content = Util::dealSmsContent($tplCodeInfo['content'], $param);
                $smsLog = [
                        'msg_id'=>Util::uuid($phone),
                        'tpl_id'=>$tplCodeInfo['id'],
                        'tpl_snapshoot'=>Util::json_encode($tplCodeInfo),
                        'sign'=>$tplCodeInfo['sign'],
                        'phone'=>$phone,
                        'content'=>$content, //短信内容
                        'params'=>Util::json_encode($param),//接口传进来的变量
                        'channel_type'=>$tplCodeInfo['channel_type'],
                        'status'=>SmsEnum::SEND_STATUS_ING,
                        'create_time'=>$time,
                ];
                $logId = $this->insertSmsLog($smsLog);
                $isOk = AliyunSms::newInstance($this->getSmsConfig('aliyun'))->setSmsLog($smsLog)->sendSms();
                if($isOk) {//发送成功
                    $ret['code']=0;
                    $ret['msg']='success';
                    $this->updateSmsLogStatus4Id($logId, ['status'=>SmsEnum::SEND_STATUS_SUCCESS]);
                } else {
                    $this->updateSmsLogStatus4Id($logId, ['status'=>SmsEnum::SEND_STATUS_FAIL]);
                }
                break;
            default:
                $ret['code']=4;
                $ret['msg']='不支持的短信通道,channel_type=' . $tplCodeInfo['channel_type'];
                break;
        }
        return $ret;
    }

    public function initSmsServer($smsConfig = []) {
        $this->smsConfig = array_merge($this->smsConfig, $smsConfig);
        return $this;
    }

    public function getSmsConfig($key = null) {
        if(is_null($key)) {
            return $this->smsConfig;
        }
        return isset($this->smsConfig[$key])?$this->smsConfig[$key]:[];
    }

    public function getSmsDbKey()
    {
        return $this->sms_db_key;
    }

    public function setSmsDbKey($sms_db_key)
    {
        $this->sms_db_key = $sms_db_key;
        return $this;
    }

    public function getTableName($tbl) {
        $smsDbConfig = MysqlDbConfig::getInstance()->getDbConfig($this->getSmsDbKey());
        $prefix = isset($smsDbConfig['prefix'])?trim($smsDbConfig['prefix']):'';
        return $prefix . $tbl;
    }

    //获取短信模板信息
    public function getSmsTplCodeInfo($tplCode) {
        $tableName = $this->getTableName('sms_tpl');
        $smsPdo = MysqlPdo::getInstance(MysqlDbConfig::getInstance()->getDbConfig($this->getSmsDbKey()));
        $selectSql = sprintf("select * from %s where code ='%s' and is_delete='%s' limit 1; ",
            $tableName, Util::htmlspecialchars($tplCode),SmsEnum::IS_DELETE_NORMAL);
        $dataOne = $smsPdo->getOne($selectSql);
        if(!$dataOne) {
            $dataOne = [];
        }
        return $dataOne;
    }

    public function insertSmsLog($param) {
        $tableName = $this->getTableName('sms_sendlog');
        $smsPdo = MysqlPdo::getInstance(MysqlDbConfig::getInstance()->getDbConfig($this->getSmsDbKey()));
        $insertSql = Util::getInsertSql($tableName, $param);
        $insertid = $smsPdo->insert($insertSql);
        if(!$insertid) {
            $insertid = 0;
        }
        return $insertid;
    }

    public function updateSmsLogStatus4Id($id, $ext=[]) {
        $time = time();
        $tableName = $this->getTableName('sms_sendlog');
        $userTokenPdo = MysqlPdo::getInstance(MysqlDbConfig::getInstance()->getDbConfig($this->getSmsDbKey()));
        $updateSql = sprintf("update %s set update_time='%s',status='%s' where id ='%s' and is_delete='%s' limit 1;",
                            $tableName, $time,$ext['status'], $id, SmsEnum::IS_DELETE_NORMAL);
        $affectNum = $userTokenPdo->exec($updateSql);
        return $affectNum;
    }

}