<?php

/** 
 * Dir.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/21 
 */

namespace Power;

class Dir
{
    /**
     * 创建目录
     * @param string|array $arr 目录路径
     * @return void
     */
    public static function create($arr)
    {
        if (is_string($arr)) {
            $v = $arr;
            if (!is_dir($v) && !mkdir($v, 0777, true)) {
                throw new \Exception("无法创建目录: $v");
            }
        } elseif (is_array($arr)) {
            foreach ($arr as $v) {
                if (!is_dir($v) && !mkdir($v, 0777, true)) {
                    throw new \Exception("无法创建目录: $v");
                }
            }
        }
    }
    /**
     * 获取目录下的所有文件
     * @param string $path 目录路径
     * @return array
     */
    public static function getDeep($path)
    {
        $arr = array();
        $arr[] = $path;
        if (is_file($path)) {
        } else {
            if (is_dir($path)) {
                $data = scandir($path);
                if (!empty($data)) {
                    foreach ($data as $value) {
                        if ($value != '.' && $value != '..') {
                            $sub_path = $path . "/" . $value;
                            $temp = self::getDeep($sub_path);
                            $arr = array_merge($temp, $arr);
                        }
                    }
                }
            }
        }
        return $arr;
    }
}
