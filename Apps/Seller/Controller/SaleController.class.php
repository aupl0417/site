<?php
namespace Seller\Controller;
class SaleController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        C('seo', ['title' => '营销管理']);
        $this->display();
    }
}