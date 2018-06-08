<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午2:14:53
// +----------------------------------------------------------------------
 */
namespace Cart\Controller;
class IndexController extends AuthController {
    
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->authApi('/CartVer2/goods_list')->with();
        $this->authApi('/Cart/cart_total')->with('count');
       	C('seo', ['title' => '我的购物车']);
        $this->display();
    }
}