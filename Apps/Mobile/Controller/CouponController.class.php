<?php
/**
 * -------------------------------------------------
 * 优惠券
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-03-13
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class CouponController extends CommonController {

    /**
     * 我的优惠券
     * Create by Lazycat
     * 2017-03-14
     */
    public function my_coupon(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Coupon/my_coupon',['openid' => session('user.openid'),'is_use' => 1]);
        $this->assign('list',$res['data']);

        //print_r($res);

        $res = $this->doApi2('/Coupon/my_coupon',['openid' => session('user.openid'),'is_expire' => 1]);
        $this->assign('elist',$res['data']);

        //print_r($res);
        $this->display();
    }

    /**
     * 我的优惠券分页
     * Create by Lazycat
     * 2017-03-14
     */
    public function my_coupon_page(){
        $this->ajax_check_logined();
        //C('DEBUG_API',true);
        $data['openid'] = session('user.openid');
        if(I('get.is_use'))     $data['is_use']      = 1;
        if(I('get.is_expire'))  $data['is_expire']   = 1;
        if(I('get.p'))          $data['p']           = I('get.p');
        $res = $this->doApi2('/Coupon/my_coupon',$data);

        $this->ajaxReturn($res);
    }

    /**
     * 领取优惠券
     * Create by Lazycat
     * 2017-03-13
     */
    public function get_coupon(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
	    $res = $this->doApi2('/Coupon/get_coupon',I('post.'));
		$this->ajaxReturn($res);
    }

    public function user_coupon(){
        C('DEBUG_API',true);
        $res = $this->doApi2('/Coupon/user_coupon',['openid' => session('user.openid'),'min_price' => 200,'shop_id' =>376]);

        print_r($res);
    }


    /**
     * 优惠详情
     * Create by lazycat
     * 2017-04-22
     */
    public function view(){
        $this->check_logined();
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Coupon/view',['openid' => session('user.openid'),'id' => I('get.id')]);
        //print_r($res);
        $this->assign('rs',$res['data']);
        $this->display();
    }

}