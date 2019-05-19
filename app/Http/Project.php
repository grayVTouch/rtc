<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 23:41
 */

namespace App\Http;


use App\Http\Action\ProjectAction;

class Project extends Base
{
    // 创建项目
    public function create()
    {
        $param = $this->request->post;
        $param['name'] = $param['name'] ?? '';
        $res = ProjectAction::create($param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}