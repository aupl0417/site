<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 多台Web文件同步功能，由于WEB为多台存储，动态生成的文件无法同步，只能以此方法解决
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-10-15
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SyncController extends CommonController {
    public function _initialize() {
        parent::_initialize();

        $action = ACTION_NAME;
        if(!in_array('_'.$action,get_class_methods($this))) $this->apiReturn(1501);  //请求的方法不存在

        $this->_api(['method' => $action]);
    }


    /**
     * 各方法的必签字段
     * @param string $method     方法
     */
    public function _sign_field($method){
        $sign_field = [
            '_sync_file'              => 'content,save_path',    //必填字段
            '_sync_file2'             => 'content,save_path',    //必填字段
            '_sync_file3'             => 'content,save_path',    //必填字段
        ];

        $result=$sign_field[$method];
        return $result;
    }
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
     * 保存文件
     * @param string|stream $_POST['content'] 内容
     * @param string $_POST['save_path']    文件保存路径
     */
    public function _sync_file(){
        $res = file_put_contents(I('post.save_path'),html_entity_decode(I('post.content')));

        if($res) return ['code' => 1];
        else return ['code' => 0];

    }

    public function _sync_file2(){
        $res = file_put_contents(I('post.save_path'),html_entity_decode(I('post.content')));

        if($res) return ['code' => 1];
        else return ['code' => 0];

    }

    public function _sync_file3(){
        $res = file_put_contents(I('post.save_path'),html_entity_decode(I('post.content')));

        if($res) return ['code' => 1];
        else return ['code' => 0];

    }


}