<?php
/**
 * -------------------------------------------------
 * 买家退款记录
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-24
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class RefundController extends CommonController {

    /**
     * 退款列表
     * Create by Lazycat
     * 2017-02-24
     */
    public function index(){
        $this->check_logined();
		//C('DEBUG_API',true);

        $data['openid'] = session('user.openid');
        $data['status'] = '1,2,3,4,5,6,10';
        if(I('get.s_no')) $data['s_no'] = I('get.s_no');
        $res = $this->doApi2('/BuyerRefund/refund_list',$data);
        $this->assign('pagelist',$res['data']);
        //print_r($res);

        $res = $this->doApi2('/BuyerRefund/refund_list',['openid' => session('user.openid')]);
        $this->assign('alist',$res['data']);


        $this->display();
    }

    /**
     * 退款列表分页读取
     * Create by Lazycat
     * 2017-02-24
     */
    public function refund_page(){
        $this->ajax_check_logined();
        //C('DEBUG_API',true);

        $data['openid'] = session('user.openid');
        if($_GET['status'] !='') $data['status'] = I('get.status');
        if($_GET['p'])  $data['p']  = I('get.p');
        $res = $this->doApi2('/BuyerRefund/refund_list',$data);

        $this->ajaxReturn($res);
    }


    /**
     * 退款详情
     * Create by Lazycat
     * 2017-02-24
     */
    public function view(){
        $this->check_logined();
        //C('DEBUG_API',true);

        $res = $this->doApi2('/BuyerRefund/view',['openid' => session('user.openid'),'r_no' => I('get.r_no')]);
        $this->assign('rs',$res['data']);
        //print_r($res);

        $this->display();
    }


    /**
     * 未发货的商品申请退款
     * Create by Lazycat
     * 2017-02-27
     */
    public function refund_goods(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund/refund_goods',['openid' => session('user.openid'),'orders_goods_id' => I('get.orders_goods_id')]);
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
     * 未发货商品退款
     * Create by Lazycat
     * 2017-02-25
     */

    public function add(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund/add',I('post.'));

        $this->ajaxReturn($res);
    }

    /**
     * 某商品退款列表（多次退款产生的多笔记录）
     * Create by Lazycat
     * 2017-02-27
     */
    public function refund_goods_list(){
        $this->check_logined();

        $data['openid'] = session('user.openid');
        if(I('get.orders_goods_id')) $data['orders_goods_id'] = I('get.orders_goods_id');
        if(I('get.s_no')) $data['s_no'] = I('get.s_no');
        $res = $this->doApi2('/BuyerRefund/refund_goods_list',$data);

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

        $res = $this->doApi2('/BuyerRefund/cancel',['openid' => session('user.openid'),'r_no' => I('post.r_no')]);
        $this->ajaxReturn($res);
    }

    /**
     * 协商详情
     * Create by Lazycat
     * 2017-03-10
     */
    public function logs(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerRefund/logs',['openid' => session('user.openid'),'r_no' => I('get.r_no')]);
        $this->assign('list',$res['data']);

        //print_r($res);
        $this->display();
    }

    /**
     * 添加留言
     * Create by Lazycat
     * 2017-03-10
     */
    public function logs_add(){
        $this->ajax_check_logined();
        $res = $this->doApi2('/BuyerRefund/logs_add',I('post.'));
        $this->ajaxReturn($res);
    }
}