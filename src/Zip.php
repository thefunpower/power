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
     * @param array|string $inputFiles 输入文件（可以是单个文件路径或文件路径数组）
     * @param string $output 输出zip文件路径
     * @return string 返回创建的zip文件路径
     * @throws \Exception 如果压缩失败
     */
    public static function run($inputFiles = [], $output = '')
    {
        if (empty($inputFiles) || empty($output)) {
            throw new \Exception('Input files and output path cannot be empty');
        }

        // Ensure output directory exists
        $outputDir = dirname($output);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($output, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cannot create zip file: ' . $output);
        }

        // Convert single file path to array
        $inputFiles = (array)$inputFiles;

        foreach ($inputFiles as $file) {
            if (!file_exists($file)) {
                $zip->close();
                throw new \Exception('File does not exist: ' . $file);
            }

            if (is_dir($file)) {
                // Add directory recursively
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($file),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($files as $item) {
                    if ($item->isFile()) {
                        $relativePath = substr($item->getPathname(), strlen(dirname($file)) + 1);
                        $zip->addFile($item->getPathname(), $relativePath);
                    }
                }
            } else {
                // Add single file
                $relativePath = basename($file);
                $zip->addFile($file, $relativePath);
            }
        }

        $zip->close();
        return $output;
    }

    /**
     * 解压文件
     * @param string $input zip文件路径
     * @param string $outputDir 输出目录
     * @throws \Exception 如果解压失败
     */
    public static function unzip($input, $outputDir = '')
    {
        if (empty($input)) {
            throw new \Exception('Input zip file path cannot be empty');
        }

        if (!file_exists($input)) {
            throw new \Exception('Zip file does not exist: ' . $input);
        }

        // Ensure output directory exists
        if (!empty($outputDir) && !is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($input) !== true) {
            throw new \Exception('Cannot open zip file: ' . $input);
        }

        $zip->extractTo($outputDir ?: dirname($input));
        $zip->close();
    }
}