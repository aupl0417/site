<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 频道装修-发布后
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-11-28
|----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class CustomPublishController extends CommonController {
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
            '_page_layout'                      => 'page,domain',   //取页面布局
            '_layout'                           => 'domain,page', //装修页面布局

        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
     * 取页面布局
     * @param string $_POST['page']    页面
     * @param int    $_POST['domain']  频道域名前缀
     */
    public function _page_layout(){

        $rs['layout']   =M('custom_publish_layout')->cache(true)->where(['page' => I('post.page'),'domain' => I('post.domain')])->field('atime,etime,ip',true)->order('sort asc')->select();

        if($rs) return ['code' => 1,'data' =>$rs];

        return ['code' => 3];
    }

    /**
     * 取频道页面布局
     * @param int    $_POST['domain'] 频道域名前缀
     * @param string $_POST['page']  装修页面,如:/Index/index
     */
    public function _layout(){

        $do=D('Common/CustomPublishLayoutModulesRelation');
        $list=$do->relation(true)->where(['domain' => I('post.domain'),'page' => I('post.page')])->field('atime,etime,ip',true)->order('sort asc')->select();

        foreach($list as $i => $val){
            foreach($val['modules'] as $j => $v){
                $list[$i]['item'][$v['col_index']] .= '<div class="col-sort" data-id="'.$v['id'].'">'.$this->_modules_item_view($v['id']).'</div>';
            }
            unset($list[$i]['modules']);
        }


        if($list) return ['code' =>1,'data' => $list];

        return ['code' =>3];
    }

    /**
     * 输出模块 ,$id和$data两项必传一项
     * @param int $id        模块id
     * @param array $data    模块数据
     */
    public function _modules_item_view($id='',$data=''){
        $modules = M('custom_publish_modules')->cache(true)->where(['id' => $id])->field('atime,etime,ip',true)->find();
        $tpl = './Templates/zh_cn/Channel'.$modules['mod_url'];

        $modules['data'] = eval(html_entity_decode($modules['data']));
        $this->assign('rs',$modules);

        $html = $this->fetch($tpl);

        return $html;
    }

}