<?php
/**
 * -------------------------------------------------
 * 图片上传
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-21
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class UploadController extends CommonController {

    /**
     * 图片上传
     * Create by Lazycat
     * 2017-02-22
     */
    public function upload(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Upload/upload_base64',['openid' => session('user.openid'),'filebody' => I('post.base64_string')],'filebody');
        $this->ajaxReturn($res);
    }

}