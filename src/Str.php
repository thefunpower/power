<?php

/** 
 * Str.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/28 
 */

namespace Power;

class Str
{
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
}
