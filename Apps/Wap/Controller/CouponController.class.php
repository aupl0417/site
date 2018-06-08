<?php
namespace Wap\Controller;
use Think\Controller;
class CouponController extends CommonController {
	public function _initialize() {
		parent::_initialize();

		//检查是否登录
		$this->check_logined();
	}

    public function index(){
    	$data['openid'] = session('user.openid');
    	$res=$this->doApi('/Coupon/my_coupon',$data);
    	$arr = $res->data->list;
    	$expire_list = array();
    	$unuse_list = array();
    	$used_list = array();
    	foreach ($arr as $key => $value) {// $status=array('已过期','未使用','已使用');3,1,2
    		if($value->status == 0)$expire_list[$key] = $value;
    		if($value->status == 1)$unuse_list[$key] = $value;
    		if($value->status == 2)$used_list[$key] = $value;
    	}
    	$this->assign('expire_list',$expire_list);
    	$this->assign('unuse_list',$unuse_list);
    	$this->assign('used_list',$used_list);
	    $this->display();
    }
    
    public function recom() {
        $width = M('coupon_recom_category')->cache(true)->where(['status' => 1])->count();
        $cates = M('coupon_recom_category')->cache(true)->where(['status' => 1])->order('sort asc,id asc')->getField('id,name', true);
        $first = M('coupon_recom_category')->where(['status' => 1])->order('sort asc, id asc')->getField('id');
        $this->assign('cates', $cates);
        $this->assign('count', $width);
        $this->assign('first', $first);
        $this->display();
    }
}
?>