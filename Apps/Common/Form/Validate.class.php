<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/2/7
 * Time: 9:25
 */

namespace Common\Form;


class Validate
{
    const PREFIX = '';      //前缀

    public static $instance;    //实例

    protected $options;     //validate

    public function __construct($options)
    {
        $this->options = $options;
    }

    public static function getInstance($options) {
        //if (self::$instance instanceof self == false) {
        self::$instance = new self($options);
        //}
        return self::$instance;
    }

    /**
     * 生成validate
     *
     * @return string
     */
    public function create() {
        $validate = '';
        if (!is_array($this->options)) {
            $this->options = explode('=', rtrim(ltrim($this->options, ' '), ' '));
        }

        foreach ($this->options as $k => $v) {
            if (is_numeric($k)) {
                $k = $v;
                $v = 'true';
            }
            $v = trim($v, ' ');
            $validate .= self::PREFIX . $k . '="' . ($v !== false ? $v : true) . '" ';
        }
        return $validate;
    }


    public function __destruct()
    {
        $this->options = null;
        // TODO: Implement __destruct() method.
    }
}