<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 14:21
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Util\RegionUtil;
use App\WebSocket\V1\Controller\Auth;
use App\WebSocket\V1\Util\TranslationUtil;
use Core\Lib\Validator;

class MiscAction extends Action
{
    public static function outside(Auth $auth , array $param)
    {
        $res = $auth->conn->getClientInfo($auth->fd);
        if (empty($res)) {
            return self::error('获取客户端信息失败' , 500);
        }
        // 改用腾讯地图 api
        $info = RegionUtil::getByIPUseQQMap($res['remote_ip']);
        if ($info['code'] != 0) {
            // 不支持的查询
            return self::success(1);
        }
        $info = $info['data'];

        if ($info['content'] == '中国') {
            // 在中国
            return self::success(0);
        }
        return self::success(1);
    }

    public static function translate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'target' => 'required' ,
            'value' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        if (empty($param['value'])) {
            return self::error('请提供待翻译的值');
        }
//        if (empty($auth->user->language)) {
//            return self::error('请先设置语言' , 403);
//        }
        $param['source'] = empty($param['source']) ? 'auto' : $param['source'];
        $translation_value = TranslationUtil::translate($param['value'] , $param['source'] , $param['target']);
        return self::success($translation_value);
    }
}