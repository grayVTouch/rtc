<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\Model;


use function core\random;

class Group extends Model
{
    protected $table = 'group';
    public $timestamps = false;

    public static function temp(string $identifier)
    {
        $data = [
            'identifier' => $identifier ,
            'name'     => 'group(test)_' . random(6 , 'mixed' , true) ,
            'is_temp'    => 'y' ,
        ];
        $id = self::insertGetId($data);
        return self::findById($id);
    }
}