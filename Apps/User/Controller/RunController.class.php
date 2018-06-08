<?php
namespace User\Controller;
class RunController extends AuthController{
    protected $_run;
    public function _initialize() {
        $this->_run =   A('Home/Run');
    }
    
    public function index() {
        $this->_run->index();
    }
}