<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/11
 * Time: 10:08
 */

const ENV = 'development';

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../plugin/oss-sdk-php/Uploader.php';


use Command\Model\UserModel;

// 更新用户头像
$users = UserModel::get();
foreach ($users as $v)
{
    if (empty($v->avatar)) {
        continue ;
    }
    if (mb_strpos($v->avatar , 'hichats.oss-ap-southeast-1.aliyuncs.com') !== false) {
        continue ;
    }
//    var_dump($v->avatar);
    $filename = preg_replace('/https?:\/\//' , '' , $v->avatar);
    $filename = mb_strstr($filename , '/');
    $filename = ltrim($filename , '/');
//    var_dump($filename);
    $content = file_get_contents($v->avatar);
//    var_dump($content);
    $uploader = new Uploader();
    $res = $uploader->upload($filename , $content);
//    var_dump($res);
    if ($res['code'] != 0) {
        var_dump('user_id: ' . $v->id . '; avatar: ' . $v->avatar . '；上传失败');
        break;
    }
    $avatar = $res['data'];
//    break;
    UserModel::updateById($v->id , [
        'avatar' => $avatar ,
    ]);

    var_dump("更新后的头像：" . $avatar);
}


