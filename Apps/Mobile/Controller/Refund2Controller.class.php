<?php
/**
 * -------------------------------------------------
 * 买家已发货退款
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-28
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class Refund2Controller extends CommonController {
    /**
     * 已发货的商品申请退款
     * Create by Lazycat
     * 2017-02-27
     */
    public function refund_goods(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund2/refund_goods',['openid' => session('user.openid'),'orders_goods_id' => I('get.orders_goods_id')]);
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
     * 已发货商品退款
     * Create by Lazycat
     * 2017-02-28
     */

    public function add(){
        $this->ajax_check_logined();

        //print_r(I('post.'));
        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund2/add',I('post.'));

        $this->ajaxReturn($res);
    }

    /**
     * 某商品退款列表（多次退款产生的多笔记录）
     * Create by Lazycat
     * 2017-02-27
     */
    public function refund_goods_list(){
        $this->check_logined();

        $res = $this->doApi2('/BuyerRefund2/refund_goods_list',['openid' => session('user.openid'),'orders_goods_id' => I('get.orders_goods_id')]);

        $this->assign('list',$res['data']);
        $this->display();
    }


    /**
     * 取消退款
     * Create by Lazycat
     * 2017-02-27
     */
    public function cancel(){
        $this->ajax_check_logined();

        $res = $this->doApi2('/BuyerRefund2/cancel',['openid' => session('user.openid'),'r_no' => I('post.r_no')]);
        $this->ajaxReturn($res);
    }

    /**
     * 寄回商品填写物流界面
     * Create by Lazycat
     * 2017-03-01
     */
    public function express_goods(){
        $this->check_logined();

        //快递公司
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Express/company',[]);
        $this->assign('company',$res['data']);
        //print_r($res);

        $this->display();
    }

    /**
     * 寄回商品
     * Create by Lazycat
     * 2017-03-01
     */
    public function send_express(){
        $this->ajax_check_logined();
		if(I('post.express_code')){
			$rs = "/^[a-zA-Z0-9\#]*$/";
			if (!preg_match($rs,I('post.express_code'))){
				$this->ajaxReturn(['code'=>0,'msg'=>"快递单号只能是数字加字母组合"]);
			}
		}
        $res = $this->doApi2('/BuyerRefund2/send_express',I('post.'));

        $this->ajaxReturn($res);
    }


    /**
     * 修改退款页面
     * Create by Lazycat
     * 2017-03-01
     */
    public function refund_edit(){
        $this->check_logined();
        //C('DEBUG_API',true);

        $res = $this->doApi2('/BuyerRefund2/refund_goods_view',['openid' => session('user.openid'),'r_no' => I('get.r_no')]);
        $this->assign('rs',$res['data']);
        //print_r($res);

        $this->display();
    }


    /**
     * 修改退款
     * Create by Lazycat
     * 2017-03-01
     */
    public function edit(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund2/edit',I('post.'));


        $this->ajaxReturn($res);
    }
	
	/**
     * 申诉页面
     * Create by 梁丰
     * 2017-03-03
     */
	public function refund_appeal(){
		$this->ajax_check_logined();
		//C('DEBUG_API',true);
		$res = $this->doApi2('/BuyerRefund/view',['openid' => session('user.openid'),'r_no' => I('get.r_no')]);
        $this->assign('rs',$res['data']);
        //$this->ajaxReturn($res);
		$this->display();
	}
	/**
     * 提交申诉
     * Create by 梁丰
     * 2017-03-03
     */
	public function appeal(){
		$this->ajax_check_logined();
		//C('DEBUG_API',true);
		$res = $this->doApi2('/Appeal/refund_appeal',I('post.'));
		$this->ajaxReturn($res);
	}

}