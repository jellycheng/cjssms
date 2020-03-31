<?php
namespace CjsSms;

class Util
{
    public static function htmlspecialchars($val, $flags = ENT_QUOTES, $encoding = 'utf-8') {
        if(!$val || !is_string($val)) {
            return $val;
        }
        return htmlspecialchars($val, $flags, $encoding);
    }


    //去除输入有争议的字符
    public static function randStr($length = 6, $type = 0) {
        //l、o、L、O、数字0、数字1
        if($type == 1) {
            $chars = "ABCDEFGHIJKMNPQRSTUVWXYZ23456789";
        } else {
            $chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789";
        }
        $str = "";
        for($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    public static function getLimit($page, $page_size = 10) {
        if($page<1) {
            $page = 1;
        }
        $start = ($page-1)*$page_size;
        $limit = " limit " . $start . " ," . $page_size;
        return $limit;
    }

    public static function parseKey($key) {
        $key   =  trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s]/', $key)) {
            $key = '`'.$key.'`';
        }
        return $key;
    }


    public static function parseValue($value){
        $value = addslashes(stripslashes($value));//重新加斜线，防止从数据库直接读取出错
        return "'".$value."'";
    }

    public static function getInsertSql($table, $insertData = []) {
        $fields = [];
        $values = [];
        foreach($insertData as $k=>$v) {
            $fields[] = static::parseKey($k);
            $values[] = static::parseValue($v);
        }
        $sql = sprintf('INSERT INTO `%s` (%s) VALUES(%s);',
                        $table,implode(',',$fields),implode(',',$values)
                );
        return $sql;
    }

    public static function getSmsKey($token) {
        return sprintf("sms:%s", $token);
    }

    public static function checkPhone($phone) {
        $flag = true;
        if(!preg_match('/^1[3456789][0-9]{9}$/', $phone)) {
            $flag = false;
        }
        return $flag;
    }

    public static function uuid($salt)
    {
        return md5($salt . uniqid(md5(microtime(true)), true) . microtime());
    }

    public static function json_encode($data, $options=null) {
        if(is_null($options)) {
            $options = 0;
            if (defined('JSON_UNESCAPED_SLASHES')) {
                $options |= JSON_UNESCAPED_SLASHES;
            }
            if (defined('JSON_UNESCAPED_UNICODE')) {
                $options |= JSON_UNESCAPED_UNICODE;
            }
        }
        return \json_encode($data, $options);
    }


    public static function dealSmsContent($tplcontent, $replaceParam = []) {
        $content = preg_replace_callback(
            '/\${\s*([A-Za-z_\-\.0-9]+)\s*}/',
            function (array $matches) use ($replaceParam) {
                $keyWord = $matches[1];
                if (isset($replaceParam[$keyWord])) {
                    $result = $replaceParam[$keyWord];
                } else {
                    $result = '';
                }
                return $result;
            },
            $tplcontent
        );
        return $content;
    }

}