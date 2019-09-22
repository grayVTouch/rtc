<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/17
 * Time: 16:57
 */

namespace Core\Lib;

class Log
{
    // 日志目录
    public $dir = '';

    // 单个日志文件大小限制，超过限制自动拆分日志
    // 单位：Byte
    public $sizeLimit = 2 * 1024 * 1024;

    // 日志前缀
    public $prefix = 'system';

    public function __construct()
    {

    }

    // 写入
    public function write($data , string $mode = 'a')
    {
        $datetime = date('Yd');
        $filename = sprintf('%s-%s.log' , $this->prefix , $datetime);
        $file = sprintf('%s%s' , $this->dir , $filename);
        if (!file_exists($file)) {
            // 创建文件
            $res = fopen($file , 'x');
        } else {
            $res = fopen($file , $mode);
        }
        fwrite($res , $data);
        fclose($res);
        return true;
    }

}