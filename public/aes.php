<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/3/18
 * Time: 14:46
 */

// 要加密的字符串
$data = 'hello boys and girls!!we will win the fight!!';

// 密钥
$key = 'fuck';

// 初始化向量 16位字符串（之所以 16byte 是因为）
// 在 128bit aes 加密算法里面
// 128bit / 8bit = 16byte
// 所以是 16 字节
// 如果是 256bit ，那么就是 256 / 8
$iv = '1234567890123456';

// 加密数据 'AES-128-CBC' 可以通过openssl_get_cipher_methods()
// OPENSSL_RAW_DATA ，会使用 pkcs7 进行补位
$res = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

var_dump($res);
$base64 = base64_encode($res);
var_dump($base64);

// 解密数据
$data = openssl_decrypt($res, 'AES-128-CBC', $key, OPENSSL_RAW_DATA , $iv);

var_dump($data);

