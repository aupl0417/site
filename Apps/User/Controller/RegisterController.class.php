<?php
namespace User\Controller;
use Common\Builder\R;
class RegisterController extends AuthController {
    public function _initialize() {
        parent::_initialize();
        //$this->redirect('/login');
    }
    /*
    public function index() {
        $this->api('/Erp/company_type')->with('type');
        C('seo', ['title' => '用户注册']);
        if (C('cfg.site')['is_register'] == 0) {    //如果系统不让注册的话，则显示提示
            $this->assign('is_register_tips', C('cfg.site')['is_register_tips']);
            $this->display('is_register');
            exit;
        }
        $this->display();
    }
    */
    public function checkMobile() {
        //echo 'true';exit();
        $mobile =   I('get.mobile');
        $this->api('/Erp/check_mobile', ['mobile' => $mobile]);
        if ($this->_data['code'] == '0') {
            echo 'false';
        } else {
            $res = M('user')->where(['mobile'=>$mobile])->find();
            if($res){
                echo 'false';
            }else{
                echo 'true';
            }
        }
    }
    /**
     * 发送验证码
     * Create by liangfeng
     * 2017-09-19
     */
    public function ajax_send_msg(){
            //dump(I('post.'));
        $data['mobile'] = I('post.mobile');
        $res = R::getInstance(['url' => ['smscode' => '/Erp/smscode'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        $this->ajaxReturn($res['smscode']);
    }
    /**
     * 供货商用户注册页面
     * Create by liangfeng
     * 2017-09-05
     */
    public function checkUsername() {
        $username =   I('get.username');
        $this->api('/Erp/check_mobile', ['mobile' => $username]);

        if ($this->_data['code'] == '0') {
            echo 'false';
        } else {
            $res = M('user')->where(['nick'=>$username])->find();
            if($res){
                echo 'false';
            }else{
                echo 'true';
            }
        }
    }

    /**
     * 供货商用户注册页面
     * Create by liangfeng
     * 2017-09-05
     */
    public function supplier() {
        $this->api('/Erp/company_type')->with('type');
        C('seo', ['title' => '用户注册']);
        if (C('cfg.site')['is_register'] == 0) {    //如果系统不让注册的话，则显示提示
            $this->assign('is_register_tips', C('cfg.site')['is_register_tips']);
            $this->display('is_register');
            exit;
        }
        $this->display();
    }

    /**
     * 供货商用户注册
     * Create by liangfeng
     * 2017-09-05
     */
    public function supplier_register(){
        $verify = new \Think\Verify;
        if (false == $verify->check(I('post.vcode'))) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '图形验证码错误'));
        }
        if(I('post.password') != I('post.password2')){
            $this->ajaxReturn(array('code' => 0, 'msg' => '两次密码输入不一致'));
        }

        //http://api.ledttest.com/apidoc/api/#api-mallapi-smsCheck_json
        $data['mobile'] = I('post.mobile');
        $data['smsCode'] = I('post.smscode');
        $res = R::getInstance(['url' => ['check_smscode' => '/Erp/check_smscode'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        if($res['check_smscode']['code'] != 1){
            $this->ajaxReturn($res['check_smscode']);
        }else{
            unset($data['smsCode']);
        }
        $data['username'] = I('post.username');
        $data['password'] = $this->password(I('post.password'));

        //C('DEBUG_API',true);
        $res = R::getInstance(['url' => ['register' => '/Supplier/register'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        $res = $res['register'];
        $this->ajaxReturn($res);
    }
}