<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/9
 * Time: 14:10
 */

namespace App\Http\Web;


use App\Model\GroupMemberModel;
use App\Model\GroupModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use Core\Lib\Hash;
use function core\random;
use Exception;

class Root extends Base
{
    // 创建数据测试
    public function createTestUser()
    {
        try {
            // 创建测试账号
            $count = 500;
            $user_ids = [
                600027 ,
                600028 ,
                600029 ,
            ];
            $last_id = 0;
            for ($i = 0; $i < $count; ++$i)
            {
                $username = random(11 , 'letter' , true);
                $password = '123456';
                $user = [
                    // 标识符
                    'identifier' => 'nimo' ,
                    // 用户名
                    'username' => $username ,
                    // 密码
                    'password' => Hash::make($password) ,
                    // 是否测试用户
                    'is_test' => 0
                ];
                // 新增用户
                $id = UserModel::insertGetId([
                    'identifier' => $user['identifier'] ,
                    'username' => $user['username'] ,
                    'password' => $user['password'] ,
                    'is_test' => $user['is_test'] ,
                ]);
                UserOptionModel::insertGetId([
                    'user_id' => $id
                ]);
                $last_id = $id;
                $user_ids[] = $id;
            }
            $group = [
                'name' => '测试群（500+用户）' ,
                'user_id' => $last_id ,
            ];
            $group_id = GroupModel::insertGetId($group);
            // 创建一个群
            foreach ($user_ids as $v)
            {
                // 加入群
                GroupMemberModel::u_insertGetId($v , $group_id);
            }
            return $this->success('创建成功');
        } catch(Exception $e) {
            throw $e;
        }
    }
}