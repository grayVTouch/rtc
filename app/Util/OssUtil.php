<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/1/10
 * Time: 14:23
 */

namespace App\Util;


use Core\Lib\Http;

class OssUtil extends Util
{

    private static $apiForDelAll = 'http://chat.hichatvip.com/delete';

    // 删除文件
    public static function delAll(array $oss_file = [])
    {
        $res = Http::post(self::$apiForDelAll , [
            'data' => [
                'oss_file' => json_encode($oss_file)
            ] ,
        ]);
        if (empty($res)) {
            return self::error('请检查本地网络是否畅通' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != 0) {
            return self::error('删除亚马逊云文件失败：' . $res['data'] , 500);
        }
        return self::success();
    }
}