<?php

require_once __DIR__ . '/vendor/autoload.php';

use OSS\OssClient;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/26
 * Time: 15:36
 */

class Uploader
{
    private $key = 'LTAI4FrndqPbAAn12a4HN1Q9';

    private $secret = 'FM1btMel4hxvoHRQNvADDPuj2vrhZM';

    // 客户端
    private $client = null;

    private $bucket = 'hichats';

    private $debug = false;

    // 亚马逊云存储返回的数据中使用的域名
    private $ossHost = 'http://hichats.oss-ap-southeast-1.aliyuncs.com/';

    // 自定义域名
    private $myHost = 'http://hichats.oss-ap-southeast-1.aliyuncs.com/';

    // endpoint
    private $endpoint = 'oss-ap-southeast-1.aliyuncs.com';

    private $typeRange = ['image/jpeg' , 'image/png' , 'image/gif'];

    public function __construct()
    {
        $this->client = new OssClient($this->key, $this->secret, $this->endpoint);
    }

    // 文件上传
    public function upload($filename , $content)
    {
//        if (!in_array($type , $this->typeRange)) {
//            return error('不支持的 type' , 403);
//        }
        $date = date('Ymd');
        try {
            $option = [
                OssClient::OSS_HEADERS => [
                    'x-oss-object-acl' => 'public-read' ,
                ] ,
            ];
            $res = $this->client->putObject($this->bucket , $filename , $content , $option);
//            print_r($res);
            if (empty($res)) {
                return $this->error('上传文件到 oss 失败' , 500);
            }
            $oss_url = $this->genOssUrl($res['oss-request-url'] ?? '');
            return $this->success($oss_url);
        } catch (Exception $e) {
            $line = sprintf("%s; Error! %s" , date('Y-m-d H:i:s' , time()) , $e->getMessage());
            if ($this->debug) {
                $log = __DIR__ . '/log/' . $date . 'runtime.log';
                if (!file_exists($log)) {
                    // 写入文件
                    file_put_contents($log , '');
                }
                file_put_contents($log , $line , LOCK_EX|FILE_APPEND);
            }
            return $this->error($line , 500);

        }
    }

    // 生成 oss 地址
    public function genOssUrl($url)
    {
        return str_replace($this->ossHost , $this->myHost , $url);
    }

    // 删除文件
    public function del($filename)
    {
        $res = $this->client->deleteObject($this->bucket , $filename);
//        var_dump($res);
        return $this->success();
    }

    public function getFilenameByUrl($url)
    {
        $my_host = rtrim($this->myHost , '/');
        $oss_host = rtrim($this->ossHost , '/');
        $my_host .= '/';
        $oss_host .= '/';
        $res = str_replace($my_host , '' , $url);
        $res = str_replace($oss_host , '' , $res);
        return $res;
    }

    // 删除多个文件
    public function delAll(array $files = [])
    {
        $res = $this->client->deleteObjects($this->bucket , $files);
        return $this->success();
    }

    private function success($data = '' , $code = 0)
    {
        return $this->response($data , $code);
    }

    private function error($data = '' , $code = 500)
    {
        return $this->response($data , $code);
    }

    private function response($data = '' , $code = 0)
    {
        return [
            'code' => $code ,
            'data' => $data
        ];
    }
}