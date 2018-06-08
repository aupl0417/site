<?php
namespace Wap\Controller;
class ServiceController extends CommonController {
    public function index(){
        $this->display();
    }
    
    public function upload(){
        $res = $this->_upload('imageData');
        $this->ajaxReturn($res);
    }
}