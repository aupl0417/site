<?php
namespace Seller\Controller;
class ShipController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        C('seo', ['title' => '发货管理']);
        $this->display();
    }
}