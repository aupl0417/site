<?php
namespace Sellergoods\Controller;
use Common\Builder\Auth;
use Think\Controller;
class ApiController extends InitController {
    public function index(){		
		echo 'error';
    }
	
	public function api(){
		//C('DEBUG_API',true);
		//dump(I('post.'));

		foreach($_POST as $key=>$val){
			if(strstr($key,'password') && trim($val)!='') $_POST[$key]=$this->password(trim($val));
			if(is_array($val)) $_POST[$key]=implode(',',$val);
		}

		$res=$this->_api(I('post.'));

		$this->ajaxReturn($res);
	}
	

	/**
	* 同时请求多个接口
	*/
	public function apis(){
		foreach(I('post.') as $key=>$val){
			$result[$key]=$this->_api($val);
		}
		$this->ajaxReturn($result);
	}

	/**
	* 单个接口请求
	*/
	public function _api($data){
		$apiurl		=$data['apiurl'];
		$no_sign	=$data['no_sign'];
		$data		=array_merge($this->api_cfg,$data);

		unset($data['apiurl']);
		if(isset($data['no_sign'])) unset($data['no_sign']);
		if($data['is_openid']==1){
			if(session('user.openid')!=''){
				$data['openid']=session('user.openid');
				unset($data['is_openid']);
			}else{
				$res['code']=0;
				$res['msg']='请先登录后再操作！';
				return $res;
			}			
		}

		$res=$this->doApi($apiurl,$data,$no_sign);	

		return $res;		
	}

}