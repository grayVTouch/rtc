<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/12
 * Time: 22:36
 */


require_once __DIR__ . '/plugin/extra/core/Lib/FacadeInterface.php';
require_once __DIR__ . '/plugin/extra/core/Lib/Facade.php';

use Core\Lib\Facade;

class A {
    public static $ins = [];

    public static function set($k , $v)
    {
        self::$ins[$k] = $v;
    }
    public static function __callStatic($name, $arguments)
    {
        $ins = self::$ins[static::get()];
        $ins->$name();
    }
}

class B extends A {
    public function test()
    {
        echo 'b';
    }

    public static function get()
    {
        return 'b';
    }
}

class C extends A {
    public function test()
    {
        echo 'c';
    }

    public static function get()
    {
        return 'c';
    }
}

$b = new B();
$c = new C();

A::set('b' , $b);
A::set('c' , $c);

$b->test();
$c->test();

echo 'hello boys and girls';
echo 'hello boys and girls';
