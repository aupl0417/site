<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class UploadController extends InitController {

    public function upload_save(){
		$this->ajax_check_login();
        $res=$this->_upload('imageData',I('post.width'),I('post.height'));

        $res['field']=I('post.field');
        $this->ajaxReturn($res);
    }

}