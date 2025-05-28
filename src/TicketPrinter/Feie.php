<?php

namespace Power\TicketPrinter;

/**
 * 飞鹅打印
 */

class Feie extends Base
{
    //手机号
    public $phone;
    //标题
    public $title;
    //备注
    public $desc;
    //条码URL，带http的
    public $qr;
    //设备号
    public $sn; //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
    public $key;
    public $user = ''; //*必填*：飞鹅云后台注册账号
    public $ukey = ''; //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】

    public $ip   = 'api.feieyun.cn'; //接口IP或域名
    public $port = 80; //接口IP端口
    public $path = '/Api/Open/';

    /**
     * 打印
     * @param $option  arr打印菜品 list[k=>v]
     */
    public function do_print($set = [], $option = [])
    {
        $this->set($set);
        $arr = $option['arr'];
        if (!$arr) {
            return false;
        }
        $info = $this->get_string($arr, 14, 6, 3, 6);
        if ($option['list']) {
            $info .= '--------------------------------<BR>';
            foreach ($option['list'] as $k => $v) {
                $info .= $k . '：' . $v . '<BR>';
            }
        }
        return  $this->do_print_58mm($set, $this->sn, $info, 1);
    }
    /**
     *    
     *    #########################################################################################################
     *
     *    进行订单的多列排版demo，实现商品超出字数的自动换下一行对齐处理，同时保持各列进行对齐
     *
     *    排版原理是统计字符串字节数，补空格换行处理
     *
     *    58mm的机器,一行打印16个汉字,32个字母;80mm的机器,一行打印24个汉字,48个字母
     *
     *    #########################################################################################################
     */
    public function get_string($arr, $A, $B, $C, $D, $string1 = '')
    {
        $nums = 0;
        $orderInfo = '<CB>' . $this->title . '</CB><BR>';
        $orderInfo .= '名称           单价  数量 金额<BR>';
        $orderInfo .= '--------------------------------<BR>';
        foreach ($arr as $k5 => $v5) {
            $name   = $v5['title'];
            $price  = $v5['price'];
            $num    = $v5['num'];
            $prices = bcmul($v5['price'], $v5['num'], 2);
            $kw3 = '';
            $kw1 = '';
            $kw2 = '';
            $kw4 = '';
            $str = $name;
            $blankNum = $A; //名称控制为14个字节
            $lan = mb_strlen($str, 'utf-8');
            $m = 0;
            $j = 1;
            $blankNum++;
            $result = array();
            if (strlen($price) < $B) {
                $k1 = $B - strlen($price);
                for ($q = 0; $q < $k1; $q++) {
                    $kw1 .= ' ';
                }
                $price = $price . $kw1;
            }
            if (strlen($num) < $C) {
                $k2 = $C - strlen($num);
                for ($q = 0; $q < $k2; $q++) {
                    $kw2 .= ' ';
                }
                $num = $num . $kw2;
            }
            if (strlen($prices) < $D) {
                $k3 = $D - strlen($prices);
                for ($q = 0; $q < $k3; $q++) {
                    $kw4 .= ' ';
                }
                $prices = $prices . $kw4;
            }
            for ($i = 0; $i < $lan; $i++) {
                $new = mb_substr($str, $m, $j, 'utf-8');
                $j++;
                if (mb_strwidth($new, 'utf-8') < $blankNum) {
                    if ($m + $j > $lan) {
                        $m = $m + $j;
                        $tail = $new;
                        $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
                        $k = $A - strlen($lenght);
                        for ($q = 0; $q < $k; $q++) {
                            $kw3 .= ' ';
                        }
                        if ($m == $j) {
                            $tail .= $kw3 . ' ' . $price . ' ' . $num . ' ' . $prices;
                        } else {
                            $tail .= $kw3 . '<BR>';
                        }
                        break;
                    } else {
                        $next_new = mb_substr($str, $m, $j, 'utf-8');
                        if (mb_strwidth($next_new, 'utf-8') < $blankNum) continue;
                        else {
                            $m = $i + 1;
                            $result[] = $new;
                            $j = 1;
                        }
                    }
                }
            }
            $head = '';
            foreach ($result as $key => $value) {
                if ($key < 1) {
                    $v_lenght = iconv("UTF-8", "GBK//IGNORE", $value);
                    $v_lenght = strlen($v_lenght);
                    if ($v_lenght == 13) $value = $value . " ";
                    $head .= $value . ' ' . $price . ' ' . $num . ' ' . $prices;
                } else {
                    $head .= $value . '<BR>';
                }
            }
            $orderInfo .= $head . $tail;
        }
        return $orderInfo;
    }


