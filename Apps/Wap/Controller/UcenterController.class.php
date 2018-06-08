<?php
namespace Wap\Controller;
use Think\Controller;
class UcenterController extends CommonController {
    public function index(){		
		$this->display();
    }
	

    /**
    * 二维码
    */
    public function qrcode_(){
    	$data['openid']		=session('user.openid');
    	$data['text']		=C('sub_domain.wap').'/Index/index?url=/Login/register/ref/'.session('user.id');
    	//$data['is_logo']	=1;
    	$data['size']		=6;
    	$res=$this->doApi('/User/qrcode',$data,'is_logo,size,errorlevel');
    	$this->ajaxReturn($res);
    }

}