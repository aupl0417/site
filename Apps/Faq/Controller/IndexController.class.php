<?php
/**
// +----------------------------------------------------------------------
// | tangrenjie [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 乐兑  All rights reserved.
// +----------------------------------------------------------------------
// | Author: 区锡钊 <ouxizhao@foxmail.com>
// +----------------------------------------------------------------------
// | Create Time: 2016/7/5 
// +----------------------------------------------------------------------
 */
namespace Faq\Controller;
use Home\Controller\CommonController;
class IndexController extends CommonController {

	//帮助中心主页面
    public function index() {
    	if(I('get.cid')){
	    	$param['cid'] = I('get.cid');
	    	$unsign ='cid';
    	}
    	if(I('get.q')){
    		$param['q'] = I('get.q');
    		$unsign = !empty($unsign)?$unsign.',q':'q';
    	}
		$this->assign('param',$param);
		$param['pagesize']=10;
        $this->api('/help/help_list',$param,$unsign)->with();
		
        C('seo' , ['title' => '帮助中心']);

        $this->api('/Help/category_buyer')->with('category_buyer');
		$this->api('/Help/category_seller')->with('category_seller');
        $this->display();
    }
}