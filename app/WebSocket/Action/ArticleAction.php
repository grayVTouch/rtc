<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:31
 */

namespace App\WebSocket\Action;


use App\Model\ArticleModel;
use App\WebSocket\Auth;

class ArticleAction extends Action
{

    public static function firstByArticleTypeId(Auth $auth , int $article_type_id , array $param)
    {
        $res = ArticleModel::firstByArticleTypeId($article_type_id);
        return self::success($res);
    }

    public static function listForHelpCenter(Auth $auth , int $article_type_id , array $param)
    {

    }

    public static function detailForHelpCenter(Auth $auth , int $article_type_id , array $param)
    {

    }


}