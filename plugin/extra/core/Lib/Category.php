<?php

namespace Core\Lib;

/*
 * 获取结构化数据
 */
use Exception;

class Category {
    // 默认的字段字典
    public static $field = [
        'id'    => 'id' ,
        'p_id'   => 'p_id'
    ];

    // 初始化
    protected static function _field($field = null){
        $field = empty($field) ? self::$field : $field;
        if (!isset($field['id']) || !isset($field['p_id'])) {
            throw new Exception('传入的字段字典错误！');
        }
        return $field;
    }

    // 找到项
    public static function current($id , array $data = [] , $field = null){
        $field = self::_field($field);
        foreach ($data as $v)
        {
            if ($v[$field['id']] == $id)  {
                return $v;
            }
        }
        return false;
    }

    // 单个：获取父级
    public static function parent($id , array $data = [] , $field = null){
        $field  = self::_field($field);
        $cur    = self::current($id , $data , $field);
        foreach ($data as $v)
        {
            if ($v[$field['id']] == $cur[$field['p_id']]) {
                return $v;
            }
        }
        return false;
    }

    // 所有：获取父级
    public static function parents($id , array $data = [] , $field = null , $save_self = true , $struct = true){
        $field      = self::_field($field);
        $cur        = self::current($id , $data  , $field);
        $save_self  = is_bool($save_self) ? $save_self : true;
        $struct     = is_bool($struct) ? $struct : true;

        $get = function($cur , array $res = []) use(&$get , $data , $field){
            $parent = self::parent($cur[$field['id']] , $data , $field);
            if (!$parent) {
                return $res;
            }
            $res[] = $parent;
            return $get($parent , $res);
        };
        $parents = $get($cur);
        if ($save_self) {
            // 保留自身
            array_unshift($parents , $cur);
        }
        $parents = array_reverse($parents);
        if ($struct) {
            $get_struct = function($list , array $res = []) use(&$get_struct){
                if (count($list) === 0) {
                    return $res;
                }
                $cur = array_shift($list);
                $res = $cur;
                $res['children'] = $get_struct($list);
                return $res;
            };
            $parents = $get_struct($parents);
        }
        return $parents;
    }

    // 获取直系子集
    public static function children($id , array $data = [] , $field = null){
        $field  = self::_field($field);
        $res    = [];
        foreach ($data as $v)
        {
            if ($v[$field['p_id']] == $id) {
                $res[] = $v;
            }
        }
        return $res;
    }

    // 获取所有子集
    public static function childrens($id , array $data = [] , $field = null , $save_self = true , $struct = true){
        $field      = self::_field($field);
        $cur        = self::current($id , $data , $field);
        $save_self  = is_bool($save_self) ? $save_self : true;
        $struct     = is_bool($struct) ? $struct : true;
        if (!$struct) {
            $res = [];
            // 不保留层级结构
            $get = function($id , $floor = 1) use(&$get , $data , $field , &$res){
                $children   = self::children($id , $data , $field);
                foreach ($children as $v)
                {
                    // 附加字段：当前层级
                    $v['floor'] = $floor;
                    $res[] = $v;
                    $get($v[$field['id']] , $floor + 1);
                }
            };
            $get($id , 1);
            if ($save_self && $cur !== false) {
                // 保存自身
                array_unshift($res , $cur);
            }
        } else {
            // 保留层级结构
            $get_struct = function($id , $floor = 1) use(&$get_struct , $data , $field){
                $children = self::children($id , $data , $field);
                foreach ($children as &$v)
                {
                    $v['floor'] = $floor;
                    $v['children'] = $get_struct($v[$field['id']] , $floor + 1);
                }
                return $children;
            };
            $res = $get_struct($id , 1);
            if ($save_self && $cur !== false) {
                $cur['children'] = $res;
                $res = $cur;
            }
        }
        return $res;
    }
}