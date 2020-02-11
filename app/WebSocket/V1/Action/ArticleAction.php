<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:31
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Model\ArticleModel;
use App\WebSocket\V1\Util\PageUtil;
use App\WebSocket\V1\Controller\Auth;
use Core\Lib\Validator;

class ArticleAction extends Action
{

    public static function firstByArticleTypeId(Auth $auth , int $article_type_id , array $param)
    {
        $res = ArticleModel::firstByArticleTypeId($article_type_id);
        return self::success($res);
    }

    public static function listForArticle(Auth $auth , int $article_type_id , array $param)
    {
        $page   = empty($param['page']) ? 1 : $param['page'];
        $limit  = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $total  = ArticleModel::countByArticleTypeId($article_type_id);
        $page   = PageUtil::deal($total , $page , $limit);
        $res = ArticleModel::list([
            'article_type_id' => $article_type_id
        ] , null , $page['offset'] , $page['limit']);
        $res   = PageUtil::data($page , $res);
        return self::success($res);
    }

    public static function detailForArticle(Auth $auth , int $article_type_id , array $param)
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