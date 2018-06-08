<?php
/**
 * -------------------------------------------------
 * 售后订单
 * -------------------------------------------------
 * Create by lizuheng <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-24
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;

class ServiceController extends CommonController {
	
    /**
     * 售后订单
     */
    public function index(){
		$this->check_logined();
		//C('DEBUG_API',true);
		$data['openid'] = session('user')['openid'];
		$data['s_no'] = I('get.s_no');
	    //全部售后
		$res = $this->doApi2('/Service/service_list',$data);
		$this->assign('pagelist',$res['data']);
	    //售后中
		$data['status'] = "1,2,3,4,5,6,10";
		$res = $this->doApi2('/Service/service_list',$data);
		$this->assign('service',$res['data']);
		//print_r($res['data']);
		$this->display();
    }
	
	/**
     * 售后订单分页
     * Create by Lazycat
     * 2017-02-21
     */
    public function service_page(){
		$this->ajax_check_logined();
        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['p']          = I('get.p');
		if($_GET['status'] !='' && $_GET['status']==1) $data['status'] = "1,2,3,4,5,6,10";
		$res = $this->doApi2('/Service/service_list',$data);
        $this->ajaxReturn($res);
    }
    /**
     * 售后详情
     */
    public function view(){
		$this->check_logined();
		//C('DEBUG_API',true);
		$data['r_no'] = I('get.r_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/Service/view',$data);
		//print_r($res);
		$this->assign('rs',$res['data']);
		$this->display();
    }
	
	/**
     * 取消售后
     */
    public function cancel(){
		$this->ajax_check_logined();
		// C('DEBUG_API',true);
		$data['r_no'] = I('post.r_no');
		$data['s_no'] = I('post.s_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/Service/cancel',$data);

		$this->ajaxReturn($res);
    }


	/**
     * 邮寄商品接口
     */
    public function send_express(){
		$this->ajax_check_logined();
		if(I('post.express_code')){
			$rs = "/^[a-zA-Z0-9\#]*$/";
			if (!preg_match($rs,I('post.express_code'))){
				$this->ajaxReturn(['code'=>0,'msg'=>"快递单号只能是数字加字母组合"]);
			}
		}
		//C('DEBUG_API',true);
		$res = $this->doApi2('/Service/buyer_send_express',I('post.'));
		$this->ajaxReturn($res);
    }
	/**
     * 邮寄商品页面
     */
    public function service_send_express(){
		$this->check_logined();
        $res = $this->doApi2('/Express/company',[]);
        $this->assign('company',$res['data']);

		$this->display();
    }

	/**
     * 收货地址列表
     * Create by Lazycat
     * 2017-02-16
     */
	/*
    public function select_address(){
        $this->check_logined();
        $res = $this->doApi2('/ShoppingAddress/address',['openid' => session('user.openid')]);
        $this->assign('pagelist',$res['data']);
        $this->display();
    }

    public function select_address_page(){
        $res = $this->doApi2('/ShoppingAddress/address',['openid' => session('user.openid'),'p' => I('get.p')]);
        $this->ajaxReturn($res);
    }
	*/
	
	/**
     * 买家收到商品，售后完成
     * Crate by lazycat
     * 2017-03-27
     */
    public function buyer_receive(){
		$this->ajax_check_logined();
		//C('DEBUG_API',true);
		$data['r_no']         = I('post.r_no');
		$data['openid']       = I('post.openid');
		$data['pay_password'] = $this->password(I('post.pay_password'));
		$res = $this->doApi2('/Service/buyer_receive',$data);
		$this->ajaxReturn($res);
    }
	
	/**
     * 申诉页面
     */
    public function service_appeal(){
		$this->check_logined();
		// C('DEBUG_API',true);
		$data['r_no'] = I('get.r_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/Service/view',$data);
		//print_r($res);
		$this->assign('data',$res['data']);
		$this->display();
    }
	
	/**
     * 申诉接口
     */
    public function appeal(){
		$this->ajax_check_logined();
		// C('DEBUG_API',true);
		$res = $this->doApi2('/Service/appeal',I('post.'));
		$this->ajaxReturn($res);
    }
	/**
     * 编辑
     */
    public function service_edit(){
		$this->check_logined();
		//C('DEBUG_API',true);
		$data['r_no'] = I('get.r_no');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/Service/service_goods_view',$data);

		//print_r($res);
		$this->assign('data',$res['data']);
		$this->display();
    }
	
	/**
     * 编辑接口
     */
    public function edit(){
		//C('DEBUG_API',true);
		$res = $this->doApi2('/Service/edit',I('post.'));
		$this->ajaxReturn($res);
    }
	
	/**
     * 申请售后
     */
    public function add(){
		//C('DEBUG_API',true);
		$data['orders_goods_id']    = I('get.orders_goods_id');
		$data['openid']             = session('user')['openid'];
		$res = $this->doApi2('/Service/service_goods',$data);

        if($res['code'] != 1){
            $this->assign('msg',$res['msg']);
            $this->display('error');
            exit();
        }

		//print_r($res);
		$this->assign('rs',$res['data']);
		$this->display();
    }
	
	/**
     * 申请售后接口
     */
    public function service_add(){
		//C('DEBUG_API',true);
		$res = $this->doApi2('/Service/add',I('post.'));
		$this->ajaxReturn($res);
    }
	
    /**
     * 协商详情
     * Create by lizuheng
     * 2017-03-12
     */
    public function logs(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Service/logs',['openid' => session('user.openid'),'r_no' => I('get.r_no')]);
        $this->assign('list',$res['data']);

        //print_r($res);
        $this->display();
    }

    /**
     * 添加留言
     * Create by lizuheng
     * 2017-03-12
     */
    public function logs_add(){
		$this->check_logined();
        $res = $this->doApi2('/Service/logs_add',I('post.'));
        $this->ajaxReturn($res);
    }



    /**
     * 某商品售后列表（多次售后产生的多笔记录）
     * Create by Lazycat
     * 2017-03-25
     */
    public function service_goods_list(){
        $this->check_logined();

        $data['openid'] = session('user.openid');
        if(I('get.orders_goods_id')) $data['orders_goods_id'] = I('get.orders_goods_id');
        if(I('get.s_no')) $data['s_no'] = I('get.s_no');
        $res = $this->doApi2('/Service/service_goods_list',$data);

        $this->assign('list',$res['data']);
        $this->display();
    }



}