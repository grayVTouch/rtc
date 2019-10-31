<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:34
 */

namespace App\Model;


class ArticleModel extends Model
{
    protected $table = 'article';

    public static function firstByArticleTypeId(int $article_type_id)
    {
        $res = self::where('article_type_id' , $article_type_id)
            ->first();
        self::single($res);
        return $res;
    }

    public static function countByArticleTypeId(int $article_type_id)
    {
        return (int) (self::where('article_type_id' , $article_type_id)->count());
    }

//    public static function
}