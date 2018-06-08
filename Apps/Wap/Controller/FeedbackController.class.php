<?php
namespace Wap\Controller;
use Think\Controller;
class FeedbackController extends CommonController {
    public function index(){
    	//dump($_SERVER);
    	$this->display();
    }

    public function upload(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);   
    }
    
}