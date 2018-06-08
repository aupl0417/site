<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/1/18
 * Time: 16:39
 */

namespace Wap\Controller;
use Common\Builder\Activity;
use Common\Common\Tangpay;
Vendor('cashierSDK2.lib.submit#class');
class PaymentController extends CommonController
{
    protected $single;  //单订单支付句柄
    protected $multi;   //合并订单支付句柄
    protected $dtpay_config;   //支付配置参数

    public function _initialize() {
        parent::_initialize();

        //支付配置参数
        $dtpay_config = C('DTPAY_CONFIG');
        if(C('DTPAY_TEST')){    //是否启用支付测试
            $dtpay_config['Single']['submit_gateway'] = C('DTPAY_SINGLE.TEST');
            $dtpay_config['Multi']['submit_gateway'] = C('DTPAY_MULTI.TEST');
        }else{
            $dtpay_config['Single']['submit_gateway'] = C('DTPAY_SINGLE.ONLINE');
            $dtpay_config['Multi']['submit_gateway'] = C('DTPAY_MULTI.ONLINE');
        }
        /*
        $dtpay_config['Single']['notify_url']   = DM('cart').'/DtpayReturn/notify_single';
        $dtpay_config['Single']['return_url']   = DM('cart').'/DtpayReturn/return_single';
        $dtpay_config['Multi']['notify_url']    = DM('cart').'/DtpayReturn/notify_multi';
        $dtpay_config['Multi']['return_url']    = DM('cart').'/DtpayReturn/return_multi';
        */

        $this->dtpay_config = $dtpay_config;


        //$this->single   = new \dtpaySubmit( 'Single' ,$this->dtpay_config);
        //$this->multi    = new \dtpaySubmit( 'Multi' ,$this->dtpay_config);
        //dump($this->multi);
    }

