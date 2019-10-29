<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/3/18
 * Time: 14:46
 */


$str = 'srJCddLmpwpAkMLuXvOfYLCVxSc7poeW4O0ts4JBCFD5xHTIqc2p3qMgeIg1HM1B';

$enc = base64_decode($str);
//$enc = $str;

// 密钥
$key1 = 'abcdefgh12345678';

// 初始化向量 16位字符串（之所以 16byte 是因为）
// 在 128bit aes 加密算法里面
// 128bit / 8bit = 16byte
// 所以是 16 字节
// 如果是 256bit ，那么就是 256 / 8
$iv1 = '1234567890123456';

// 加密数据 'AES-128-CBC' 可以通过openssl_get_cipher_methods()

// 解密数据
$data1 = openssl_decrypt($enc, 'AES-128-CBC', $key1, OPENSSL_RAW_DATA , $iv1);

//var_dump($data);
var_dump($data1);



$test_str = 'fuck';

$enc = openssl_encrypt($test_str , 'AES-128-CBC' , $key1 , OPENSSL_RAW_DATA , $iv1);
//var_dump($enc);
var_dump(base64_encode($enc));

$data2 = openssl_decrypt($enc, 'AES-128-CBC', $key1, OPENSSL_RAW_DATA , $iv1);

var_dump($data2);


