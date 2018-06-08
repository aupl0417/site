<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 收银台 - 用于话费、流量充值
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-04-05
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
Vendor('cashierSDK2.lib.submit#class');
class MobileCashierController extends ApiController {
    protected $action_logs = array('create_single_form','create_single_url');

    public function _initialize() {
        parent::_initialize();

        //支付配置参数
        $dtpay_config = C('DTPAY_CONFIG');
        if(C('DTPAY_TEST')){    //是否启用支付测试
            $dtpay_config['Single']['submit_gateway']   = C('DTPAY_SINGLE.TEST');
            $dtpay_config['Multi']['submit_gateway']    = C('DTPAY_MULTI.TEST');
        }else{
            $dtpay_config['Single']['submit_gateway']   = C('DTPAY_SINGLE.ONLINE');
            $dtpay_config['Multi']['submit_gateway']    = C('DTPAY_MULTI.ONLINE');
        }

        $this->dtpay_config = $dtpay_config;
    }

    /**
     * subject: 创建单订单支付表单
     * api: /Cashier/create_single_form
     * author: Lazycat
     * day: 2017-04-05
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: paytype,int,1,支付方式
     */
    public function create_single_form(){
        $this->check('openid,s_no,paytype');

        if(C('cfg.site')['is_pay'] !=1) $this->apiReturn(['code' => 0,'msg' => C('cfg.site')['is_pay_tips']]);

        $res = $this->_create_single_form($this->post);
        $this->apiReturn($res);
    }

    public function _create_single_form($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        $ors = M('mobile_orders')->where(['uid' => $this->user['id'],'s_no' => $param['s_no']])->field('atime,etime,ip',true)->find();
        if($ors['status'] != 1) return ['code' => 0,'msg' => '未付款的订单才可以执行此操作！'];

        $shop = D('ShopUserRelation')->relation(true)->where(['id' => $ors['shop_id']])->field('id,status,shop_name,uid')->find();
        if($shop['status'] != 1) return ['code' => 0,'msg' => '店铺已停止营业！'];

        $tmp        = $this->sub_orders($ors,$shop,$param['paytype'],$param['terminal']);

        log_add('mobile_pay_post',['atime' => date('Y-m-d H:i:s'),'type' => 'single','post' => var_export($tmp,true)]);

        $dtpay      = new \dtpaySubmit( 'Single' ,$this->dtpay_config);
        $html_text  = $dtpay->buildRequestForm( $tmp, "post", "确认" );

        return ['code' => 1,'data' => $html_text];
    }


    /**
     * 格式化订单数据
     * Create by lazycat
     * 2017-04-05
     * @param $ors      array   订单数据
     * @param $shop     array   店铺数据
     * @param $paytype  int     支付类型
     * @param $terminal int     支付终端
     */
    public function sub_orders($ors,$shop,$paytype,$terminal=''){
        $this->terminal = $terminal ? $terminal : $this->terminal;
        $goods_name = ($ors['recharge_type'] == 1 ? '手机话费' : '手机流量').$ors['desc'];
        $remark     = $this->user['nick'].'在['.$shop['shop_name'].']订购了['.$goods_name.']共'.$ors['pay_price'].'元';

        $data = array (
            'channelID'     => C('DTPAY_CONFIG.channelID'),            //渠道编号
            'recieverID'    => $shop['erp_uid'],                //卖家UID
            'buyerID'       => $this->user['erp_uid'],                 //买家UID
            'buyerNick'     => $this->user['nick'],
            'merOrderID'    => $ors['s_no'],                           //商家订单号
            'settleMode'    => $ors['type']==1 ? 2: 1,       //结算模式,1为扣库存积分，2为扣货款
            'buyAgentFee'   => 0,                             //代购费
            'orderAmount'   => $ors['pay_price'] * 100,         //实付金额
            'onlyPay'       => $paytype,        //支付方式
            'giveScore'     => $ors['score'],   //积分
            'autoRecieve'   => 0,                                                           //是否自动收货
            'goodsUrl'      => C('sub_domain.m').'/MobileOrders/view/s_no/'.$ors['s_no'],    //商品地址
            'goodsName'     => $goods_name,                                     //订购商品名称
            'remark'        => $remark,                                         //备注
            'disabledPay'   => '',
            'returnUrl'     => C('sub_domain.m').'/MobileReturn/return_single/terminal/'.$this->terminal,     //同步通知地址
            'notifyUrl'     => C('sub_domain.m').'/MobileReturn/notify_single/terminal/'.$this->terminal,     //异步通知地址
            'busID'         => C('busID'),
            //'payAccountCode'=> 'TRJ_SHOPPING',  //专用通道标识
        );

        return $data;
    }


    /**
     * subject: 单订单支付 - 授权并生成支付连接（APP使用）
     * api: /Cashier/create_single_url
     * author: Lazycat
     * day: 2017-04-06
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: paytype,int,1,支付方式
     */
    public function create_single_url(){
        $this->check('openid,s_no,paytype');

        $res = $this->_create_single_url($this->post);
        $this->ajaxReturn($res);
    }

    public function _create_single_url($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        $url = C('sub_domain.m').'/TellRecharge/single_pay/nobar/1/s_no/'.$param['s_no'].'/paytype/'.$param['paytype'].'/terminal/'.$this->terminal;
        $res = A('Rest2/App')->_token(['erp_uid' => $this->user['erp_uid'],'redirect_url' => $url]);
        return $res;
    }

}