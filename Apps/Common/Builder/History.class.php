<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午2:23:14
// +----------------------------------------------------------------------
 */
namespace Common\Builder;
class History{
    private $first      =   0;
    private $maxSize    =   9;
    private $cookieName =   'history';
    public  $_data      =   array();
    
    public function __construct() {
        //$this->_data    =   cookie($this->cookieName);
    }
    
    /**
     * 添加数据
     * @param unknown $data
     */
    public function addHistory($data) {
        $his    =   $this->getHistory();
        if ($his) {
            foreach ($his as $k => $v) {
                if ($v['id'] == $data['id']) {
                    unset($his[$k]);
                }
            }
            array_unshift($his, $data);
            $count  =   count($his);
            if ($count > $this->maxSize) {
                array_pop($his);
            }
        } else {
            $his[$this->first]    =   $data;
        }
        $his    =   enCryptRestUri(serialize($his));
        cookie($this->cookieName, $his, array('expire' => 31536000));
    }
    
    /**
     * 清空历史记录
     * @param string $name
     */
    public function removeHistory($name = null) {
        cookie($this->cookieName, null);
    }
    
    /**
     * 获取数据
     * @return mixed
     */
    public function getHistory() {
        $this->_data    =   cookie($this->cookieName);
        return (unserialize(deCryptRestUri($this->_data)));
    }
    
    function __destruct() {
        unset($this->_data);
    }
}