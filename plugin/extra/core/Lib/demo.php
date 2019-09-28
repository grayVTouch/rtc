<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/4/9
 * Time: 11:52
 */

require_once __DIR__ . '/../Function/base.php';
require_once __DIR__ . '/../Function/file.php';

require_once __DIR__ . '/File.php';
require_once __DIR__ . '/Image.php';


$image = __DIR__ . '/test.jpg';

use Core\Lib\Image;
$i = new Image(__DIR__);
$res = $i->single($image);

echo "<img src='{$res}'>";