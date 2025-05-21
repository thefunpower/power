<?php

/** 
 * Zip.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/21 
 */

namespace Power;

class Zip
{

    /**
     * 压缩文件
     * @param string $inputFiles 输入文件
     * @param string $output 输出目录
     * @return string
     */
    public static function run($inputFiles = [], $output = '')
    {
        $zippy = \Alchemy\Zippy\Zippy::load();
        $archive = $zippy->create($output, $inputFiles, true);
        return $archive;
    }

    /**
     * 压缩文件
     */
    public static function unzip($input, $outputDir = '')
    {
        $zippy = \Alchemy\Zippy\Zippy::load();
        $archive = $zippy->open($input);
        $archive->extract($outputDir);
    }
}
