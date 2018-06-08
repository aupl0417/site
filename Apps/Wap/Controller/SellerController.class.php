<?php
namespace Wap\Controller;
use Think\Controller;
class SellerController extends CommonController {
    public function index(){		
		$this->display();
    }


    public function upload_save(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);
    }    
}