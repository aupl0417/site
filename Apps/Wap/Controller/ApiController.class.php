<?php
namespace Wap\Controller;
use Think\Controller;
class ApiController extends CommonController {
    public function index(){		
		echo 'error';
    }
	
	public function api(){
		//C('DEBUG_API',true);
		//dump(I('post.')); 
		foreach($_POST as $key=>$val){
			if(strstr($key,'password') && trim($val)!='') $_POST[$key]=$this->password(trim($val));
		}
		$res=$this->_api(I('post.'));
//dump($res);
		//用户登录
		if($res->code==1 && I('post.apiurl')=='/Erp/check_login'){
			//dump($res->data);
			session('user',objectToArray($res->data));
            $jm=new \Think\Crypt\Driver\Crypt();
            cookie('user',array('openid'=>$jm::encrypt($res->data->openid,C('CRYPT_PREFIX'))));						
		}

		//记录商品搜索关键词
		if(I('post.apiurl')=='/Goods/goods_list' && trim($_POST['q'])!=''){
			$goods_q=cookie('goods_q');
			if(!in_array(trim($_POST['q']),$goods_q)){
				$goods_q[]=trim($_POST[q]);
				cookie('goods_q',$goods_q);
			}
		}

		//记录店铺搜索关键词
		if(I('post.apiurl')=='/Goods/shop_list' && trim($_POST['q'])!=''){
			$shop_q=cookie('shop_q');
			if(!in_array(trim($_POST['q']),$shop_q)){
				$shop_q[]=trim($_POST[q]);
				cookie('shop_q',$shop_q);
			}
		}

		$this->ajaxReturn($res);
	}
	
	/**
	* 需要更新session的接口
	*/
	public function user_info($apiurl){
		$arr=array('/UserUpgrade/upgrade_create_pay','/UserUpgrade/agent_create_pay');
		if(session('user') && in_array($apiurl,$arr)){
			if($rs=D('Common/UserRelation')->relation(true)->where(array('openid'=>session('user.openid')))->field('etime,ip',true)->find()){
				session('user',$rs);			
			}
		}
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
		//dump($res);
		if($res->code==1) $this->user_info($apiurl);
		
		return $res;		
	}

}