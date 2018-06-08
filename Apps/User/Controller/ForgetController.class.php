<?php
namespace User\Controller;
class ForgetController extends AuthController {
    public function _initialize() {
        parent::_initialize();
        $this->redirect('/login');
    }
    
    /**
     * 忘记密码
     */
    public function index() {
        C('seo', ['title' => '忘记密码']);
        $this->display();
    }
    
    /**
     * 忘记密码第二步
     */
    public function step2() {
        $mobile =   deCryptRestUri(I('get.mobile'));
        $data   =   S('forgetPass_' . $mobile);
        C('seo', ['title' => '设置新密码']);
        if ($data) {
            $this->assign('data', $data);
            $this->display();
        } else {
            $this->redirect('/forget');
        }
    }
}