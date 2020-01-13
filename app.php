<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 16:45
 */


/**
 * ***************
 * 定义测试环境
 * ***************
 *
 * 可选的值有：
 *
 * production-生产环境
 * development-开发环境
 *
 * 根据开发还是调试可以选择不同的环境
 * 这样就不会导致相关方面冲突
 */
//const ENV = 'production';
const ENV = 'development';

require_once __DIR__ . '/bootstrap/bootstrap.php';
