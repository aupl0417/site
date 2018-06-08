<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午2:25:11    Builder
// +----------------------------------------------------------------------
 */
namespace Common\Builder;
use Think\Controller;
class BuilderForm extends Controller {
    protected $_keyList     =   array();
    protected $_data        =   array();
    
    /**
     * key
     * @param string $name          表单name
     * @param string $title         表单标题
     * @param string $isRequired    是否为必须
     * @param string $subtitle      tips
     * @param string $options       表单选项数据  key => val   id => name  1 => 北京市
     * @param string $type          表单类型
     * 
     */
    public function key($name, $title, $isRequired = null, $subtitle = null, $options = null, $type = null , $other = null) {
        $this->_keyList[]   =   array('name' => $name, 'title' => $title, 'isRequired' => $isRequired, 'subtitle' => $subtitle, 'options' => $options, 'type' => $type ,'other' => $other);
        return $this;
    }
    
    /**
     * 主键编辑时可以使用
     * @param string $name
     */
    public function keyId($name = 'id', $type = 'id') {
        $this->key($name, '', '', '', '', $type);
        return $this;
    }
    
    /**
     * 直接显示文字
     * @param unknown $title
     * @param unknown $subtitle
     * @param string $type
     */
    public function keyHtmltext($title, $subtitle, $type = 'htmltext') {
        $this->key('', $title, '', $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 搜索下拉框
     * @param unknown $name
     * @param unknown $title
     * @param string $subtitle
     * @param string $isRequired
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keySearchSelect($name, $title, $subtitle = null, $isRequired = null, $type = 'searchSelect') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * input
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     */
    public function keyText($name, $title, $isRequired = null, $subtitle = null, $type = 'input') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    public function keyPass($name, $title, $isRequired = 1, $subtitle = null, $type = 'pass') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    public function keySelect($name, $title, $options, $isRequired = null, $subtitle = null, $type = 'select') {
        $this->key($name, $title, $isRequired, $subtitle, $options, $type);
        return $this;
    }
    
    public function keyTextArea($name, $title, $isRequired = null, $subtitle = null, $type = 'textarea') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    public function keyCheckBox($name, $title, $options, $isRequired = null, $subtitle = null, $type = 'checkbox') {
        $this->key($name, $title, $isRequired, $subtitle, $options, $type);
        return $this;
    }
    
    /**
     * 协议
     * @param unknown $name
     * @param unknown $title
     * @param string $subtitle
     * @param number $isRequired
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyProtocol($name, $title, $subtitle = null, $isRequired = 1, $type = 'protocol') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 验证码
     * @param unknown $name
     * @param unknown $title
     * @param string $subtitle
     * @param number $isRequired
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyVcode($name = 'vcode', $title = '图形验证码', $subtitle = null, $isRequired = 1, $type = 'vcode') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 短信验证码
     * @param unknown $name
     * @param unknown $title
     * @param string $subtitle
     * @param number $isRequired
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keySmsCode($name, $title, $subtitle = null, $isRequired = 1, $type = 'smscode') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 多图上传
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyMultiImages($name, $title, $isRequired = null, $subtitle = null, $type = 'mutilImages') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    /**
     * 单图上传
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keySingleImages($name, $title, $isRequired = null, $subtitle = null, $type = 'singleImages') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 编辑器
     * @param unknown $name
     * @param unknown $title
     * @param string $options
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyUeditor($name, $title, $options = null, $isRequired = null, $subtitle = null, $type = 'ueditor') {
        $this->key($name, $title, $isRequired, $subtitle, $options, $type);
        return $this;
    }
    
    /**
     * 城市
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyCity($name, $title, $isRequired = null, $subtitle = null, $type = 'city') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 日期选择
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyDate($name, $title, $isRequired = null, $subtitle = null, $type = 'date') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 日期选择
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyDatetime($name, $title, $isRequired = null, $subtitle = null, $type = 'datetime') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 文件上传
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyFile($name, $title, $isRequired = null, $subtitle = null, $type = 'file') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }
    
    /**
     * 表单数据
     * @param unknown $data
     * @return \Common\Builder\BuilderForm
     */
    public function data($data) {
        $this->_data    =   $data;
        return $this;
    }
    
    /**
     * 按时间搜索
     * @param unknown $name
     * @param unknown $title
     * @param unknown $options
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keySday($name, $title, $options, $subtitle = null, $type = 'searchDay') {
        $this->key($name, $title, '', $subtitle, $options, $type);
        return $this;
    }
    
    /**
     * 评分
     * @param unknown $name
     * @param unknown $title
     * @param unknown $options
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyRate($name, $title, $options, $isRequired = null, $subtitle = null, $type = 'rate') {
        $this->key($name, $title, $isRequired, $subtitle, $options, $type);
        return $this;
    }
    
    /**
     * 商品选择
     * @param unknown $name
     * @param unknown $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     * @return \Common\Builder\BuilderForm
     */
    public function keyGoods($name, $title, $isRequired = null, $subtitle = null, $type = 'goods') {
        $this->key($name, $title, $isRequired, $subtitle, '', $type);
        return $this;
    }

    /**
     * 收货/发货地址选择
     * @param string $name
     * @param string $title
     * @param string $isRequired
     * @param string $subtitle
     * @param string $type
     */
    public function selectAddress($option,$src_data,$status = 1) {
        if($status == 1) {
            $this->key($option['name'], $option['title'], $option['isRequired'], $option['subtitle'], $src_data, 'selectAddress',$option['other']);
        }
        return $this;
    }
    
    /**
     * 返回表单数据
     * @return Ambigous <multitype:, multitype:string >
     */
    public function view($name = 'keyList') {
        if (!empty($this->_data)) {
            foreach ($this->_keyList as &$val) {
                if (!empty($val['name'])) {
                    if (array_key_exists($val['name'], $this->_data)) {
                        $val['value']   =   $this->_data[$val['name']];
                    }
                    if (is_array($val['name'])) {
                        foreach ($val['name'] as $k => $v) {
                            if (array_key_exists($v, $this->_data)) {
                                $val['value'][$k]   =   $this->_data[$v];
                            }
                        }
                    }
                }
            }
        }
        unset($v, $val, $k);
        $this->assign($name, $this->_keyList);
        //return $this->_keyList;
    }
    
    function __destruct() {
        unset($this->_data, $this->_keyList, $this);
    }
}