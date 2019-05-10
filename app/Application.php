<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:29
 */

namespace App;



use App\Model\User;

class Application
{
    public function run()
    {
        $user = User::where('user_id' , 1)->first()->toArray();
        print_r($user);
    }
}