<?php
namespace Wap\Controller;
use Think\Controller;
class LoginController extends CommonController {
    public function index(){		
		$this->display();
    }
	
	public function check_login(){
		$res=$this->doApi('/User/login',$this->data);
		if($res->code==1){
    		session('user',objectToArray($res->data));

            //$jm=new \Think\Crypt\Driver\Crypt();
            //cookie('user',array('openid'=>$jm::encrypt($res->data->openid,C('CRYPT_PREFIX'))));
            S(md5(session_id()),$res->data->erp_uid,3600); //用于ERP快捷登录商城
            cookie('remember', enCryptRestUri(serialize(objectToArray($res->data))), 86400);
		}
		$this->ajaxReturn(array('code'=>$res->code,'msg'=>$res->msg));
	}

	/**
	* 是否已登录
	*/
	public function is_logined(){
		if(session('user')) $result['code']=1;
		else $result['code']=0;

		$this->ajaxReturn($result);
	}
	/**
	* 是否已认证
	*/
	public function is_authed(){
		if(session('user.is_auth')>0) $result['code']=1;
		else $result['code']=0;

		$this->ajaxReturn($result);
	}	
    /**
    * 登出
    */
    public function logout(){
        /*
		session('user',null);
        cookie('user',null);
        redirect('/');
		*/
		session('user',null);
        cookie('remember',null);
		$result['code']=1;
		$this->ajaxReturn($result);
    }	
}