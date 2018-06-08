<?php
/**
 * 同步登录ERP系统-用户授权获取
 * Create by lazycat
 * 2017-04-05
 */
namespace Oauth2\Controller;
use Mobile\Controller\CommonController;
class AppV2Controller extends CommonController {
    protected $api_cfg 	=array(); 	//API接口配置

    /**
     * 授权登录
     * Create by lazycat
     * 2017-04-05
     */
    public function login(){
        $cache_name = I('get.token');
        $cache = S($cache_name);
log_add('api_debug', ['token' => $cache_name, 'data' => $cache, 'time' => date('Y-m-d H:i:s')]);
        if(empty($cache_name)) {
            redirect(C('sub_domain.m'));
            exit();
        }

        //$res  = S('app_auth_' . $cache['erp_uid']);
        //if(empty($res)) {
            $res = $this->doApi2('/App/auth', ['erp_uid' => $cache['erp_uid']]);
        //}

        if($res['code'] == 1){
            $this->_logout();   //清除旧的登录状态
            session('user',$res['data']);

            if(I('get.redirect_url')) redirect(I('get.redirect_url'));
            else redirect(C('sub_domain.m'));
        }else{
            redirect(C('sub_domain.m'));
        }

    }

    /**
     * 登出
     */
    public function _logout(){
        session('user',null);
        cookie('remember',null);
        cookie('wap_status',null);
    }


}