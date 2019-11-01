<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:34
 */

namespace App\Model;


use function core\convert_obj;

class ArticleModel extends Model
{
    protected $table = 'article';

    public static function firstByArticleTypeId(int $article_type_id)
    {
        $res = self::with(['articleType'])
            ->where('article_type_id' , $article_type_id)
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        ArticleTypeModel::single($res->article_type);
        return $res;
    }

    public static function countByArticleTypeId(int $article_type_id)
    {
        return (int) (self::where('article_type_id' , $article_type_id)->count());
    }

    public function articleType()
    {
        return $this->belongsTo(ArticleTypeModel::class , 'article_type_id' , 'id');
    }

    // 分页
    public static function list(array $filter = null , array $order = null , int $offset = 0 , int $limit = 20)
    {
        $filter = $filter ?? [];
        $order  = $order ?? [];
        $filter['id'] = $filter['id'] ?? '';
        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['value'] ?? 'desc';
        $where = [];
        if ($filter['id'] != '') {
            $where[] = ['id' , '=' , $filter['id']];
        }
        $res = self::with(['articleType'])
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->offset($offset)
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            ArticleTypeModel::single($v->article_type);
        }
        return $res;
    }

    public static function findByIdAndArticleTypeId(int $id , int $article_type_id)
    {
        $res = self::with(['articleType'])
                ->where([
                    ['id' , '=' , $id] ,
                    ['article_type_id' , '=' , $article_type_id] ,
                ])
                ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        ArticleTypeModel::single($res->article_type);
        return $res;
    }
}