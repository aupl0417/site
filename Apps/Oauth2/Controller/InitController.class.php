<?php
namespace Oauth2\Controller;
use Wap\Controller\CommonController;	//继承wap模块的公共文件
class InitController extends CommonController {
    public function _initialize() {
		parent::_initialize();
	}


	/**
     * 是否登录
     */
	public function check_login(){
	    if(empty($_SESSION['user'])){
	        redirect(C('sub_domain.user').'/login.html');
        }
    }
}
