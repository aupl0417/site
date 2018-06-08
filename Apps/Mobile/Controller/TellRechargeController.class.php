<?php
/**
 * -------------------------------------------------
 * 手机充值
 * -------------------------------------------------
 * Create by Lizuheng
 * -------------------------------------------------
 * 2017-05-10
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class TellRechargeController extends CommonController {
    public function index(){
		$this->check_logined();

		$res = $this->doApi2('/MobileRecharge/fare_list',[]);
		$this->assign('res',$res['data']);	
        //支付方式
        $result = $this->doApi2('/Cashier/paytype',['notid' => '2,7']);
		//print_r($res['data']);
        $this->assign('paytype',$result['data']);		
		$this->assign('mobile',$_SESSION['user']['mobile']);
		$this->assign('type',I('get.type')?I('get.type'):1);
		$this->display();
    }
	//话费、流量充值创建订单
    public function recharge(){
		$this->ajax_check_logined();
      // $this->ajaxReturn(['code'=>1,'data'=>array('s_no'=>1)]);
	    //C('DEBUG_API',true);
        $data['openid']    		= session('user.openid');
		$data['fare']      		= I('post.fare');
        $data['mobile']         = I('post.mobile');
        $data['type']           = I('post.type');
		$data['recharge_type']  = I('post.recharge_type');

        $res = $this->doApi2('/MobileRecharge/create_orders',$data);
		// print_r($res);
        $this->ajaxReturn($res);
    }
	
	/**
     * 收银台单订单支付
     * Create by lizuheng
     * 2017-05-11
     */
	public function single_pay(){
        if(empty($_SESSION['user'])){
            $res['msg'] = '请先登录！';
            goto error;
        }

        //C('DEBUG_API',true);
	    $res = $this->doApi2('/MobileRecharge/create_single_form',['openid' => session('user.openid'),'s_no' => I('get.s_no'),'paytype' => I('get.paytype'),'terminal' => I('get.terminal')]);
		//print_r($res);
        if($res['code'] == 1) {
            echo html_entity_decode($res['data']);
            exit();
        }

        error:
        $this->assign('msg',$res['msg']);
        $this->display('error');
    }

    /**
     * 订单支付状态
     * Create by lizuheng
     * 2017-05-11
     */
    public function check_orders_status(){
        $this->doApi2('/MobileRecharge/check_ordres_in_erp',['s_no' => I('post.s_no')]);    //检测订单是否有异常，如有进行修正
        $status = M('mobile_orders')->where(['s_no' => I('post.s_no')])->getField('status');
		
        if($status == 2) $this->ajaxReturn(['code' => 1,'msg' => '支付成功！']);
        else $this->ajaxReturn(['code' => 0,'msg' => '支付失败或未支付！']);
    }

    /**
     * 充值列表
     * Create by lizuheng
     * 2017-05-11
     */
     public function recharge_list(){
		$data['openid']    		= session('user.openid');
		//话费
		$fare = $this->doApi2('/MobileRecharge/orders',$data);
		//流量
		$data['recharge_type'] = 2;
		$flow = $this->doApi2('/MobileRecharge/orders',$data);
/* 		print_r($flow);
		print_r($fare); */
        $this->assign('flow',$flow['data']);
		$this->assign('fare',$fare['data']);
        $this->display();
    } 
	/**
     * 充值分页
     * Create by lizuheng
     * 2017-05-10
     */
    public function recharge_page(){
		$this->ajax_check_logined();
        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['p']          = I('get.p');
		$res = $this->doApi2('/MobileRecharge/orders',$data);
        $this->ajaxReturn($res);
    }
	/**
     * 充值详情
     * Create by lizuheng
     * 2017-05-12
     */
    public function view(){
		$this->check_logined();
		//支付方式
		$result = $this->doApi2('/Cashier/paytype',['notid' => '2,7']);
        $this->assign('paytype',$result['data']);
		
		//充值详情
		$data['s_no']   = I('get.s_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/view',$data);
		// print_r($res);
		$this->assign('res',$res['data']);
		$this->display();
    }
	
	/**
     * 关闭充值订单
     * Create by lizuheng
     * 2017-05-14
     */
    public function orders_close(){
		$this->ajax_check_logined();
		
		$data['s_no']   = I('post.s_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/orders_close',$data);
		// print_r($res);
		$this->ajaxReturn($res);
    }
	/**
     * 充值退款
     * Create by lizuheng
     * 2017-05-14
     */
    public function refund_add(){
		$this->ajax_check_logined();
		
		$data['s_no']   = I('post.s_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/refund_add',$data);
		// print_r($res);
		$this->ajaxReturn($res);
    }
	/**
     * 取消充值退款
     * Create by lizuheng
     * 2017-05-14
     */
    public function refund_cancel(){
		$this->ajax_check_logined();
		
		$data['r_no']   = I('post.r_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/refund_cancel',$data);
		// print_r($res);
		$this->ajaxReturn($res);
    }
	/**
     * 充值退款详情
     * Create by lizuheng
     * 2017-05-14
     */
    public function refund_view(){
		$this->check_logined();
		
		//充值退款详情
		$data['r_no']   = I('get.r_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/refund_view',$data);
		// print_r($res);
		$this->assign('res',$res['data']);
		$this->display();
    }
	/**
     * 付款结果页面
     * Create by lizuheng
     * 2017-05-15
     */
    public function pay_result(){
		$this->check_logined();
		$data['s_no']   = I('get.s_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/MobileRecharge/view',$data);
		if($res['data']['status'] == 2) $res['data']['msg'] = "支付成功";
        else $res['data']['msg'] = "支付失败或未支付";
		//print_r($res['data']);
		$this->assign('res',$res['data']);
		$this->display();
    }
}