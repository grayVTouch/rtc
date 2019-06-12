<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 10:07
 */

namespace App\Http\Action;


use App\Model\Project;
use App\Util\Misc;
use Core\Lib\Validator;

class ProjectAction extends Action
{
    public static function create(array $param)
    {
        $validator = Validator::make($param , [
            'name' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $param['identifier'] = Misc::identifier();
        if (!empty(Project::findByIdentifier($param['identifier']))) {
            // 检查 identifier 是否有冲突
            return self::error('系统生成的 identifier 和现有的冲突，请重新调用该请求以重新生成');
        }
        $id = Project::u_insertGetId($param['name'] , $param['identifier']);
        return self::success($id);
    }
}