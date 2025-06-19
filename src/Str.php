<?php

/** 
 * Str.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/28 
 */

namespace Power;

class Str
{
    static $size = ['B', 'KB', 'MB', 'GB', 'TB'];

    /**
     * float不进位，如3.145 返回3.14
     * 进位的有默认round(3.145) 或sprintf("%.2f",3.145);
     */
    public static function floatNoup($float_number, $dot = 2)
    {
        $p = pow(10, $dot);
        return floor($float_number * $p) / $p;
    }
    /**
     * 四舍五入
     * @param $mid_val 逢几进位
     */
    public static function floatUp($float_number, $dot = 2, $mid_val = 5)
    {
        $p = pow(10, $dot);
        if (strpos($float_number, '.') !== false) {
            $a = substr($float_number, strpos($float_number, '.') + 1);
            $a = substr($a, $dot, 1) ?: 0;
            if ($a >= $mid_val) {
                return bcdiv(bcmul($float_number, $p) + 1, $p, $dot);
            } else {
                return bcdiv(bcmul($float_number, $p), $p, $dot);
            }
        }
        $p = pow(10, $dot);
        return floor($float_number * $p) / $p;
    }
    /**
     * GBK字符截取
     * 一个中文算2个字符
     */
    public static function gbkSubstr($text, $start, $len, $gbk = 'GBK')
    {
        $str = mb_strcut(mb_convert_encoding($text, $gbk, "UTF-8"), $start, $len, $gbk);
        $str = mb_convert_encoding($str, "UTF-8", $gbk);
        return $str;
    }
    /**
     * GBK长宽
     * 2个字符
     */
    public static function getGbkLenght($value, $gbk = 'GBK')
    {
        return strlen(iconv("UTF-8", $gbk . "//IGNORE", $value));
    }
    /**
     * 文字居中
     */
    public static function getTextCenter(string $str, int $len)
    {
        $cur_len = self::getGbkLenght($str);
        $less    = $len - $cur_len;
        $s = (int)($less / 2);
        $e = $less - $s;
        $append = '';
        $end    = '';
        for ($i = 0; $i < $s; $i++) {
            $append .= " ";
        }
        for ($i = 0; $i < $e; $i++) {
            $end .= " ";
        }
        return $append . $str . $end;
    }

    /**
     * 文字排版
     * 左 中 右
     * 左    右
     */
    public static function getTextLcr(array $arr, int $length, $return_arr = false)
    {
        $count  = count($arr);
        $middle = (int)(bcdiv($length, $count));
        $j = 1;
        foreach ($arr as &$v) {
            $cur_len = self::getGbkLenght($v);
            $less    = $middle - $cur_len;
            $append  = "";
            if ($less > 0) {
                for ($i = 0; $i < $less; $i++) {
                    $append .= " ";
                }
                if ($j == $count) {
                    $v = $append . $v;
                } else {
                    $v = $v . $append;
                }
            } else {
                $v = self::gbkSubstr($v, 0, $middle);
            }
            $j++;
        }
        if ($return_arr) {
            return $return_arr;
        } else {
            return implode("", $arr);
        }
    }
    /**
     * 字符或数组 转UTF-8
     */
    public static function toUtf8($str)
    {
        if (!$str || (!is_array($str) && !is_string($str))) {
            return $str;
        }
        if (is_array($str)) {
            $list = [];
            foreach ($str as $k => $v) {
                $list[$k] = self::toUtf8($v);
            }
            return $list;
        } else {
            $encoding = mb_detect_encoding($str, "UTF-8, GBK, ISO-8859-1");
            if ($encoding && $encoding != 'UTF-8') {
                $str = iconv($encoding, "UTF-8//IGNORE", $str);
                $str = trim($str);
            }
            return $str;
        }
    }
    /**
     * 优化数量显示
     * 1.10显示为1.1
     * 1.05显示为1.05
     * 1.00显示为1
     */
    public static function showNumber($num)
    {
        return rtrim(rtrim($num, '0'), '.');
    }
    /**
     * 取字符中的数字
     */
    public static function getNumber($input)
    {
        $pattern = '/(\d+(\.\d+)?)/';
        preg_match_all($pattern, $input, $matches);
        return $matches[1];
    }
    /**
     * 500m 1km
     * 1公里
     * @param  mixed $dis [description]
     * @return string     [description]
     */
    public static function dis($dis)
    {
        $l['公里'] = 1000;
        $l['里']   = 1000;
        $l['m']    = 1;
        foreach ($l as $k => $num) {
            if (strpos($dis, $k) !== false) {
                $dis = str_replace($k, "", $dis);
                $dis = $dis * $num;
            }
        }
        return $dis;
    }
    /**
     * 折扣 100 1 0.1折
     * @param string $size 
     * @return string　 
     */
    public static function discount($price, $nowprice)
    {
        return round(10 / ($price / $nowprice), 1);
    }



