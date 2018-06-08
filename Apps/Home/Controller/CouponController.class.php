<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2016/12/16
 * Time: 10:35
 */

namespace Home\Controller;


class CouponController extends CommonController
{
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $cate = M('goods_category')->cache(true)->where(['status' => 1, 'sid' => 0, 'id' => ['neq', 100845542]])->limit(11)->field('sub_name,icon,id')->order('sort asc')->select();
        $data = [];
        if (isset($_GET['sort']) && !empty(I('get.sort'))) $data['sort'] = I('get.sort');
        if (isset($_GET['self']) && !empty(I('get.self'))) $data['self'] = I('get.self');
        if (isset($_GET['cat']) && !empty(I('get.cat'))) $data['cat'] = I('get.cat');
        if (isset($_GET['shop']) && !empty(I('get.shop'))) $data['shop'] = I('get.shop');
        $this->api('/Coupon/lists', $data, 'sort,self,cat,shop')->with();
		
		//echo $this->_data['data']['sql'];
		//dump($this->_data['data']);
        $this->assign('cate', $cate);
        C('seo', ['title' => '优惠券']);
        $this->display();
    }

    public function post() {
        writeLog('post');
        writeLog($_POST);
    }
}