<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class GroupMessageReadStatus extends Model
{
    protected $table = 'group_message_read_status';
    public $timestamps = false;

    // 消息是否读取
    public static function isRead($user_id , $group_message_id)
    {
        return self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['group_message_id' , '=' , $group_message_id] ,
                ])
                ->value('is_read');
    }

    public static function delByGroupId(int $group_id)
    {
        return DB::table('group_message_read_status as gmrs')
            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
            ->where('gm.group_id' , $group_id)
            ->delete();
    }

    public static function unreadCountByUserIdAndGroupId(int $user_id , int $group_id)
    {
        return DB::table('group_message as gm')
            ->leftJoin('group_message_read_status as gmrs' , 'gm.id' , '=' , 'gmrs.group_message_id')
            ->where([
                ['gm.group_id' , '=' , $group_id] ,
                ['gmrs.user_id' , '=' , $user_id] ,
                ['gmrs.is_read' , '=' , 'n'] ,
            ])
            ->count();
    }

    public static function updateStatus(int $user_id , int $group_id , string $is_read = 'y')
    {
        return DB::table('group_message_read_status as gmrs')
            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
            ->where([
                ['gmrs.user_id' , '=' , $user_id] ,
                ['gm.group_id' , '=' , $group_id] ,
            ])->update([
                'gmrs.is_read' => $is_read
            ]);
    }
}