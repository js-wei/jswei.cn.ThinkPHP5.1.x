<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function timestamp()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1)+floatval($t2))*1000);
}

/**
 * 实时图片
 * @param string $image_path
 */
function image_round($image_path='')
{
    if (!$image_path) {
        return '';
    }
    return $image_path. '?_id=' .time();
}
/**
 * 请求数据
 * @param string $url
 * @param array $data
 * @param string $method
 * @return array
 */
function http($url='', $data=[], $method='get')
{
    if (!$url) {
        $this->flag = false;
        $this->msg=[
            'code'=>804,
            'massage'=>'错误的请求操作'
        ];
    }
    if (!$data) {
        $this->flag = false;
        $this->msg=[
            'code'=>805,
            'massage'=>'没有请求的参数'
        ];
    }
    if (is_object($data)) {
        $data = json_decode(json_encode($data), true);
    }
    $curl = new \Curl\Curl();
    $response = $curl->$method($url, $data);
    return json_decode($response->response, true);
}


/**
 * 微信浏览器
 * @return bool
 */
function is_wei_xin()
{
    $agent = request()->header()['user-agent'];
    if (strpos($agent, 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * 打印函数
 * @param array $array
 */
function p($array)
{
    dump($array, 1, '<pre>', 0);
}

/**
 * 去除转义字符
 * @param $array
 * @return mixed
 */
function strips_lashes_array(&$array)
{
    while (list($key, $var) = each($array)) {
        if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {
            if (is_string($var)) {
                $array[$key] = stripslashes($var);
            }
            if (is_array($var)) {
                $array[$key] = strips_lashes_array($var);
            }
        }
    }
    return $array;
}

/**
 * 判断是否存在汉字
 * @param $str
 * @return bool
 */
function has_chiness($str)
{
    if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str)>0) {
        $flag= true;
    } elseif (preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)>0) {
        $flag= true;
    } else {
        $flag =false;
    }
    return $flag;
}

/**
 * 日期转成时间戳
 * @param $str
 * @return false|int
 */
function str2time($str)
{
    if (has_chiness($str)) {
        $result = preg_replace('/([\x80-\xff]*)/i', '', $str);	//去掉汉字
        if (strstr($str, '小时前')) {
            $d = strtotime('-'.$result.' hour');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '秒前')) {
            $d = strtotime('-'.$result.' second');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '分钟前')) {
            $d = strtotime('-'.$result.' minute');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '昨天')) {
            $d = strtotime('-2 day');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '前天')) {
            $d = strtotime('-2 day');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '天前')) {
            $d = strtotime('-'.$result.' day');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '月前')) {
            $d = strtotime('-'.$result.' months');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '年前')) {
            $d = strtotime('-'.$result.' year');
            $str = date('Y-m-d H:i:s', $d);
        } elseif (strstr($str, '-')) {
            $str = date('Y', time())."-".$str;
        } else {
            $str = preg_replace('/([{年}{月}{日}])/u', '/', $str);
        }
    }
    return strtotime($str);
}
/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else {
        return $output;
    }
}
/**
 * @author 魏巍
 * @description 检测邮箱格式
 */
