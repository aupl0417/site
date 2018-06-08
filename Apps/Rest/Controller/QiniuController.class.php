<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 七牛云存储
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class QiniuController extends RestController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    /*
    * 获取七牛配置
    */
    public function qiniu(){
        $cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
        $this->response($cfg['qiniu']);
    }



}