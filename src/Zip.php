<?php

namespace Power;

class Zip
{
    /**
     * 压缩文件
     * @param array|string $inputFiles 输入文件（单个文件路径或文件路径数组）
     * @param string $output 输出zip文件路径
     * @return string 返回创建的zip文件路径
     * @throws \Exception 如果压缩失败
     */
    public static function run($inputFiles, string $output): string
    {
        if (empty($inputFiles) || empty($output)) {
            throw new \Exception('输入文件和输出路径不能为空');
        }

        // 确保输出目录存在
        $outputDir = dirname($output);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) {
            throw new \Exception('无法创建输出目录: ' . $outputDir);
        }

        $zip = new \ZipArchive();
        if ($zip->open($output, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('无法创建zip文件: ' . $output);
        }

        // 转换为数组并过滤不存在的文件
        $inputFiles = array_filter((array)$inputFiles, function ($file) {
            if (!file_exists($file)) {
                throw new \Exception('文件不存在，已跳过: ' . $file);
                return false;
            }
            return true;
        });

        foreach ($inputFiles as $file) {
            $file = realpath($file); // 使用绝对路径避免路径问题

            if (is_dir($file)) {
                // 如果是目录，只打包目录本身及其内容
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($file, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $item) {
                    if ($item->isFile()) {
                        $itemPath = $item->getPathname();
                        $relativePath = substr($itemPath, strlen(dirname($file)) + 1);
                        $zip->addFile($itemPath, $relativePath);
                    }
                }
            } else {
                // 如果是文件，只打包该文件
                $relativePath = basename($file);
                $zip->addFile($file, $relativePath);
            }
        }

        if ($zip->numFiles === 0) {
            $zip->close();
            throw new \Exception('未添加任何文件到zip');
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
    public static function unzip(string $input, string $outputDir): void
    {
        if (empty($input)) {
            throw new \Exception('输入zip文件路径不能为空');
        }

        if (!file_exists($input)) {
            throw new \Exception('zip文件不存在: ' . $input);
        }

        if (empty($outputDir)) {
            throw new \Exception('输出目录不能为空');
        }

        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) {
            throw new \Exception('无法创建输出目录: ' . $outputDir);
        }

        $zip = new \ZipArchive();
        if ($zip->open($input) !== true) {
            throw new \Exception('无法打开zip文件: ' . $input);
        }

        $zip->extractTo($outputDir);
        $zip->close();
    }
}