function check_email($email)
{
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    if (preg_match($pattern, $email)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 计算时间差
 * @param $start
 * @param $end
 * @return array
 */
function time_diff($start, $end)
{
    $end = time();
    $cha = $end -$start;

    $minute=floor($cha/60);
    $hour=floor($cha/60/60);
    $day=floor($cha/60/60/24);
    return [
        'min'=>$minute,
        'hour'=>$hour,
        'day'=>$day
    ];
}

/**
 * 加解密
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return bool|string
 * @example:
 *   $str= '1234';
 *   $auth =  auth_code($str,'ENCODE'); //加密
 *   $str = auth_code($auth,'DECODE'); //解密
 *   p($auth);
 */
function auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0, $auth_key='jswei30')
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;

    // 密匙
    $key = md5($key ? $key : $auth_key);

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    //解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 * @author 魏巍
 * @description 获取当前时间的本周的开始结束时间
 */
function get_first_last_week_day()
{
    //当前日期
    $sdefaultDate = date("Y-m-d");
    //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
    $first=1;
    //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
    $w=date('w', strtotime($sdefaultDate));
    //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
    $week_start=date('Y-m-d', strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
    //本周结束日期
    $week_end=date('Y-m-d', strtotime("$week_start +6 days"));

    return [
        'first'=>$week_start,
        'last'=>$week_end
    ];
}

/**
 * 拆分内容
 * @param $content
 * @param string $separator
 * @return string
 */
function split_content($content, $separator="，,。")
{
    $separator = explode(',', $separator);
    $result =  array();
    $content = htmlspecialchars_decode($content);
    $str = tag_str(trim_all($content));
    $str=str_replace('，', ' ', str_replace('。', ' ', $str));
    $result = explode(' ', $str);
    $start_index =0;
    $_result='';
    for ($i=0; $i < count($result)-1; $i++) {
        if ($start_index%2==0) {
            $_result .= strip_tags($result[$i]).'，';
            $start_index = 1;
        } else {
            $_result .= strip_tags($result[$i]).'。';
            $start_index = 0;
        }
    }
    return $_result;
}

/**
 * 读取CSV文件中的某几行数据
 * @param $csv_file
 * @param $lines
 * @param int $offset
 * @return array|bool
 */
function csv_get_lines($csv_file, $lines, $offset = 0)
{
    if (!$fp = fopen($csv_file, 'r')) {
        return false;
    }
    $i = $j = 0;
    while (false !== ($line = fgets($fp))) {
        if ($i++ < $offset) {
            continue;
        }
        break;
    }
    $data = array();
    while (($j++ < $lines) && !feof($fp)) {
        $data[] = fgetcsv($fp);
    }
    fclose($fp);
    return $data;
}

/**
 * 拆分诗词
 * @param $content
 * @param $query
 * @param string $separator
 * @return string
 */
function get_next_split_content($content, $query, $separator="，,。")
{
    $separator = explode(',', $separator);
    $content = htmlspecialchars_decode($content);
    $str = tag_str(trim_all($content));
    $str=str_replace('，', ' ', str_replace('。', ' ', $str));
    $result = explode(' ', $str);
    $result = $temp = array_filter($result);
    $flag = 0;
    $_result ='';
    for ($i=0;$i<count($result);$i++) {
        if (strpos($result[$i], $query)!==false) {
            $flag = $i;
            break;
        }
    }
    $result = array_splice($result, $flag);
    //开始下标
    $start_index = count($temp) - count($result);

    for ($i=0; $i < count($result); $i++) {
        if ($start_index%2==0) {
            $_result .= strip_tags($result[$i]).$separator[0];
            $start_index = 1;
        } else {
            $_result .= strip_tags($result[$i]).$separator[1];
            $start_index = 0;
        }
    }
    return $_result;
}

/**
 * 去除标点符号
 * @param $text
 * @return string
 */
function filter_mark($text)
{
    if (trim($text)=='') {
        return '';
    }
    $text=preg_replace("/[[:punct:]\s]/", ' ', $text);
    $text=urlencode($text);
    $text=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/", ' ', $text);
    $text=urldecode($text);
    return trim($text);
}

/**
 * 去除空格
 * @param $str
 * @return mixed
 */
function trim_all($str)
{
    $q=array(" ","　","\t","\n","\r");
    $h=array("","","","","");
    return str_replace($q, $h, $str);
}

/**
 * 生成MAC
 * @return mixed|string
 */
function getMacAddr()
{
    $return_array = array();
    $temp_array = array();
    $mac_address = "";

    @exec("arp -a", $return_array);

    foreach ($return_array as $value) {
        if (strpos($value, $_SERVER["REMOTE_ADDR"]) !== false &&
            preg_match("/(:?[0-9a-f]{2}[:-]){5}[0-9a-f]{2}/i", $value, $temp_array)) {
            $mac_address = $temp_array[0];
            break;
        }
    }

    return ($mac_address);
}

/**
 * 生成订单号
 * @return string
 */
function build_order_no()
{
    return date('Ymd').substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 列出目录下的所有文件
 * @param $path
 * @param string $exts
 * @param array $list
 * @return array
 */
function dir_list($path, $exts = '', $list = array())
{
    $path = dir_path($path);
    $files = glob($path . '*');
    foreach ($files as $v) {
        if (!$exts || preg_match("/\.($exts)/i", $v)) {
            $list[] = $v;
            if (is_dir($v)) {
                $list = dir_list($v, $exts, $list);
            }
        }
    }
    return $list;
}

/**
 * 组织地址目录
 * @param $path
 * @return mixed|string
 */
function dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/') {
        $path = $path . '/';
    }
    return $path;
}

/**
 * 不重复随机数
 * @param int $begin
 * @param int $end
 * @param int $limit
 * @return string
 */
function NoRand($begin=0, $end=20, $limit=4)
{
    $rand_array=range($begin, $end);
    shuffle($rand_array);//调用现成的数组随机排列函数
    return implode('', array_slice($rand_array, 0, $limit));//截取前$limit个
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv=false)
{
    $type       =  $type ? 1 : 0;
    static $ip  =   null;
    if ($ip !== null) {
        return $ip[$type];
    }
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip     =   trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}



/**
 * 判断黑白天
 * @return bool
 */
function day_or_night()
{
    date_default_timezone_set('PRC');   //设定时区，PRC就是天朝
    $hour = date('H');
    if ($hour <= 18 && $hour > 6) {
        return true;
    } else {
        return false;
    }
}

function unicode_encode($word)
{
    $word0 = iconv('gbk', 'utf-8', $word);
    $word1 = iconv('utf-8', 'gbk', $word0);
    $word = ($word1 == $word) ? $word0 : $word;
    $word = json_encode($word);
    $word = preg_replace_callback('/\\\\u(\w{4})/', create_function('$hex', 'return \'&#\'.hexdec($hex[1]).\';\';'), substr($word, 1, strlen($word)-2));
    return $word;
}

function unicode_decode($uncode)
{
    $word = json_decode(preg_replace_callback('/&#(\d{5});/', create_function('$dec', 'return \'\\u\'.dechex($dec[1]);'), '"'.$uncode.'"'));
    return $word;
}
/**
 * 检测访问的ip是否为规定的允许的ip
 */
function check_ip($ip='')
{
    $ip = $ip?$ip:'0.0.0.0,*.*.*.*,127.0.0.1';
    $allow_ip=explode(',', $ip);
    $ip =get_client_ip();
    $check_ip_arr= explode('.', $ip);

    $bl=false;
    foreach ($allow_ip as $val) {
        if (($val == $ip) || ($val=='0.0.0.0') || ($val=='*.*.*.*')) {
            $bl = true;
        } elseif (strpos($val, '*')!==false) {
            $arr=[];//
            $arr=explode('.', $val);
            $j=0;
            for ($i=0;$i<4;$i++) {
                if ($arr[$i]!='*') {//不等于*  就要进来检测，如果为*符号替代符就不检查
                    if ($arr[$i]==$check_ip_arr[$i]) {
                        $bl=true;
                        break;//终止检查本个ip 继续检查下一个ip
                    }
                } else {
                    $j++;
                }
            }
            //			if($j){
//				$bl=true;
//				break;
//			}
        }
    }
    if (!$bl) {
        header('HTTP/1.1 403 Forbidden');
        echo "Access forbidden";
        die;
    }
}
/**
 * 转换彩虹字
 * @param string $str
 * @param int $size
 * @param bool $bold
 * @return string
 */
function color_txt($str, $size=20, $bold=false)
{
    $len = mb_strlen($str);
    $colorTxt   = '';
    if ($bold) {
        $bold="bolder";
        $bolder="font-weight:".$bold;
    }
    for ($i=0; $i<$len; $i++) {
        $colorTxt .=  '<span style="font-size:'.$size.'px;'.$bolder.'; color:'.rand_color().'">'.mb_substr($str, $i, 1, 'utf-8').'</span>';
    }
    return $colorTxt;
}

/**
 * 获取随机颜色
 * @return string
 */
function rand_color()
{
    return '#'.sprintf("%02X", mt_rand(0, 255)).sprintf("%02X", mt_rand(0, 255)).sprintf("%02X", mt_rand(0, 255));
}
/**
 * 替换表情
 * @param string $content
 * @return string
 */
function replace_phiz($content)
{
    preg_match_all('/\[.*?\]/is', $content, $arr);
    if ($arr[0]) {
        $phiz=cache('phiz', '', './data/');
        foreach ($arr[0] as $v) {
            foreach ($phiz as $key =>$value) {
                if ($v=='['.$value.']') {
                    $content=str_repeat($v, '<img src="'.__ROOT__.'/Public/Images/phiz/'.$key.'.gif"/>', $content);
                    break;
                }
            }
        }
        return $content;
    } else {
        return '';
    }
}
/**
 * 截取字符串
 * @param string $str
 * @param int $start
 * @param int $length
 * @param bool $suffix
 * @param string $charset
 * @return string|string
 */
function sub_str($str, $start=0, $length, $suffix=true, $charset="utf-8")
{
    if (strlen($str)==0) {
        return '';
    }
    $l=strlen($str);
    $slice = '';
    if (function_exists("mb_substr")) {
        return 	!$suffix?mb_substr($str, $start, $length, $charset):mb_substr($str, $start, $length, $charset)."…";
    } elseif (function_exists('iconv_substr')) {
        return  !$suffix?iconv_substr($str, $start, $length, $charset):iconv_substr($str, $start, $length, $charset)."…";
    }
    $re['utf-8']="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312']="/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']="/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']="/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));

    if ($l>$length) {
        return $suffix?$slice."…":$slice;
    } else {
        return $slice;
    }
}

