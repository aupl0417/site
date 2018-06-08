<?php
namespace Cart\Controller;
class SuccessController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        C('seo' ,['title' => '支付成功']);
        $this->display();
    }

    public function error() {
        C('seo' ,['title' => '支付失败']);
        $this->display();
    }  
}