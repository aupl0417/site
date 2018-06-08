<?php
/**
 * -------------------------------------------------
 * 购物车
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-21
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
use Common\Form\Form;
use Think\Exception;

class CartController extends CommonController {
    /**
     * 购物车商品列表
     * Create by Lazycat
     * 2017-02-15
     */
    public function index(){
        redirect(DM('m'));  //暂时没有购物车这个页面
        $this->check_logined();
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/goods',['openid' => session('user.openid')]);

        $this->assign('list',$res['data']);
        //print_r($res);

        $this->display();
    }

    /**
     * 加入购物车
     * Create by Lazycat
     * 2017-02-15
     */
    public function add(){
        $this->ajax_check_logined();
        //C('DEBUG_API',true);
		$data['attr_list_id']   = I('post.attr_list_id');
		$data['num']            = I('post.num');
		$data['openid']         = session('user.openid');
		$data['type']           = 1;
		if(I('post.atonce')) {  //立即购买
            $data['atonce']     = I('post.atonce');
		    $data['type']       = 3;
        }

        //C('DEBUG_API',true);
		$res = $this->doApi2('/Cart/add',$data);

        $this->ajaxReturn($res);
    }

    /**
     * 根据属性加入购物车
     * Create by Lazycat
     * 2017-02-18
     */
    public function select_to_cart(){
        $this->ajax_check_logined();

        $data['openid']     = session('user.openid');
        $data['type']       = 1;
        $data['num']        = I('post.num');
        if(I('post.atonce') == 1) {
            $data['atonce'] = 1;
            $data['type']   = 3;
        } //立即购买

        //根据属性获取库存ID
        $res = $this->doApi2('/Cart/attr_list_id',['goods_id' => I('post.goods_id'),'attr_id' => implode(',',I('post.attr_id'))]);
        //print_r($res);

        if($res['data']['num'] < I('post.num')) $this->ajaxReturn(['code' => 0,'msg' => '库存不足！']);

        $data['attr_list_id']   = $res['data']['id'];
        $res = $this->doApi2('/Cart/add',$data);

        $this->ajaxReturn($res);
    }


    /**
     * 设置选中的商品
     * Create by Lazycat
     * 2017-02-15
     */
    public function set_selected(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/set_selected',['openid' => session('user.openid'),'ids' => serialize(I('post.cart_id'))]);

        $this->ajaxReturn($res);
    }

    /**
     * 获取已选中要购买的商品
     * Create by Lazycat
     * 2017-02-15
     */
    public function selected_goods(){
        $this->check_logined();
        //C('DEBUG_API',true);
        $address_id = I('get.address_id', 0, 'int');
        $res = $this->doApi2('/Cart/selected_goods',['openid' => session('user.openid'), 'address_id' => $address_id]);
        //print_r($res);
        //exit();
        if($res['code'] != 1){
            redirect('/Cart');
            exit();
        }

        $this->assign('list',$res['data']);
        $this->display();
    }


    /**
     * 收货地址列表
     * Create by Lazycat
     * 2017-02-16
     */
    public function address(){
        $this->check_logined();
        $res = $this->doApi2('/ShoppingAddress/address',['openid' => session('user.openid')]);
        $this->assign('pagelist',$res['data']);
        $this->display();
    }

    public function address_page(){
        $res = $this->doApi2('/ShoppingAddress/address',['openid' => session('user.openid'),'p' => I('get.p')]);
        $this->ajaxReturn($res);
    }


    /**
     * 新增地址
     * Create by Lazycat
     * 2017-02-16
     */
    public function address_add(){
        $this->check_logined();
        $city = $this->doApi2('/City/city_level');
        $this->assign('city',$city['data']);
        //var_dump($city);

        $this->display();
    }

    public function address_add_save(){
        $this->ajax_check_logined();
        $res = $this->doApi2('/ShoppingAddress/add',I('post.'));
        $this->ajaxReturn($res);
    }


    /**
     * 计算运费，更新多个商家运费
     * Create by Lazycat
     * 2017-02-16
     */
    public function express_price_multi(){
        $this->ajax_check_logined();

        $data = [];
        foreach(I('post.data') as $val){
            $val['openid'] = session('user.openid');
            $res = $this->doApi2('/Cart/express_price',$val);

            $data[$val['express_tpl_id']] = $res['data'];
        }

        $this->ajaxReturn(['code' => 1,'data' => $data]);
    }

    /**
     * 创建合并订单
     * Create by Lazycat
     * 2017-02-16
     */
    public function create_orders(){
        $this->ajax_check_logined();

        $data['address_id'] = I('post.address_id');
        $data['openid']     = session('user.openid');
        $data['terminal']   = 1;
        $data['data']       = [];
        if(is_array(I('post.express_tpl_id'))){
            foreach(I('post.express_tpl_id') as $key => $val){
                $data['data'][$key]['express_tpl_id']   = $val;
                $data['data'][$key]['express_type']     = I('post.express_type')[$key];
                $data['data'][$key]['remark']           = I('post.remark')[$key];
                $data['data'][$key]['coupon_id']        = I('post.coupon_id')[$key];
            }
        }else{
            $data['data'][0]['express_tpl_id']  = I('post.express_tpl_id');
            $data['data'][0]['express_type']    = I('post.express_type');
            $data['data'][0]['remark']          = I('post.remark');
            $data['data'][0]['coupon_id']       = I('post.coupon_id');
        }

        $data['data'] = serialize($data['data']);

        //print_r($data);exit();

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/create_orders',$data);

        $this->ajaxReturn($res);
    }


    /**
     * 合并订单支付
     * Create by Lazycat
     * 2017-02-17
     */
    public function orders_multi_view(){
        $this->check_logined();

        $res = $this->doApi2('/Cart/orders_multi_view',['openid' => session('user.openid'),'o_no' => I('get.o_no')]);
        $this->assign('rs',$res['data']);
        if($res['code'] != 1 || $res['data']['status'] !=1){
            redirect('/Cart');
            exit();
        }

//        $account = $this->doApi2('/Erp/account',['erp_uid' => session('user.erp_uid')]);
//        $this->assign('account',$account['data']);
//
//        //支付方式
//        $res = $this->doApi2('/Cashier/paytype',['notid' => '7,8']);
//        $this->assign('paytype',$res['data']);

        $this->display();
    }

    /**
     * 合并订单支付（废弃）
     * Create by Lazycat
     * 2017-02-18
     */
    public function orders_multi_pay(){
        $this->ajax_check_logined();

        //C('DEBUG_API',true);
        if(!empty($_POST['pay_password'])) $_POST['pay_password'] = $this->password(I('post.pay_password'));

        //print_r(I('post.'));
        $res = $this->doApi2('/Erp/orders_multi_pay',I('post.'));

        $this->ajaxReturn($res);
    }
	
	
    /**
     * 购入车减少数量
     * Create by lizuheng
     * 2017-02-20
     */
    public function dec_num(){
		$data['type']         = 2;		
		$data['num']          = 1;
		$data['openid']       = session('user')['openid'];
		$data['attr_list_id'] = I('post.id');
	
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/add',$data);
        $this->ajaxReturn($res);
    }
    /**
     * 购入车增加数量
     * Create by lizuheng
     * 2017-02-20
     */
    public function add_num(){
		$data['type']         = 1;		
		$data['num']          = 1;
		$data['openid']       = session('user')['openid'];
		$data['attr_list_id'] = I('post.id');

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/add',$data);
        $this->ajaxReturn($res);
    }
    /**
     * 设置购入车数量
     * Create by lizuheng
     * 2017-02-20
     */
    public function set_num(){
		$data['type']         = 3;		
		$data['num']          = I('post.num');
		$data['openid']       = session('user')['openid'];
		$data['attr_list_id'] = I('post.id');

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cart/add',$data);
        $this->ajaxReturn($res);
    }
    /**
     * 购入删除商品
     * Create by lizuheng
     * 2017-02-20
     */
    public function delete(){
        $res = $this->doApi2('/Cart/delete',I('post.'));

        $this->ajaxReturn($res);
    }

    /**
     * 收银台合并订单支付（废弃）
     * Create by lazycat
     * 2017-04-06
     */
    public function multi_pay(){
        if(empty($_SESSION['user'])){
            $res['msg'] = '请先登录！';
            goto error;
        }
        $res = $this->doApi2('/Cashier/create_multi_form',['openid' => session('user.openid'),'o_no' => I('get.o_no'),'paytype' => I('get.paytype')]);
        if($res['code'] == 1) {
            echo html_entity_decode($res['data']);
            exit();
        }

        error:
        $this->assign('msg',$res['msg']);
        $this->display('error');
    }

    /**
     * subject: 多订单支付
     * api: multiPay
     * author: Mercury
     * day: 2017-06-22 11:45
     * [字段名,类型,是否必传,说明]
     */
    public function multiPay()
    {

        try {
            if(empty($_SESSION['user'])) throw new Exception('请登录');
            $res = $this->doApi2('/Cashier/multi_orders_pay',['openid' => session('user.openid'),'o_no' => I('get.o_no')]);
            if ($res['code'] != 1) throw new Exception($res['msg']);
            echo html_entity_decode($res['data']);
        } catch (Exception $e) {
            $this->assign('msg',$e->getMessage());
            $this->display('error');
        }
    }

    /**
     * 订单支付状态
     * Create by lazycat
     * 2017-04-06
     */
    public function check_orders_status(){
        //检测erp订单状态
        try {
            $orders = M('orders_shop')->where(['o_no' => I('post.o_no')])->field('s_no,status')->find();
            if ($orders['status'] == 1) {
                //M('orders_shop')->where(['o_no' => I('post.o_no'),'status' => ['neq',2]])->count() > 0
                $res = $this->doApi2('/erp/checkErpOrderStatus', ['s_no' => $orders['s_no']]);
                if ($res['code'] != 1) throw new Exception('支付失败或未支付');
            }
            $returnData = ['code' => 1, 'msg' => '支付成功'];
        } catch (Exception $e) {
            $returnData = ['code' => 0, 'msg' => $e->getMessage()];
        }
        $this->ajaxReturn($returnData);
//        $count = M('orders_shop')->where(['o_no' => I('post.o_no'),'status' => ['neq',2]])->count();
//
//        if($count > 0) $this->ajaxReturn(['code' => 0,'msg' => '支付失败或未支付！']);
//        else $this->ajaxReturn(['code' => 1,'msg' => '支付成功！']);
    }

    /**
     * 支付结果提示
     * Create by lazycat
     * 2017-04-06
     */
    public function pay_result(){
        $count = M('orders_shop')->where(['o_no' => I('get.o_no'),'status' => ['neq',2]])->count();

        if($count > 0){
            $res['code']    = 0;
            $res['msg']     = '支付失败！';
        }else{
            $res['code']    = 1;
            $res['msg']     = '支付成功！';
        }

        $this->assign('res',$res);

        $this->display();
    }

}