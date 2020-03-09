<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/13
 * Time: 22:28
 */

namespace App\WebSocket\V1\Util;

use App\WebSocket\V1\Model\TranslationModel;
use function core\obj_to_array;
use Exception;
use function extra\has_cn;
use function extra\has_en;
use function extra\is_http;


class TranslationUtil extends Util
{

    // 翻译
    public static function translate($value = null , string $source = 'cn' , string $target = 'cn')
    {
        if (empty($value)) {
            return $value;
        }
        $scalar = is_scalar($value);
        $source = $source == 'cn' ? 'zh-CHS' : $source;
        $target = $target == 'cn' ? 'zh-CHS' : $target;
        return $scalar ? self::single($value , $source , $target) : self::multiple($value , $source , $target);
    }

    // 标量值：中文 => 英文
    private static function single(string $value = '' , string $source = 'zh-CHS' , string $target = 'en')
    {
        return self::persistent($value , $source , $target);
    }

    // 对象，属性值：中文 => 英文
    private static function multiple($value = null , string $source = 'zh-CHS' , $target = 'en')
    {
        if (empty($value)) {
            return $value;
        }
        $value = obj_to_array($value);
        foreach ($value as &$v)
        {
            if (is_array($v)) {
                 $v = self::multiple($v , $source , $target);
                continue ;
            }
            $v = self::persistent($v , $source , $target);
        }
        return $value;
    }

    // 保存到映射表（持久化）
    private static function persistent($value = '' , string $source = 'zh-CHS' , string $target = 'en')
    {
        if ($source == $target) {
             return $value;
        }
        if (!is_scalar($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return $value;
        }
        if (is_http($value)) {
            return $value;
        }
//        if ($source == 'zh-CHS' && !has_cn($value)) {
//            return $value;
//        }
//        if ($source == 'en' && !has_en($value)) {
//            return $value;
//        }
        $original = $value;
        $res = TranslationModel::findBySourceLanguageAndTargetLanguageAndOriginal($source , $target , $original);
        // 检查是否存在
        if (!empty($res)) {
            return $res->translation;
        }
        var_dump("source: {$source}; target: {$target}; value: {$value}");
        $translation = YouDaoTranslationUtil::translate($value , $source , $target);
        if (!empty($translation)) {
            // 保存到翻译表
            $data = [
                'source_language'   => $source ,
                'target_language'   => $target ,
                'original'          => $original ,
                'translation'       => $translation
            ];
            TranslationModel::insert($data);
        }
        return $translation;
    }
}