    /**
     * 计算时间剩余　 
     * 
     * $timestamp - $small_timestamp 剩余的时间，相差几天几小时几分钟
     * @param   $timestamp 当前时间戳
     * @param   $small_timestamp 自定义时间戳，小于当前时间戳
     * @return array ２天３小时２８分钟１０秒 
     */
    public static function lessTime($timestamp, $small_timestamp = null)
    {
        if (!$small_timestamp) $time = $timestamp;
        else $time = $timestamp - $small_timestamp;
        if ($time <= 0) return -1;
        $days = intval($time / 86400);
        $remain = $time % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        return ["d" => $days, "h" => $hours, "m" => $mins, "s" => $secs];
    }

    /**
     * 字节单位自动转换 显示1GB MB等
     * @param string $size 
     * @return string　 
     */
    public static function size($size)
    {
        $units = static::$size;
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    /**
     * 字节单位自动转换到指定的单位
     * @param string $size 　 
     * @param string $to 　
     * @return string
     */
    public static function sizeTo($size, $to = 'GB')
    {
        $size = strtoupper($size);
        $to = strtoupper($to);
        $arr = explode(' ', $size);
        $key = $arr[1];
        $size = $arr[0];
        $i = array_search($key, static::$size);
        $e = array_search($to, static::$size);
        $x = 1;
        if ($i < $e) {
            for ($i; $i < $e; $i++) {
                $x *= 1024;
            }
            return round($size / $x, 2);
        }
        for ($e; $e < $i; $e++) {
            $x *= 1024;
        }
        return $size * $x;
    }

    /**
     * 随机数字
     * @param string $j 位数 　 
     * @return int
     */
    public static function rand_number($j = 4)
    {
        $str = null;
        for ($i = 0; $i < $j; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }
    /**
     * 随机字符
     * @param string $j 位数 　 
     * @return string
     */
    public static function rand($j = 8)
    {
        $string = "";
        for ($i = 0; $i < $j; $i++) {
            srand((float)microtime() * 1234567);
            $x = mt_rand(0, 2);
            switch ($x) {
                case 0:
                    $string .= chr(mt_rand(97, 122));
                    break;
                case 1:
                    $string .= chr(mt_rand(65, 90));
                    break;
                case 2:
                    $string .= chr(mt_rand(48, 57));
                    break;
            }
        }
        return $string; //to uppercase
    }

    /**
     * 截取后，用 ...代替被截取的部分
     * @param  string $string 字符串
     * @param  int $length 截取长度
     * @return string
     */
    public static function cut($string, $length, $append = '')
    {
        $new_str = '';
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $info);
        for ($i = 0; $i < count($info[0]); $i++) {
            $new_str .= $info[0][$i];
            $j = ord($info[0][$i]) > 127 ? $j + 2 : $j + 1;
            if ($j > $length - 3) {
                return $new_str . $append;
            }
        }
        return join('', $info[0]);
    }
}
