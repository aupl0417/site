<?php
namespace Admin\Controller;
use Think\Controller;
class UploadController extends CommonModulesController {

    public function upload_save(){
        $res=$this->_upload('imageData',I('post.width'),I('post.height'));

        $res['field']=I('post.field');
        $this->ajaxReturn($res);
    }

}