    /**
     * 合并订单付款
     */
    public function index(){
        if(!IS_POST) $this->ajaxReturn(['code' => 0,'msg' => '非法请求！']);

        $orderNo = I('post.o_no');
        $payType = I('post.paytype', 0, 'int');

        if (!in_array($payType, Tangpay::PAY_TYPE))  $this->ajaxReturn(['code' => 0, 'msg' => '支付方式不正确']);

        if (!$orderNo) $this->ajaxReturn(['code' => 0, 'msg' => '订单号错误']);
        if (empty($_SESSION['user']['openid'])) $this->ajaxReturn(['code' => 0, 'msg' => '请先登录']);

        //检测订单所有者
        $orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>session('user.id')));
        $ret=$orders->check_orders();
        if($ret['code'] != 1) $this->ajaxReturn($ret);

        //检查子订单状态
        $error          = 0;
        $seller_score   = array();
        $pay_price      = 0;    //实付金额
        $score          = 0;    //积分
        $daigou_price   = 0;    //代购费

        $sub_orders = array();
        //$remark     = array();
        foreach($ret['data']['orders_shop'] as $key => $val){
            $tmp = $this->sub_orders($val['s_no'],$payType);
            $ret['data']['orders_shop'][$key]['shop']   = $tmp['shop'];
            $ret['data']['orders_shop'][$key]['seller'] = $tmp['seller'];
            //dump($tmp);
            if($tmp['code'] != 1){
                $error++;
                $ret['data']['orders_shop'][$key]['error_msg']      = $tmp['msg'];
                $ret['data']['orders_shop'][$key]['error_goods']    = $tmp['data'] ? $tmp['data'] : '';
            }else{
                $sub_orders[]   = $tmp['data'];
                $pay_price      += $tmp['data']['orderAmount'];
                $score          += $tmp['data']['giveScore'];
                $daigou_price   += $tmp['data']['buyAgentFee'];

                if($val['inventory_type'] == 1){
                    $seller_score[$val['seller_id']] += $tmp['data']['giveScore'];
                }
                //$remark[]       = $tmp['remark'];
            }
        }
        //商家库存积分是否足够，有可能存在同一家商不同运费模板的订单
        if($seller_score) {
            foreach ($seller_score as $key => $val) {
                $openid = M('user')->where(['id' => $key])->getField('openid');
                $res = $this->doApi('/Erp/account',['openid' => $openid],'',1);
                //dump($res);
                if($res['code'] != 1 || $res['data']['a_storeScore'] < $val){
                    foreach($ret['data']['orders_shop'] as $i => $v) {
                        if($v['seller_id'] == $key){
                            $error++;
                            $ret['data']['orders_shop'][$i]['error_msg'] = '卖家库存积分不足！';
                            //发送短信通知
                            $sms_data     = [];
                            $sms_data['content']    = $this->sms_tpl(16,'{nick}',session('user.nick'));
                            $sms_data['mobile']     = $v['shop']['mobile'];
                            sms_send($sms_data);

                        }
                    }
                }
            }
        }
        //dump($ret['data']['orders_shop']);
        if($error > 0) {
            $this->assign('shop',$ret['data']['orders_shop']);
            $this->display('multi_error');
            exit();
        }
        //exit();

        //dump($ret);

        $remark = '用户'.session('user.nick').'，合并付款单号#'.$orderNo . ', '.$ret['data']['shop_num'].'个商家，共订购'.$ret['data']['goods_num'].'款商品';

        $goods = M('orders_goods')->where(['o_no' => $orderNo])->field('goods_name')->find();   //只取一条即可
        $goods_name = '用户'.session('user.nick').'订购了'.$goods['goods_name'].'……共'.$ret['data']['goods_num'].'款商品';


        $order = array(
            'busID'         => C('busID'),                      //业务类型：“余额业务ID,唐宝业务ID”
            'channelID'     => C('DTPAY_CONFIG.channelID'),     //来源渠道
            'buyerID'       => session('user.erp_uid'),
            'buyerNick'     => session('user.nick'),
            'onlyPay'       => $payType,
            'gOrderID'      => $orderNo,                            //合并订单号
            'orderNum'      => count($ret['data']['orders_shop']),  //订单数量
            'orders'        => json_encode( $sub_orders ),
            'disabledPay'   => '',                                  //不允许用户使用的支付方式：余额Money，唐宝Tangbao，第三方支付Alipay,Wingpay,Wxpay,Unionpay。多个值用英文逗号组合 Money,Tangbao,Wxpay。区分大小写。可为空
            'buyAgentFee'   => $daigou_price,                       //所有子订单的 代购手续费 之和。以“分”为单位。没有则传0
            'orderAmount'   => $pay_price,                          //所有子订单的 订单金额 之和。人民币，以分为单位
            'giveScore'     => $score,                              //所有子订单的 赠送积分 之和
            'goodsName'     => $goods_name,                         //商品名称
            'remark'        => $remark,                             //订单备注
            'returnUrl'     => DM('wap', '/notify/return_multi'),     //同步通知地址
            'notifyUrl'     => DM('cart', '/DtpayReturn/notify_multi'),     //异步通知地址
        );
        log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'multi','post' => var_export($order,true)]);

        //更新支付方式
        M('orders_shop')->where(['o_no' => $orderNo])->setField('pay_type',$payType);

        $dtpay   = new \dtpaySubmit( 'Multi' ,$this->dtpay_config);
        $html_text = $dtpay->buildRequestForm( $order, "post", "确认" );
        echo $html_text;
    }

    /**
     * 子订单生成支付参数
     */
    public function sub_orders($s_no,$paytype){
        if(empty($_SESSION['user'])) return ['code' => 0,'msg' => '请先登录！'];
        if(empty($s_no) || empty($paytype)) return ['code' => 0,'msg' => '参数错误！'];

        $orders=new \Common\Controller\OrdersController(array('s_no'=>$s_no,'uid'=>session('user.id')));
        $ret = $orders->check_s_orders(2);
        if($ret['code'] != 1) return $ret;

        if($ret['data']['status'] != 1) return ['code' => 0,'msg' => '订单状态错误！'];

        //dump($ret);
        $shop   = M('shop')->cache(true)->where(['id' => $ret['data']['shop_id']])->field('id,shop_name,shop_logo,mobile,domain')->find();
        $seller = M('user')->cache(true)->where(['id' => $ret['data']['seller_id']])->field('nick,erp_uid')->find();

        //检测商品属性或价格是否有变更
        $check = $orders->check_goods_attr();
        if($check['code'] !=1 ) return ['code' => 0,'msg' => '订单中有部分商品库存属性或价格已变更！', 'data' => $check['data'],'shop' => $shop,'seller' => $seller];


        $goods_name = $check['data'][0]['goods_name'].'……共'.$ret['data']['goods_num'].'款商品';
        $remark = session('user.nick').'在['.$shop['shop_name'].']订购了['.$check['data'][0]['goods_name'].'……]共'.$ret['data']['goods_num'].'款商品';

        //dump($check);

        $price = $this->ref_orders_price($ret['data'],$paytype);

        $pay_price      = $price['pay_price'];
        $score          = $price['score'];
        $daigou_price   = $ret['data']['daigou_cost'];

        $data = array (
            'channelID'     => C('DTPAY_CONFIG.channelID'),             //渠道编号
            'recieverID'    => $seller['erp_uid'],                      //卖家UID
            'buyerID'       => session('user.erp_uid'),                 //买家UID
            'buyerNick'     => session('user.nick'),
            'merOrderID'    => $ret['data']['s_no'],                            //商家订单号
            'settleMode'    => $ret['data']['inventory_type']==1 ? 1: 2,        //结算模式,1为扣库存积分，2为扣货款
            'buyAgentFee'   => $daigou_price * 100,                             //代购费
            'orderAmount'   => (($pay_price * 100) - ($daigou_price * 100)),                             //实付金额
            'onlyPay'       => $paytype,                                        //支付方式
            'giveScore'     => $score,                                          //积分
            'autoRecieve'   => 0,                                               //是否自动收货
            'goodsUrl'      => DM('my').'/orders/detail/id/'.$ret['data']['s_no'],  //商品地址
            'goodsName'     => $goods_name,                                     //订购商品名称
            'remark'        => $remark,                                         //备注
            'disabledPay'   => '',
            'returnUrl'     => DM('wap', '/notify/return_single'),        //同步通知地址
            'notifyUrl'     => DM('cart', '/DtpayReturn/notify_single'),        //异步通知地址
            'busID'         => C('busID'),
        );

        //dump($data);

        return ['code' => 1,'data' => $data ,'orders' => $ret['data'],'shop' => $shop,'seller' => $seller];

    }

    //检测是否有活动，如果活动存在则重新计算积分及支付金额
    public function ref_orders_price($ret,$paytype){
        //检查是否有唐宝支付打折活动
        $pay_price    =   $ret['pay_price'];
        $score        =   $ret['score'];
        $activity     =   [];
        if ($paytype == 2) {
            $activity = Activity::getTangpayActivityByOrdersShop($ret);   //查找活动
            if ($activity) {
                $pay_price  =   $activity['pay_price'];
                $score      =   $activity['score'];
            }
        }

        return ['pay_price' => $pay_price,'score' => $score];
    }



    /**
     * 单一订单付款
     */
    public function single_pay(){
        $res = $this->sub_orders(I('post.s_no'),I('post.paytype'));

        if($res['code'] == 1){
            //检测库存积分是否足够
            if($res['data']['settleMode'] == 1){    //当分账模式为扣库存积分时
                $openid = M('user')->where(['id' => $res['orders']['seller_id']])->getField('openid');
                $account = $this->doApi('/Erp/account',['openid' => $openid],'',1);
                if($account['code'] != 1 || $account['data']['a_storeScore'] < $res['data']['giveScore']){
                    //发送短信通知
                    $sms_data     = [];
                    $sms_data['content']    = $this->sms_tpl(16,'{nick}',session('user.nick'));
                    $sms_data['mobile']     = $res['shop']['mobile'];
                    sms_send($sms_data);

                    $error = ['code' => 0,'msg' => '卖家库存积分不足！'];
                    $this->assign('error',$error);
                    $this->display('single_error');
                    exit();
                }
            }

            log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'single','post' => var_export($res['data'],true)]);

            //更新支付方式
            M('orders_shop')->where(['s_no' => I('post.s_no')])->setField('pay_type',I('post.paytype'));

            $dtpay   = new \dtpaySubmit( 'Single' ,$this->dtpay_config);
            $html_text = $dtpay->buildRequestForm( $res['data'], "post", "确认" );

            //dump($html_text);
            echo $html_text;
        }else{
            $this->assign('error',$res);
            $this->display('single_error');
        }
    }

    /**
     * 单个订单支付错误提示页面
     */
    public function single_error(){
        $this->display();
    }

    /**
     * 检测订单是否支付成功
     */
    public function check() {
        if (IS_POST) {
            $ono = I('post.ono');
            $data= [
                'code'  =>  1,
                'msg'   =>  '支付成功',
            ];
            $map = [
                's_no'  =>  ['in', rtrim($ono, ',')],
                'is_pay'=>  1,
                'status'=>  2,
            ];

            $tmp = M('orders_shop')->where($map)->getField('id');

            if (false == $tmp) {
                $data= [
                    'code'  =>  0,
                    'msg'   =>  '支付失败',
                ];
            }
            $this->ajaxReturn($data);
        }
    }
}