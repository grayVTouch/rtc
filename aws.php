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

$credentials = new Aws\Credentials\Credentials('AKIAIXU3H2XGLTGGQDZQ', 'GZ/6LfodBcubRc5MB2ZLoxw2edUfgCv5RcEjtU0o');

//Create a S3Client
$s3 = new Aws\S3\S3Client([
//    'profile' => 'default',
    'version' => 'latest',
    'region' => 'us-east-2' ,
    'credentials' => $credentials
]);

$avatar = file_get_contents(__DIR__ . '/avatar.png');
$filename = 'test.png';
$bucket = 'nimo';


// 上传文件
$res = $s3->putObject([
    'Bucket' => 'nimo',
    'Key' => $filename,
    'Body' => $avatar ,
]);

$res = $s3->getObject([
    'Bucket' => $bucket,
    'Key' => $filename
]);

// Print the body of the result by indexing into the result object.
var_dump($res['Body']);