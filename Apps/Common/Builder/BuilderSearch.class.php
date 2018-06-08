<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 上午9:21:54
// +----------------------------------------------------------------------
 */
namespace Common\Builder;
class BuilderSearch extends BuilderForm {
    public function searchDay($name, $title, $options, $type = 'searchDay') {
        $this->key($name, $title, '', '', $options, $type);
        return $this;
    }
}