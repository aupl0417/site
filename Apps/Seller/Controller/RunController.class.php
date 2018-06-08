<?php
namespace Seller\Controller;
class RunController extends AuthController{
    protected $_run;
    public function _initialize() {
        $this->_run =   A('Home/Run');
    }
    
    public function index() {
        $this->_run->index();
    }
    
    public function authRun() {
        $this->_run->authRun();
    }
    
    public function upload() {
        $this->_run->upload();
    }
}