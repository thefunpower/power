<?php
/** 
 * Exec.php
 * User: KenKen <68103403@qq.com>
 * Date: 2025/05/21 
 */
namespace Power;

class Exec
{
    /**
     * 执行命令
     * @param $cmd
     * @param array $output
     * @param bool $show_err
     * @return bool
     */
    public static  function run($cmd, &$output = '', $show_err = false)
    {
        @putenv("LANG=zh_CN.UTF-8");
        exec($cmd, $output, $return_var);
        if ($show_err && $return_var !== 0) {
            return false;
        }
        return true;
    }
}
