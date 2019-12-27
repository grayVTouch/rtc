<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/25
 * Time: 14:38
 */

require_once __DIR__ . '/plugin/aws/vendor/autoload.php';

var_dump((int) "0");

exit;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

use Aws\Exception\AwsException;

$id = 'AKIAIDGM5H5OVLJAQSGQ';
$secret = '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';

$credentials = new Aws\Credentials\Credentials($id , $secret);

//Create a S3Client
$s3 = new Aws\S3\S3Client([
    'region' => 'ap-northeast-1' ,
    'version' => 'latest',
    'credentials' => $credentials ,
    'http' => [
//        'verify' => __DIR__ . '/ca-bundle.crt' ,
        'verify' => __DIR__ . '/curl_cacert.pem' ,
    ],
]);
$bucket = 'nimo';
$filepath = __DIR__ . '/avatar.png';
$avatar = file_get_contents($filepath);
$filename = 'test.png';
$bucket = 'nimo';
try {
    $result = $s3->putObject([
        'Bucket' => 'nimo' ,
        // 文件名称 + 文件名称
        'Key'    => '2018/one_two_thr.jpg' ,
        // 文件内容
        'Body'   => $avatar ,
        // 公共读取必须有否则只能上传
        'ACL'    => 'public-read' ,
        // 元数据必须增加 content-type
        'ContentType' => 'image/jpeg' ,
    ]);

    print_r($result['ObjectURL']);
//    var_dump($result);
} catch (S3Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}