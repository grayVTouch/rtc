<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/14
 * Time: 11:20
 */

namespace App\WebSocket\V1\Controller;


use Exception;
use Illuminate\Support\Facades\DB;

class Test extends Base
{
    public function index(array $param)
    {
        try {
            DB::beginTransaction();
            var_dump("开启事务成功");
            DB::insert('insert into test_user (name) value ("running")');
            DB::insert('insert into test_user (name,value) value ("running")');
//            throw new Exception("exception");
            var_dump("抛出异常后程序正常执行");
            DB::commit();
            var_dump("程序提交成功");
        } catch(Exception $e){
            DB::rollBack();
            var_dump("程序回滚成功");
        }
    }
}