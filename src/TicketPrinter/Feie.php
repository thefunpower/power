<?php

namespace Power\TicketPrinter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Feie extends Base
{
    public $backUrl = '';
    public $sn;
    public $user = '';
    public $ukey = '';
    public $ip = 'api.feieyun.cn';
    public $port = 80;
    public $path = '/Api/Open/';

    // 配置参数
    public function set($arr)
    {
        foreach ($arr as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * 58mm打印
     */
    public function print($content, $num = 1)
    {
        $content = $this->parse($content);
        return $this->printMsg($this->sn, $content, $num);
    }
    /**
     * 标签机打印
     */
    public function print_label($content, $num = 1)
    {
        $content = $this->parse($content);
        return $this->printMsgLabel($this->sn, $content, $num);
    }

    /**
     * 统一请求方法
     */
    protected function request($apiName, $params = [], $method = 'GET')
    {
        $time = time();
        $baseParams = [
            'user' => $this->user,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => $apiName
        ];

        $client = new Client([
            'base_uri' => 'http://' . $this->ip,
            'timeout' => 10,
            'verify' => false,
            'http_errors' => false
        ]);

        try {
            if ($method === 'GET') {
                $response = $client->get($this->path, [
                    'query' => array_merge($baseParams, $params)
                ]);
            } else {
                $response = $client->post($this->path, [
                    'form_params' => array_merge($baseParams, $params)
                ]);
            }

            $content = $response->getBody()->getContents();
            return json_decode($content, true) ?? $content;
        } catch (RequestException $e) {
            return [
                'ret' => -1,
                'msg' => $e->getMessage()
            ];
        }
    }

    // ========== 打印机接口方法 ==========

    public function info($sn)
    {
        return $this->request('Open_printerInfo', ['sn' => $sn]);
    }

    public function add($printerContent)
    {
        return $this->request('Open_printerAddlist', ['printerContent' => $printerContent], 'POST');
    }

    public function printMsg($sn, $content, $times)
    {
        return $this->request('Open_printMsg', [
            'sn' => $sn,
            'content' => $content,
            'times' => $times,
            'backurl' => $this->backUrl
        ], 'POST');
    }

    public function printMsgLabel($sn, $content, $times)
    {
        return $this->request('Open_printLabelMsg', [
            'sn' => $sn,
            'content' => $content,
            'times' => $times
        ], 'POST');
    }

    public function del($snlist)
    {
        return $this->request('Open_printerDelList', ['snlist' => $snlist], 'POST');
    }

    public function edit($sn, $name, $phonenum)
    {
        return $this->request('Open_printerEdit', [
            'sn' => $sn,
            'name' => $name,
            'phonenum' => $phonenum
        ], 'POST');
    }

    public function clear($sn)
    {
        return $this->request('Open_delPrinterSqs', ['sn' => $sn], 'POST');
    }

    public function query($orderid)
    {
        return $this->request('Open_queryOrderState', ['orderid' => $orderid]);
    }

    public function query_date($sn, $date)
    {
        return $this->request('Open_queryOrderInfoByDate', [
            'sn' => $sn,
            'date' => $date
        ]);
    }

    public function get_status($sn)
    {
        return $this->request('Open_queryPrinterStatus', ['sn' => $sn]);
    }

    /**
     * 生成签名
     */
    public function signature($time)
    {
        return sha1($this->user . $this->ukey . $time);
    }
}
