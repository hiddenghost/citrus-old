<?php

// 应用公共函数库文件

use think\Request;
use think\Log;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

/**
 * 写入日志 (废弃)
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
function write_log($values, $dir)
{
    if (is_array($values))
        $values = print_r($values, true);
    // 日志内容
    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
    try {
        // 文件路径
        $filePath = $dir . '/logs/';
        // 路径不存在则创建
        !is_dir($filePath) && mkdir($filePath, 0755, true);
        // 写入文件
        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 隐藏敏感字符
 * @param $value
 * @return string
 */
function substr_cut($value)
{
    $strlen = mb_strlen($value, 'utf-8');
    if ($strlen <= 1) return $value;
    $firstStr = mb_substr($value, 0, 1, 'utf-8');
    $lastStr = mb_substr($value, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', $strlen - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version;
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 时间戳转换日期
 * @param $timeStamp
 * @return false|string
 */
function format_time($timeStamp)
{
    return date('Y-m-d H:i:s', $timeStamp);
}

/**
 * 左侧填充0
 * @param $value
 * @param int $padLength
 * @return string
 */
function pad_left($value, $padLength = 2)
{
    return \str_pad($value, $padLength, "0", STR_PAD_LEFT);
}

/**
 * 过滤emoji表情
 * @param $text
 * @return null|string|string[]
 */
function filter_emoji($text)
{
    // 此处的preg_replace用于过滤emoji表情
    // 如需支持emoji表情, 需将mysql的编码改为utf8mb4
    return preg_replace('/[\xf0-\xf7].{3}/', '', $text);
}
/**
 *方法二：获取随机字符串
 * @param int $randLength 长度
 * @param int $addtime 是否加入当前时间戳
 * @param int $includenumber 是否包含数字
 * @return string
 */
function rand_str($randLength = 6, $addtime = 1, $includenumber = 0)
{
    if ($includenumber) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randLength; $i++) {
        $randStr .= $chars[mt_rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr . time();
    }
    return $tokenvalue;
}

function get_img_pic($id){
    $detail = \think\Db::name('upload_file')->where(['file_id'=>$id])->find();
    $pic =  '/uploads/'.$detail['file_name'];
    return $pic;
}

function check_mobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^1[3,4,5,7,8,9]{1}[\d]{9}$#', $mobile) ? true : false;
}

/**
 * 验证字符串长度
 *
 * 通过给定的字符串及合法的范围来验证是否满足当前的条件
 *
 * @param string $string 需要验证的字串
 * @param int $max 允许的最大长度
 * @param int $min 允许的最少长度，默认为:0
 * @param string $encode 处理字串的编码类型, 默认为：'utf8'
 * @exception HVerifyException 验证异常
 */
function isStrLen($string, $name, $min, $max = 0)
{
    $len    = (strlen($string) + mb_strlen($string, 'UTF8')) / 2;
    if($min !== 0 && $min > $len) {
        return ['rs' => false, 'msg' => $name . '字符长度不能小于' . $min];
    }
    if($max != 0 && $max < $len) {
        return ['rs' => false, 'msg' => $name . '字符长度不能大于' . $max];
    }

    return ['rs' => true];
}

/**
 * 验证变量是不是空，包括0在内
 * 对于数组只支持一维的验证
 * @param mix $param 需要验证的变量
 * @return boolean
 * @exception none
 */
function isEmpty($param, $name = '')
{
    if(is_array($param)) {
        $param  = array_filter($param);
    }
    if(empty($param)) {
        if(!$name) {
            return true;
        }
        return false;
    }

    return false;
}

/**
 * 验证邮箱地址是否正确
 *
 * 用法：
 * <code>
 *  HVerify::isEmail('test');    //抛出验证异常
 *  HVerify::isEmail('example@example.com'); //验证正常
 * </code>
 *
 * @param  String $email 需要验证的邮箱地址
 * @return void
 * @throws HVerifyException
 */
function isEmail($email)
{
    if(!preg_match('/^[\w\-\.]+@[\w\-]+(\.\w+)+$/', $email)) {
        return false;
    }
    return true;
}

/**
 * 验证身份证件
 * @param  [type]  $id [description]
 * @return boolean     [description]
 */
function isIdcard($id)
{
    $set = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
    $ver = array('1','0','x','9','8','7','6','5','4','3','2');
    $arr = str_split($id);
    $sum = 0;
    for ($i = 0; $i < 17; $i++)
    {
        if (!is_numeric($arr[$i]))
        {
            return false;
        }
        $sum += $arr[$i] * $set[$i];
    }
    $mod = $sum % 11;
    if (strcasecmp($ver[$mod],$arr[17]) != 0)
    {
        return false;
    }

    return true;
}

/**
 * 得到随机密码 32位以内
 * @param $len
 */
function getRandPassword($len = 6)
{
    return substr(md5(time()), 0, $len);
}

/**
 * 格式化時間字符串
 * @param  String $datetime 時間信息
 * @return String 格式化后的時間
 */
function formatTime($datetime)
{
    $curTime    = $_SERVER['REQUEST_TIME'];
    $timestamp  = is_int($datetime) ? $datetime : strtotime($datetime);
    $subTime    = $curTime - $timestamp;
    if(604800 < $subTime) {     //一周以上就显示发表的具体时间
        return date('Y-m-d',$timestamp);
    }
    if(86400 < $subTime) {
        return ceil($subTime / 86400) . '天以前';
    }
    if(3600 < $subTime) {
        return ceil($subTime / 3600) . '小时以前';
    }
    if(60 < $subTime) {
        return ceil($subTime / 60) . '分钟以前';
    }
    if(10 < $subTime) {
        return $subTime . '秒以前';
    }

    return '刚刚';
}

/**
 * 对二维数组按多个字段排序 中文排序支持
 * @author 罗新华
 * @param  array  &$arr  [description]
 * @param  array  $keys  [description]
 * @param  array  $order [description]
 * @return [type]        [description]
 * 使用案例 sort_array_multi($data, ['name', 'score'], ['asc', 'desc']);
 */
function sort_array_multi(array &$arr, array $keys, array $order)
{
    //校验参数
    if ( count($keys) == ($times = count($order)) ) {
        for ( $i = 0, $j = 0; $j < $times; $i += 2, $j++ ) {
            foreach ( $arr as $k => $v ) {
                //原数组是否存在该字段
                if ( isset($v[$keys[$j]]) ) {
                    $params[$i][] = iconv('UTF-8', 'GBK', $v[$keys[$j]]);    //TODO 中文排序支持
                } else {
                    return false;
                }
            }
            if ( strtoupper($order[$j]) == 'ASC' ) {
                $params[$i + 1] = SORT_ASC;
            } else {
                $params[$i + 1] = SORT_DESC;
            }
        }
        $params[] = &$arr;
        return call_user_func_array('array_multisort', $params);
    } else {
        return false;
    }
}