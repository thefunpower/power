<?php

/** 
 * Unzip.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/21 
 */

namespace Power;

class Unzip
{ 
	/**
	 * 解压文件
	 * @param string $input 输入文件, 支持7z, zip, rar, tar, bz2, gz, tar
	 * @param string $output_base 输出目录
	 * @return string
	 */
	public static function run($input, $output_base = '')
	{
		$output_dir = $output_base . md5($input);
		$ext = File::getExt($input);
		$cmd = "";
		$tar = "tar -xvf " . $input . " -C " . $output_dir;
		if (is_dir($output_base . '/uploads/')) {
			if (is_dir($output_dir)) {
				Exec::run("rm -rf " . $output_dir);
			}
		}
		Dir::create($output_dir);
		switch ($ext) {
			case '7z':
				$cmd = "7za x " . $input . " -o" . $output_dir;
				break;
			case 'zip':
				$cmd = "unzip " . $input . " -d " . $output_dir;
				break;
			case 'rar':
				$cmd = "unar  " . $input . " -o " . $output_dir;
				break;
			case 'bz2':
				$cmd = $tar;
				break;
			case 'gz':
				$cmd = $tar;
				break;
			case 'tar':
				$cmd = $tar;
				break;
		}
		if ($cmd) {
			Exec::run($cmd);
			return $output_dir;
		}
	}
}
