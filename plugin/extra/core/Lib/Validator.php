<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 8:27
 */

namespace Core\Lib;

class Validator
{
    protected $data = null;

    protected $rule = null;

    protected $_message = null;

    protected $_error = [];

    public function __construct(array $data , array $rule , array $message)
    {
        $this->data = $data;
        $this->rule = $rule;
        $this->_message = $message;
    }

    public static function make(array $data , array $rule , array $message = [])
    {
        return (new static($data , $rule , $message));
    }


    // 验证规则
    protected function required($value)
    {
        return !($value === null || $value === '');
    }

    public function fails()
    {
        foreach ($this->data as $k => $v)
        {
            foreach ($this->rule as $k1 => $v1)
            {
                $_rule = explode('|' , $v1);
                if ($k == $k1) {
                    foreach ($_rule as $v2)
                    {
                        $check = $this->$v2($v);
                        if ($check) {
                            continue ;
                        }
                        $this->_error[$k] = $this->_message[sprintf('%s.%s' , $k , $v2)] ?? sprintf('%s is required' , $k);
                    }
                }
            }
        }
        if (empty($this->_error)) {
            return false;
        }
        return true;
    }

    public function error()
    {
        return $this->_error;
    }

    public function message()
    {
        $error = array_values($this->_error);
        return $error[0] ?? '';
    }
}