/**
 * 去掉指定的字符串
 * @param $mub
 * @param $zhi
 * @param string $a
 * @return bool|mixed|string
 */
function pai_chu($mub, $zhi, $a='l')
{
    if (!$mub) {
        return "被替换的字符串不存在";
    }

    $mub = mb_convert_encoding($mub, 'GB2312', 'UTF-8');
    $zhi = mb_convert_encoding($zhi, 'GB2312', 'UTF-8');
    $last = '';
    if ($a=="") {
        $last = str_replace($mub, "", $zhi);
    } elseif ($a=="r") {
        $last = substr($mub, strrpos($mub, $zhi));
    } elseif ($a=="l") {
        //$last = preg_replace("/[\d\D\w\W\s\S]*[".$mub."]+/","",$zhi);
        $last = substr($mub, 0, strrpos($mub, $zhi));
    }
    //$last =  mb_convert_encoding($last,'UTF-8','GB2312');
    return $last;
}
/**
 * [get_image 获取文档中的图片]
 * @param  [type] $str [文档]
 * @return [type]      [description]
 */
function get_image($str)
{
    $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
    preg_match_all($pattern, $str, $match);
    return $match[1];
}
//高亮关键词
function heigLine($key, $content)
{
    return preg_replace('/'.$key.'/i', '<font color="red"><b>'.$key.'</b></font>', $content);
}

