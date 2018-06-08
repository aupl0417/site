<?php
/**
同步登录ERP系统-用户授权获取
*/
namespace Oauth2\Controller;
use Wap\Controller\CommonController;
class AppController extends CommonController {
    protected $api_cfg 	=array(); 	//API接口配置

    /**
     * 授权接口
     * 考虑到安全性问题，此方法只适合WEB方式的自动授权
     */
    public function auth(){
        $data = [
            'appid'         => I('post.appid'),
            'access_key'    => I('post.access_key'),
            'secret_key'    => I('post.secret_key'),
            'sign_code'     => I('post.sign_code'),
            'erp_uid'       => I('post.erp_uid'),
        ];

        //测试数据
        /*
        $data = [
            'appid'         => 6,
            'access_key'    => '0546734d7b599e802e9e2f3d701de851',
            'secret_key'    => 'b850ee6e7546377dad23cb87885c2711',
            'sign_code'     => '5e64fe04bfd8363b6c74ea86f5c867f1',
            'erp_uid'       => 'f0c4b2eaa072434420b8b53c7cc88f87',
        ];
        */


        foreach($data as $key => $val){
            if(empty($val)) $this->ajaxReturn(['code' => 0,'msg' => '缺少'.$key.'参数！']);
        }
        $this->api_cfg = $data;

        //是否已登录状态
        if(!empty($_SESSION['user']) && session('user.erp_uid') == I('post.erp_uid')){
            if(I('post.redirect_url')) redirect(I('post.redirect_url'));
            else redirect(C('sub_domain.m'));
            exit();
        }


        //未登录，进行授权验证
        //C('DEBUG_API');
        $res = $this->doApi('/App/auth',$data,'',1);
        //dump($res);exit;

        if($res['code'] == 1){
            //S('app_auth_' . I('post.erp_uid'),['code' => 1,'atime' => date('Y-m-d H:i:s')]);     //记录APP授权状态
            session('user',$res['data']);

            if(I('post.redirect_url')) redirect(I('post.redirect_url'));
            else redirect(C('sub_domain.m'));
        }else{
            redirect(C('sub_domain.m'));
        }
    }


    /**
     * token
     * App登录授权生成token URL
     */
    public function token(){
        $data = [
            'appid'         => I('post.appid'),
            'access_key'    => I('post.access_key'),
            'secret_key'    => I('post.secret_key'),
            'sign_code'     => I('post.sign_code'),
            'erp_uid'       => I('post.erp_uid'),
            'sign'          => I('post.sign'),
        ];
        /*
        $data = [
            'appid'         => 6,
            'access_key'    => '0546734d7b599e802e9e2f3d701de851',
            'secret_key'    => 'b850ee6e7546377dad23cb87885c2711',
            'sign_code'     => '5e64fe04bfd8363b6c74ea86f5c867f1',
            'erp_uid'       => 'f0c4b2eaa072434420b8b53c7cc88f87',
            'redirect_url'  => 'https://www.trj.cc',
        ];
        */


        foreach($data as $key => $val){
            if(empty($val)) $this->ajaxReturn(['code' => 0,'msg' => '缺少'.$key.'参数！']);
        }
        $this->api_cfg = $data;
        $res = $this->doApi('/App/token',I('post.'),'redirect_url,sign');
        $this->ajaxReturn($res);
    }

    /**
     * 授权登录
     */
    public function login(){
        $cache_name = I('get.token');
        $cache = S($cache_name);

        if(empty($cache_name)) {
            redirect(C('sub_domain.m'));
            exit();
        }

        $res = $this->doApi('/App/auth',['erp_uid' => $cache['erp_uid']],'',1);
        //dump($res);exit;

        if($res['code'] == 1){
            //S('app_auth_' . I('post.erp_uid'),['code' => 1,'atime' => date('Y-m-d H:i:s')]);     //记录APP授权状态
            session('user',$res['data']);

            if(I('get.redirect_url')) redirect(I('get.redirect_url'));
            else redirect(C('sub_domain.m'));
        }else{
            redirect(C('sub_domain.m'));
        }

    }


}