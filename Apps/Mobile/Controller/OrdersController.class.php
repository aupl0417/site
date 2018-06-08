<?php
/**
 * -------------------------------------------------
 * 买家订单管理
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-20
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Common\Builder\R;
use Think\Controller;
use Common\Form\Form;
use Think\Exception;

class OrdersController extends CommonController {

    /**
     * 买家订单
     * Create by Lazycat
     * 2017-02-20
     */
    public function index(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['pagesize']   = 15;
        if(I('get.status')) $data['status']   = I('get.status');
        $res = $this->doApi2('/BuyerOrders/orders',$data);
        $this->assign('pagelist',$res['data']);
        //print_r($res);

        //$account = $this->doApi2('/Erp/account',['erp_uid' => session('user.erp_uid')]);
        //$this->assign('account',$account['data']);

        //支付方式
//        $res = $this->doApi2('/Cashier/paytype',['notid' => '7,8']);
//        $this->assign('paytype',$res['data']);
		
        $this->display();
    }

    /**
     * 买家订单分页
     * Create by Lazycat
     * 2017-02-21
     */
    public function orders_page(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['pagesize']   = 15;
        $data['p']          = I('get.p');
        if(I('get.status')) $data['status']   = I('get.status');
        $res = $this->doApi2('/BuyerOrders/orders',$data);

        $this->ajaxReturn($res);
    }


    /**
     * 关闭订单
     * Create by Lazycat
     * 2017-02-20
     */
    public function close(){
        $this->check_logined();

        $this->display();
    }

    public function close_save(){
        $this->ajax_check_logined();

        $res = $this->doApi2('/BuyerOrders/close',I('post.'));

        $this->ajaxReturn($res);
    }




    /**
     * 订单详情
     * Create by Lazycat
     * 2017-02-20
     */
    public function view(){
        $this->check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerOrders/view',['openid' => session('user.openid'),'s_no' => I('get.s_no'),'is_love' => 1]);
        $this->assign('rs',$res['data']);
        //print_r($res);

        if($res['data']['status'] == 1){
//            $account = $this->doApi2('/Erp/account',['erp_uid' => session('user.erp_uid')]);
//            $this->assign('account',$account['data']);
//
//            //支付方式
//            $res = $this->doApi2('/Cashier/paytype',['notid' => '7,8']);
//            $this->assign('paytype',$res['data']);
        }
		

        $this->display();
    }


    /**
     * 订单支付
     * Create by Lazycat
     * 2017-02-21
     */
    public function orders_pay(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        if(!empty($_POST['pay_password'])) $_POST['pay_password'] = $this->password(I('post.pay_password'));

        //print_r(I('post.'));
        $res = $this->doApi2('/Erp/orders_pay',I('post.'));

        $this->ajaxReturn($res);
    }

    /**
     * subject: 单订单支付 - 乐兑
     * api: ordersPay
     * author: Mercury
     * day: 2017-06-22 15:17
     * [字段名,类型,是否必传,说明]
     */
    public function ordersPay()
    {
        $this->ajax_check_logined();
        try {
            if(empty($_SESSION['user'])) throw new Exception('请登录');
            $res = $this->doApi2('/Cashier/orders_pay',['openid' => session('user.openid'),'s_no' => I('get.s_no')]);
            if ($res['code'] != 1) throw new Exception($res['msg']);
            echo html_entity_decode($res['data']);
        } catch (Exception $e) {
            $this->assign('msg',$e->getMessage());
            $this->display('error');
        }
    }
    
    /**
     * 查询物流信息
     * Create by Lazycat
     * 2017-02-21
     */
    public function logistics_view(){
        $this->check_logined();

        //$res = $this->doApi2('/BuyerOrders/logistics_info',['openid' => session('user.openid'),'s_no' => I('get.s_no')]);
		//阿里云物流接口
		$res = $this->doApi2('/BuyerOrders/logistics_info_aliyun',['openid' => session('user.openid'),'s_no' => I('get.s_no')]);
        $this->assign('rs',$res['data']);
        //print_r($res);

        $this->display();
    }


    /**
     * 订单评价
     * Create by Lazycat
     * 2017-02-21
     */
    public function rate(){
        $this->check_logined();

        $res = $this->doApi2('/BuyerOrders/wait_rate_goods',['openid' => session('user.openid'),'s_no' => I('get.s_no')]);
        $this->assign('list',$res['data']);
        //print_r($res);

        $this->display();
    }

    /**
     * 保存评价
     * Create by Lazycat
     * 2017-02-23
     */
    public function rate_save(){
        $this->ajax_check_logined();

        $data   = [
            'openid'            => session('user.openid'),
            'fraction_service'  => I('post.fraction_service'),
            'fraction_speed'    => I('post.fraction_speed'),
            'is_anonymous'      => $_POST['is_anonymous'] ? 1 : 0,
            's_no'              => I('post.s_no'),
        ];

        $tmp = [];
        //数据格式化
        if(is_array($_POST['orders_goods_id'])){
            foreach($_POST['orders_goods_id'] as $key => $val){
                $tmp[$key] = [
                    'orders_goods_id'   => $val,
                    'fraction_desc'     => I('post.fraction_desc')[$key],
                    'content'           => I('post.content')[$key],
                    'images'            => I('post.images')[$key]
                ];
            }
        }else{
            $tmp[0] = [
                'orders_goods_id'   => I('post.orders_goods_id'),
                'fraction_desc'     => I('post.fraction_desc'),
                'content'           => I('post.content'),
                'images'            => I('post.images')
            ];
        }
        //print_r($data);
        $data['goods_rate']   = serialize($tmp);
        //$data['goods_rate']   = json_encode($tmp);
        //print_r($data);
        //C('DEBUG_API',true);
        $res = $this->doApi2('/BuyerOrders/rate_save',$data);

        $this->ajaxReturn($res);
    }


    /**
     * 确认收货
     * Create by Lazycat
     * 2017-02-23
     */
    public function receive(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        //$_POST['pay_password'] = $this->password(I('post.pay_password'));
        //$res = $this->doApi2('/Erp/orders_confirm',I('post.'));
        //$res = R::getInstance()->auth();
        $res = $this->doApi2('/orders/receive', ['openid' => session('user.openid'), 's_no' => I('post.s_no')]);
        $this->ajaxReturn($res);
    }


	/**
     * 收银台单订单支付
     * Create by lazycat
     * 2017-04-06
     */
	public function single_pay(){
        if(empty($_SESSION['user'])){
            $res['msg'] = '请先登录！';
            goto error;
        }

        //C('DEBUG_API',true);
	    $res = $this->doApi2('/Cashier/create_single_form',['openid' => session('user.openid'),'s_no' => I('get.s_no'),'paytype' => I('get.paytype')]);
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
     * Create by lazycat
     * 2017-04-06
     */
    public function check_orders_status(){
        //检测erp订单状态
        try {
            if (M('orders_shop')->where(['s_no' => I('post.s_no')])->getField('status') != 2) {
                $res = $this->doApi2('/erp/checkErpOrderStatus', ['s_no' => I('post.s_no')]);
                if ($res['code'] != 1) throw new Exception('支付失败或未支付');
            }
            $returnData = ['code' => 1, 'msg' => '支付成功'];
            log_add('erp_pays_success_'.date('Ym'), array('atime' => date('Y-m-d H:i:s'),'type' => 'check_orders_status','res' => $returnData,'post' => var_export(I('post.s_no'),true)));  //写入日志
        } catch (Exception $e) {
            $returnData = ['code' => 0, 'msg' => $e->getMessage()];
        }
        $this->ajaxReturn($returnData);
    }

    /**
     * 支付结果提示
     * Create by lazycat
     * 2017-04-06
     */
    public function pay_result(){
        $status = M('orders_shop')->where(['s_no' => I('get.s_no')])->getField('status');

        if($status == 2){
            $res['code']    = 1;
            $res['msg']     = '支付成功！';
        }else{
            $res['code']    = 0;
            $res['msg']     = '支付失败！';
        }

        $this->assign('res',$res);

        $this->display();
    }
}