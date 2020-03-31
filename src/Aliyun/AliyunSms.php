<?php
/**
 * 阿里云短信
 */
namespace CjsSms\Aliyun;


use CjsSms\Util;

class AliyunSms
{
    protected $url = "https://dysmsapi.aliyuncs.com/";
    protected $accessKeyId; //访问者身份,阿里云分配的访问id
    protected $accessKeySecret;//阿里云分配的短信密钥

    protected $smsLog = [];

    public static function newInstance($config =[]) {
        return new static($config);
    }

    public function __construct($config = [])
    {
        if(isset($config['url'])) {
            $this->setUrl($config['url']);
        }
        if(isset($config['AccessKeyId'])) {
            $this->setAccessKeyId($config['AccessKeyId']);
        }
        if(isset($config['AccessKeySecret'])) {
            $this->setAccessKeySecret($config['AccessKeySecret']);
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }

    public function setAccessKeyId($accessKeyId)
    {
        $this->accessKeyId = $accessKeyId;
        return $this;
    }

    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    public function setAccessKeySecret($accessKeySecret)
    {
        $this->accessKeySecret = $accessKeySecret;
        return $this;
    }

    public function getSmsLog()
    {
        return $this->smsLog;
    }

    public function setSmsLog($smsLog)
    {
        $this->smsLog = $smsLog;
        return $this;
    }

    public function sendSms() {
        $flag = false;
        $time = time();
        $action = 'SendSms';
        $accessKeyId = $this->getAccessKeyId();
        $accessKeySecret = $this->getAccessKeySecret();

        $smsLog = $this->getSmsLog();

        $phone = isset($smsLog['phone'])?$smsLog['phone']:'';
        if(!$phone) {
            return $flag;
        }
        //$smsContent = isset($smsLog['content'])?$smsLog['content']:'';
        $outId = isset($smsLog['msg_id'])?$smsLog['msg_id']:'msg_id';
        $signName = isset($smsLog['sign'])?$smsLog['sign']:'';
        $channel = '';
        $tplParamsKey = []; //模板中配置需要的参数
        //模板快照
        $tpl_snapshoot = isset($smsLog['tpl_snapshoot'])?$smsLog['tpl_snapshoot']:'';
        if($tpl_snapshoot) {
            $tpl_snapshootAry = \json_decode($tpl_snapshoot, true);
            $channel = isset($tpl_snapshootAry['channel'])?$tpl_snapshootAry['channel']:'';
            if(isset($tpl_snapshootAry['has_param']) && $tpl_snapshootAry['has_param'] && isset($tpl_snapshootAry['param'])) {
                if($tpl_snapshootAry['param']) {
                    $tplParamsKey = json_decode($tpl_snapshootAry['param'], true);
                }
            }
        }
        $templateCode = '';
        if($channel) {
            $channel = \json_decode($channel, true);
            $templateCode = isset($channel['code'])?$channel['code']:'';
        }
        $urlParam = [
                'Action'=>$action,
                'AccessKeyId'=>$accessKeyId,
                'Format'=>'json', //json,xml
                'RegionId'=>'cn-hangzhou',
                'SignatureMethod'=>ShaHmac1Sign::newInstance()->getMethod(),
                'SignatureNonce'=>Util::uuid($outId),
                'SignatureVersion'=>ShaHmac1Sign::newInstance()->getVersion(),
                'Timestamp'=>date('Y-m-d\TH:i:s\Z', $time), //格式为yyyy-MM-ddTHH:mm:ssZ 示例：2018-01-01T12:00:00Z
                'Version'=>'2017-05-25',
        ];
        $urlParam['OutId'] = $outId;
        $urlParam['PhoneNumbers'] = $phone;
        $urlParam['SignName'] = $signName;
        $urlParam['TemplateCode'] = $templateCode;
        $paramsData = isset($smsLog['params'])?json_decode($smsLog['params'],true):[];
        $tmp = [];
        foreach ($tplParamsKey as $k=>$v) {
            if(isset($paramsData[$k])) {
                $tmp[$k] = $paramsData[$k];
            } else {
                $tmp[$k] = $v;
            }
        }
        if($tmp) {
            $urlParam['TemplateParam'] = Util::json_encode($tmp);
        }

        $sortQueryString = $this->param2Str($urlParam);
        $sign = ShaHmac1Sign::newInstance()->sign($this->stringToSign('GET', $sortQueryString), $accessKeySecret.'&');
        $sign = $this->specialUrlEncode($sign);
        //curl请求
        $smsUrl = sprintf('%s?Signature=%s&%s',$this->getUrl(),$sign,$sortQueryString);
        $ret = $this->request4get($smsUrl);

        return $flag;
    }

    protected function specialUrlEncode($string) {
        $result = urlencode($string);
        $result = str_replace(['+', '*'], ['%20', '%2A'], $result);
        $result = preg_replace('/%7E/', '~', $result);
        return $result;
    }

    protected function param2Str($parameters) {
        ksort($parameters);
        $canonicalized = '';
        foreach ($parameters as $key => $value) {
            $canonicalized .= '&' . $this->specialUrlEncode($key) . '=' . $this->specialUrlEncode($value);
        }
        return mb_substr($canonicalized, 1);
    }

    protected function stringToSign($method, $str) {
        return $method . '&%2F&' . $this->specialUrlEncode($str);
    }

    protected function request4get($smsUrl, $ext = []) {
        //{"Message":"OK","RequestId":"9D696506-53E1-4E97-B95D-3B158E0BB93C","BizId":"708319185652234112^0","Code":"OK"}
        $ret = file_get_contents($smsUrl);

        return $ret;
    }

}