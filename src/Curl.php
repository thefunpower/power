<?php

namespace Power;
/**
 * "guzzlehttp/guzzle": "^7.9"
 */
class Curl
{
    /**
     * 配置
     * @param $option
     */
    public static $option = [
        'timeout' => 10,
        'verify' => false,
    ];
    /**
     * 上传文件
     * @param $upload_url
     * @param $local_file
     * @param array $headers
     * @param int $timeout
     * @return bool
     */ 
    public static function put($upload_url, $local_file, $headers = [], $timeout = 60)
    {
        if (!file_exists($local_file)) {
            return false;
        }
        $body = file_get_contents($local_file);
        $client = self::init();
        $request = new \GuzzleHttp\Psr7\Request('PUT', $upload_url, $headers = [], $body);
        $response = $client->send($request, ['timeout' => $timeout]);
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * POST 请求
     * @param $url
     * @param $data ['body'=>] || ['json]=>] || ['form_params'=>[]]
     */
    public static function post($url, $data = [])
    {
        $client = self::init();
        $res = $client->request('POST', $url, $data);
        return (string) $res->getBody();
    }
    /**
     * GET 请求
     * @param $url
     * @param $data ['body'=>] || ['json]=>] || ['form_params'=>[]]
     */
    public static function get($url)
    {
        $client = self::init();
        $res = $client->request('GET', $url);
        return (string) $res->getBody();
    }
    /**
     * 初始化
     */
    public static function init()
    {
        $client = new \GuzzleHttp\Client(self::$option);
        return $client;
    }
}
