<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 上午11:37:02   设置密码
// +----------------------------------------------------------------------
 */
namespace Cart\Controller;
class AccountController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function payPass() {
        $run    =   A('Ucenter/Account');
        $run->payPass();
    }
}