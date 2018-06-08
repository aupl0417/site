<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午2:14:47
// +----------------------------------------------------------------------
 */
namespace Cart\Controller;
class RunController extends AuthController {
	protected $run;
	public function _initialize() {
		parent::_initialize();
		$this->run    =   A('Home/Run');
	}
	
    public function index() {
        if (IS_POST) {
            $this->run->index();
        }
    }
    
    public function authRun() {
        if (IS_POST) {
            $this->run->authRun();
        }
    }
}