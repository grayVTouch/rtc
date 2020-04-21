<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/3/9
 * Time: 11:07
 */

namespace App\Http\ApiV1\Model;


class TranslationModel extends Model
{
    protected $table = 'translation';

    public static function findByOriginal(string $original)
    {
        $res = self::where('original' , $original)
            ->find();
        self::single($res);
        return $res;
    }

    public static function findBySourceLanguageAndTargetLanguageAndOriginal(string $source_language , string $target_language , string $original)
    {
        $res = self::where([
                ['source_language' , '=' , $source_language] ,
                ['target_language' , '=' , $target_language] ,
                ['original' , '=' , $original] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

}