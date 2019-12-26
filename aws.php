<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/25
 * Time: 14:38
 */

require_once __DIR__ . '/plugin/aws/vendor/autoload.php';


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
        'verify' => __DIR__ . '/cert.pem' ,
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



//
//
//$filepath = __DIR__ . '/avatar.png';
//$file_md5 = md5_file($filepath);
//$avatar = file_get_contents($filepath);
//$filename = 'test.png';
//$bucket = 'nimo';
//
//$uploader = new \Aws\S3\MultipartUploader($s3, $filepath, [
//    //存储桶
//    'bucket' => $bucket,
//    //上传后的新地址
//    'key'    => $file_md5,
//    //设置访问权限  公开,不然访问不了
//    'ACL'    => 'public-read',
//    //分段上传
//    'before_initiate' => function (\Aws\Command $command) {
//        // $command is a CreateMultipartUpload operation
//        $command['CacheControl'] = 'max-age=3600';
//    },
//    'before_upload'   => function (\Aws\Command $command) {
//        // $command is an UploadPart operation
//        $command['RequestPayer'] = 'requester';
//    },
//    'before_complete' => function (\Aws\Command $command) {
//        // $command is a CompleteMultipartUpload operation
//        $command['RequestPayer'] = 'requester';
//    },
//]);
//
//try {
//    $result = $uploader->upload();
//    //上传成功--返回上传后的地址
//    $data = [
//        'type' => '1',
//        'data' => urldecode($result['ObjectURL'])
//    ];
//} catch (Aws\Exception\MultipartUploadException $e) {
//    //上传失败--返回错误信息
//    $uploader =  new \Aws\S3\MultipartUploader($s3, $filepath, [
//        'state' => $e->getState(),
//    ]);
//    $data = [
//        'type' => '0',
//        'data' =>  $e->getMessage(),
//    ];
//}
//
//print_r($data);
//var_dump($data);