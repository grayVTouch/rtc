<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:31
 */

namespace App\WebSocket\Action;


use App\Model\ArticleModel;
use App\Util\PageUtil;
use App\WebSocket\Auth;
use App\WebSocket\Base;
use Core\Lib\Validator;

class ArticleNoAuthAction extends Action
{

    public static function firstByArticleTypeId(Base $base , int $article_type_id , array $param)
    {
        $res = ArticleModel::firstByArticleTypeId($article_type_id);
        return self::success($res);
    }

    public static function detailForArticle(Base $base , int $article_type_id , array $param)
    {
        $validator = Validator::make($param , [
            'id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = ArticleModel::findByIdAndArticleTypeId($param['id'] , $article_type_id);
        if (empty($res)) {
            return self::error('未找到对应的文章' , 404);
        }
        return self::success($res);
    }


}