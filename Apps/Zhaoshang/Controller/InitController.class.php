<?php
namespace Zhaoshang\Controller;
use Wap\Controller\CommonController;	//继承wap模块的公共文件
class InitController extends CommonController {
    protected $zs;  //招商介绍

    protected $userInfo;

    public function _initialize() {
		parent::_initialize();
        $this->zs = M('zhaoshang')->cache(false)->field('atime,etime,ip',true)->find();
        $this->assign('zs',$this->zs);
	}


	/**
     * 是否登录
     */
	public function check_login(){
	    if(empty($_SESSION['user'])){
	        redirect(C('sub_domain.user').'/login.html');
        }elseif(session('user.shop_id') > 0){ //已开店
            redirect(U('/Index/opened'));
        }
    }



    public function check_auth($res){
    	if($res->data->auth != 1 || $res->data->u_level < 3 || $res->data->u_type != 1){
            redirect(U('/Index/auth_failed'));
            exit();
        }

        return true;
    }
    
    public function checkup_auth($res){
    	if($res->data->is_auth== 1 && $res->data->level_id>= 3 && $res->data->type== 1 && $res->data->shop_type==6){
    		return true;
    	}else {
    		redirect(U('/Index/auth_failed'));
    		exit();
    	}
    }

    public function ajax_check_login(){
        if(empty($_SESSION['user'])){
            $this->ajaxReturn(['code' => 0,'msg' => '请先登录后再操作！']);
        }
    }
}