    /**
     * [批量添加打印机接口 Open_printerAddlist]
     * @param  [string] $printerContent [打印机的sn#key]
     * @return [string]                 [接口返回值]
     */
    public function add($set = [], $printerContent)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printerAddlist',
            'printerContent' => $printerContent
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }


    /**
     * [打印订单接口 Open_do_print_58mm]
     * @param  [string] $sn      [打印机编号sn]
     * @param  [string] $content [打印内容]
     * @param  [string] $times   [打印联数]
     * @return [string]          [接口返回值]
     */
    protected function do_print_58mm($set, $sn, $content, $times)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printMsg',
            'sn' => $this->sn,
            'content' => $content,
            'times' => $times //打印次数
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [标签机打印订单接口 Open_printLabelMsg]
     * @param  [string] $sn      [打印机编号sn]
     * @param  [string] $content [打印内容]
     * @param  [string] $times   [打印联数]
     * @return [string]          [接口返回值]
     */
    protected function do_print_label($set = [], $sn, $content, $times)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printLabelMsg',
            'sn' => $this->sn,
            'content' => $content,
            'times' => $times //打印次数
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [批量删除打印机 Open_printerDelList]
     * @param  [string] $snlist [打印机编号，多台打印机请用减号“-”连接起来]
     * @return [string]         [接口返回值]
     */
    public function del($set = [], $snlist)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printerDelList',
            'snlist' => $snlist
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [修改打印机信息接口 Open_printerEdit]
     * @param  [string] $sn       [打印机编号]
     * @param  [string] $name     [打印机备注名称]
     * @param  [string] $phonenum [打印机流量卡号码,可以不传参,但是不能为空字符串]
     * @return [string]           [接口返回值]
     */
    public function edit($set = [], $sn, $name, $phonenum = '')
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printerEdit',
            'sn' => $this->sn,
            'name' => $name,
        );
        if ($phonenum) {
            $msgInfo['phonenum'] = $phonenum;
        }
        return Curl::post($this->ip . $this->port, $msgInfo);
    }


    /**
     * [清空待打印订单接口 Open_delPrinterSqs]
     * @param  [string] $sn [打印机编号]
     * @return [string]     [接口返回值]
     */
    public function clear($set = [], $sn)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_delPrinterSqs',
            'sn' => $this->sn
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [查询订单是否打印成功接口 Open_queryOrderState]
     * @param  [string] $orderid [调用打印机接口成功后,服务器返回的JSON中的编号 例如：123456789_20190919163739_95385649]
     * @return [string]          [接口返回值]
     */
    public function query($set = [], $orderid)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_queryOrderState',
            'orderid' => $orderid
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [查询指定打印机某天的订单统计数接口 Open_queryOrderInfoByDate]
     * @param  [string] $sn   [打印机的编号]
     * @param  [string] $date [查询日期，格式YY-MM-DD，如：2019-09-20]
     * @return [string]       [接口返回值]
     */
    public function query_date($set = [], $sn, $date)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_queryOrderInfoByDate',
            'sn' => $this->sn,
            'date' => $date
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [获取某台打印机状态接口 Open_queryPrinterStatus]
     * @param  [string] $sn [打印机编号]
     * @return [string]     [接口返回值]
     */
    public  function get_status($set = [], $sn)
    {
        $this->set($set);
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_queryPrinterStatus',
            'sn' => $this->sn
        );
        return Curl::post($this->ip . $this->port, $msgInfo);
    }

    /**
     * [signature 生成签名]
     * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
     * @return [string]       [接口返回值]
     */
    public function signature($time)
    {
        return sha1($this->user . $this->ukey . $time); //公共参数，请求公钥
    }
}
