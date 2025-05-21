<?php

/** 
 * File.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/21 
 */

namespace Power;

class File
{
    /**
     * 获取目录
     * @param string $name 文件路径
     * @return string
     */
    public static function getDir($name)
    {
        return substr($name, 0, strrpos($name, '/'));
    }
    /**
     * 获取文件扩展名
     * @param string $name 文件路径
     * @return string
     */
    public static function getExt($name)
    {
        if (strpos($name, '?') !== false) {
            $name = substr($name, 0, strpos($name, '?'));
        }
        $name = substr($name, strrpos($name, '.'));
        return strtolower(substr($name, 1));
    }
    /**
     * 获取文件名
     * @param string $name 文件路径
     * @return string
     */
    public static function getName($name)
    {
        $name = substr($name, strrpos($name, '/'));
        $name = substr($name, 0, strrpos($name, '.'));
        $name = substr($name, 1);
        return $name;
    }
}
