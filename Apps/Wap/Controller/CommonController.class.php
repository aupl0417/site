<?php
namespace Wap\Controller;
use Think\Controller;
class CommonController extends Controller {
	protected $api_url	='';		//API请求地址前缀
	protected $api_cfg 	=array(); 	//API接口配置
	protected $data 	=array();	//要请求的数据

	public function _initialize() {
		$cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
		$cfg['api']['apiurl']=DM('rest');
        $cfg['erp']['domain']=eval(html_entity_decode($cfg['erp']['domain']));
		C('cfg',$cfg);

		C('sub_domain',sub_domain());

		//停用旧版wap
		if(C('sub_domain.wap') == strtolower(($_SERVER['HTTPS']=='on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'])) {
			header('location:'.C('sub_domain.m'));
			exit();
		}

		//加载错误代码库
		S('error_code',null);
		$error_code=D('Common/ErrorCode')->error_code();
		C('error_code',$error_code);
		

        /*
		$cookie_user=cookie('user');
		if($cookie_user && empty($_SESSION['user']['id'])){
			$jm=new \Think\Crypt\Driver\Crypt();
			$openid=$jm::decrypt($cookie_user['openid'],C('CRYPT_PREFIX'));

			if($rs=D('Common/UserRelation')->relation(true)->where(array('openid'=>$openid))->field('etime,ip',true)->find()){
				$rs['level_name']=$rs['level']['level_name'];
				session('user',$rs);
            	cookie('user',array('openid'=>$cookie_user['openid']));				
			}
		}
        */
        $this->cookie_login();  //检查cookie登录状态
		$this->api_cfg=$this->get_api_cfg();
		$this->api_url=C('cfg.api')['apiurl'];

		$this->data=$this->api_cfg;
		if(IS_POST && !empty($_POST)) $this->data=array_merge($this->data,$_POST);

		$this->assign('sex',array('保密','男','女'));
	}

	/**
	* 检查是否登录
	*/
	public function check_logined(){
		if(empty($_SESSION['user'])) redirect('/Login');
	}

	/**
	* 接口授权检查
	*/
	public function get_api_cfg(){
		$param=C('cfg.api');
		unset($param['apiurl']);
		return $param;
	}


	/**
	* 错误提示页面
	*/
	public function err($msg){
		$this->error($msg);
	}


	/**
	* 文件上传
	*/
    public function _upload($field,$width=0,$height=0){
        if (empty($_FILES)) {
	        $result['code']=53;
	        $result['status']=0;
	        $result['msg']=C('error_code')[$result['code']];
	        return $result;
        }

	    //充许上传格式
	    $ext_arr    =array('gif','jpg','jpeg','png');
	    $file_ext   =strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	    if(!in_array($file_ext,$ext_arr)){
	       	$result['code']=52;
	       	$result['status']=0;
	       	$result['msg']=C('error_code')[$result['code']];
	        return $result;
	    }
	    //充许上传文件大小，限制3M
	    $maxsize=1024*1024*3;
	    $filesize=filesize($_FILES[$field]['tmp_name']);
	    if($filesize>$maxsize){
	        $result['code']=51;
	        $result['status']=0;
	        $result['msg']=C('error_code')[$result['code']];
	        return $result;
	    }

		//尺寸要求
		$imginfo=getimagesize($_FILES[$field]['tmp_name']);
		if($width>0 && $height>0){
			if($imginfo[0]!=$width || $imginfo[1]!=$height) {
				$result['code'] 	= 550;	//图片尺寸不符合要求
				$result['status']	= 0;
				$result['msg']		= C('error_code')[$result['code']];
				return $result;
			}


		}

	    $res=$this->doApi('/Upload/upload2',array('openid'=>session('user.openid'),'content'=>file_get_contents($_FILES[$field]['tmp_name'])),'content,openid');

	    $result['code']		=$res->code;
	    $result['status']	=1;
	    $result['msg']		=$res->msg;
	    $result['url']		=$res->data->url;
		$result['imginfo']	=$imginfo;

	    return $result;
   
    }	

}