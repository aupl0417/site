<?php
/**
同步登录ERP系统-用户授权获取
*/
namespace Oauth2\Controller;
use Wap\Controller\CommonController;
class ErpController extends CommonController {
    //获取ERP 用户
    public function login(){
		$cache_name=trim(I('get.token'));
		$data=S($cache_name);
		if(empty($data)){
			//echo '授权失败！';
			redirect(urldecode(I('get.redirect_url')));
			exit;
		}

		//C('DEBUG_API',true);
		//$data['userid']='5cce33bdb9d7ecc5ec2ee7325a91a55d';

        $res = $this->doApi('/App/auth',['erp_uid' => $data['uid']],'',1);
        //dump($res);exit;

        if($res['code'] == 1){
            //S('app_auth_' . I('post.erp_uid'),['code' => 1,'atime' => date('Y-m-d H:i:s')]);     //记录APP授权状态
            session('user',$res['data']);

            redirect(urldecode(I('get.redirect_url')));
        }else{
            redirect(urldecode(I('get.redirect_url')));
        }

    }


}