function reg($str)
{
    return  _strip_tags(array("p", "br"), $str);
}

/**
 * PHP去掉特定的html标签
 * @param $tagsArr
 * @param $str
 * @return mixed
 */
function _strip_tags($tagsArr, $str)
{
    $p=[];
    foreach ($tagsArr as $tag) {
        $p[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
    }
    $return_str = preg_replace($p, "", $str);
    return $return_str;
}

/**
 * 截取字符串
 * @param $str
 * @param int $start
 * @param int $length
 * @return string
 */
function tag_str($str, $start=0, $length=250)
{
    $str=strip_tags(htmlspecialchars_decode($str));
    $temp=mb_substr($str, $start, $length, 'utf-8');
    //return (strlen($str)>$length*1.5)?$temp.'...':$temp;
    return $temp;
}

/**
 * 发送短信
 * @param string  $to                       发送人
 * @param string $templateId                短信模板id
 * @param int $t                            用户检测
 * @return array
 */
function send_sms($to, $templateId= "72035", $t=0)
{
    if ($t) {	//检测用户
        $member = db('member')->where(['username'=>$to])->count();
        if ($member) {
            return ['status'=>0,'msg'=>'抱歉用户名已被使用'];
        }
    }
    $options['accountsid']= config('Ucpaas.accountSid');
    $options['token']=config('Ucpaas.authToken');
    $appId = config('Ucpaas.appId');
    $d = NoRand(0, 9, 4);
    $ucpass = new \service\Ucpaas($options);
    $param ="{$d}";	//参数
    $arr=$ucpass->templateSMS($appId, $to, $templateId, $param);
    if (cookie('?'.$d.'_session_code')) {
        cookie($d.'_session_code', null, time()-60*2);
    }
    cookie($d.'_session_code', $d, 60*3);
    return $arr;
}

/**
 * * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null)
{
    $config = config('THINK_EMAIL');
    vendor('PHPMailer.class#phpmailer');
    vendor('PHPMailer.class#SMTP');
    $mail             = new \PHPMailer(); //PHPMailer对象
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能,1 = errors and messages,2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    //$mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
    $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
    $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
    $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
    $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
    $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
    $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->isHTML(true);
    $mail->AddAddress($to, $name);
    //p($mail);die;
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 * 不重复随机数
 * @param int $begin
 * @param int $end
 * @param int $limit
 * @return string
 */
function no_repeat_random($begin=0, $end=20, $limit=4)
{
    $rand_array=range($begin, $end);
    shuffle($rand_array);//调用现成的数组随机排列函数
    return implode('', array_slice($rand_array, 0, $limit));//截取前$limit个
}

/**
 * 数字补足
 * @param $num
 * @param int $length
 * @param int $type
 * @param string $fill
 * @return string
 */
function zero_ize($num, $length=10, $type=1, $fill='0')
{
    $type=$type?STR_PAD_LEFT:STR_PAD_RIGHT;
    return str_pad($num, $length, $fill, $type);
}

/**
 * 根据value得到数组key
 * @param $arr
 * @param $value
 * @return bool|int|string
 */
function get_key($arr, $value)
{
    if (!is_array($arr)) {
        return false;
    } else {
        foreach ($arr as $k => $v) {
            $return = get_key($v, $value);
            if ($v == $value) {
                return $k;
            }
            if (!is_null($return)) {
                return $return;
            }
        }
    }
    return;
}

/**
 * Formats a JSON string for pretty printing
 *
 * @param string $json The JSON to make pretty
 * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
 * @return string The prettified output
 */
function _format_json($json, $html = false)
{
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;
    if ($html) {
        $tab = "   ";
        $newline = "<br/>";
    } else {
        $tab = "\t";
        $newline = "\n";
    }
    for ($i = 0; $i < strlen($json); $i++) {
        $char = $json[$i];
        if ($ignorenext) {
            $result .= $char;
            $ignorenext = false;
        } else {
            switch ($char) {
                case '{':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '}':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case ',':
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '"':
                    $inquote = !$inquote;
                    $result .= $char;
                    break;
                case '\\':
                    if ($inquote) {
                        $ignorenext = true;
                    }
                    $result .= $char;
                    break;
                default:
                    $result .= $char;
            }
        }
    }
    return $result;
}
