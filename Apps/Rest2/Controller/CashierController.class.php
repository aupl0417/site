<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 收银台
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-04-05
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
use Think\Exception;

Vendor('cashierSDK2.lib.submit#class');
class CashierController extends ApiController {
    protected $action_logs = array('create_multi_form','create_single_form','create_multi_url','create_single_url','paytype');

    protected $filter_string = [
        '"',
        '\'',
        '&'
    ];

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
     * subject: 合建合并订单支付表单（废弃）
     * api: /Cashier/create_multi_form
     * author: Lazycat
     * day: 2017-04-05
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: o_no,string,1,合并订单号
     * param: paytype,int,1,支付方式
     */
    public function create_multi_form(){
        $this->check('openid,o_no,paytype',5);

        if(C('cfg.site')['is_pay'] !=1) $this->apiReturn(['code' => 0,'msg' => C('cfg.site')['is_pay_tips']]);

        $res = $this->_create_multi_form($this->post);
        $this->apiReturn($res);
    }

    public function _create_multi_form($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        $ors = M('orders')->where(['uid' => $this->user['id'],'o_no' => $param['o_no']])->field(['id,status,score,pay_price,shop_num,goods_num'])->find();
        if(empty($ors)) return ['code' => 0,'msg' => '合并订单不存在！'];
        if($ors['status'] != 1) return ['code' => 0,'msg' => '已有部分子订单被关闭或支付！'];

        //子订单校验
        $sub_ors = M('orders_shop')->where(['uid' => $this->user['id'],'o_no' => $param['o_no'],'status' => 1])->field('count(*) as shop_num,sum(pay_price) as pay_price,sum(score) as score')->find();
        if($sub_ors['shop_num'] != $ors['shop_num'] || $sub_ors['pay_price'] != $ors['pay_price'] || $sub_ors['score'] != $ors['score']){
            return ['code' => 0,'msg' => '已有部分子订单被关闭或支付！'];
        }

        $list = M('orders_shop')->where(['uid' => $this->user['id'],'o_no' => $param['o_no'],'status' => 1])->field('id,atime,status,s_no,o_id,o_no,shop_id,uid,seller_id,express_price_edit,goods_price_edit,pay_price,inventory_type,score,goods_num')->select();
        $sub_orders = [];
        foreach($list as $key => $val){
            //进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
            $res = A('Rest2/Orders')->_paying(['s_no' => $val['s_no']]);
            if($res['code'] != 1) return $res;

            $res = A('Rest2/Orders')->_orders_pay_check(['s_no' => $val['s_no']],$val);
            if($res['code'] != 1) return $res;

            $tmp        = $this->sub_orders($res['data']['orders'],$res['data']['shop'],$param['paytype']);
            if($tmp['code'] != 1){
                return $tmp;
                break;
            }
            $sub_orders[] = $tmp['orders'];
        }

        $remark     = '用户'.$this->user['nick'].'，合并付款单号#'.$param['o_no'] . ', '.$ors['shop_num'].'个商家，共订购'.$ors['goods_num'].'件商品';
        $goods_name = '用户'.$this->user['nick'].'订购了'.$tmp['goods_name'].'……共'.$ors['goods_num'].'件商品';

        //print_r($sub_orders);

        $order = array(
            'busID'         => C('busID'),                      //业务类型：“余额业务ID,唐宝业务ID”
            'channelID'     => C('DTPAY_CONFIG.channelID'),     //来源渠道
            'buyerID'       => $this->user['erp_uid'],
            'buyerNick'     => $this->user['nick'],
            'onlyPay'       => $param['paytype'],
            'gOrderID'      => $param['o_no'],                      //合并订单号
            'orderNum'      => count($list),  //订单数量
            'orders'        => json_encode( $sub_orders ),
            'disabledPay'   => '',                                  //不允许用户使用的支付方式：余额Money，唐宝Tangbao，第三方支付Alipay,Wingpay,Wxpay,Unionpay。多个值用英文逗号组合 Money,Tangbao,Wxpay。区分大小写。可为空
            'buyAgentFee'   => 0,                                   //所有子订单的 代购手续费 之和。以“分”为单位。没有则传0
            'orderAmount'   => $ors['pay_price'] * 100,             //所有子订单的 订单金额 之和。人民币，以分为单位
            'giveScore'     => $ors['score'],                       //所有子订单的 赠送积分 之和
            'goodsName'     => $goods_name,                         //商品名称
            'remark'        => $remark,                             //订单备注
            'returnUrl'     => C('sub_domain.cart').'/DtpayReturn/return_multi/terminal/'.$this->terminal,     //同步通知地址
            'notifyUrl'     => C('sub_domain.cart').'/DtpayReturn/notify_multi/terminal/'.$this->terminal,     //异步通知地址
            //'payAccountCode'=> 'TRJ_SHOPPING',  //专用通道标识
        );

        //print_r($order);
        log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'multi','post' => var_export($order,true)]);

        $dtpay      = new \dtpaySubmit( 'Multi' ,$this->dtpay_config);
        $html_text  = $dtpay->buildRequestForm( $order, "post", "确认" );

        return ['code' => 1,'data' => $html_text];

    }

    /**
     * subject: 多订单支付
     * api: multi_orders_pay
     * author: Mercury
     * day: 2017-06-22 11:16
     * [字段名,类型,是否必传,说明]
     */
    public function multi_orders_pay()
    {
        $this->check('openid,o_no',5);
        $param = $this->post;
        try {
            if(C('cfg.dtpay')['is_pay'] != 1) throw new Exception('第三方支付维护中，暂停使用！');
            $ors = M('orders')->where(['uid' => $this->user['id'],'o_no' => $param['o_no']])->field(['id,status,score,pay_price,shop_num,goods_num'])->find();
            if(empty($ors)) throw new Exception('合并订单不存在！');
            if($ors['status'] != 1) throw new Exception('已有部分子订单被关闭或支付！');
            //子订单校验
            $sub_ors = M('orders_shop')->where(['uid' => $this->user['id'],'o_no' => $param['o_no'],'status' => 1])->field('count(*) as shop_num,sum(pay_price) as pay_price,sum(score) as score')->find();
            if($sub_ors['shop_num'] != $ors['shop_num'] || $sub_ors['pay_price'] != $ors['pay_price'] || $sub_ors['score'] != $ors['score']) throw new Exception('已有部分子订单被关闭或支付！');


            $list = M('orders_shop')->where(['uid' => $this->user['id'],'o_no' => $param['o_no'],'status' => 1])->field('id,atime,status,s_no,o_id,o_no,shop_id,uid,seller_id,express_price_edit,goods_price_edit,pay_price,inventory_type,score,goods_num')->select();
            $sub_orders = [];
            $recieverID = '';
            foreach($list as $key => $val){
                //进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
                $res = A('Rest2/Orders')->_paying(['s_no' => $val['s_no']]);
                if($res['code'] != 1) throw new Exception($res['msg']);

                $res = A('Rest2/Orders')->_orders_pay_check(['s_no' => $val['s_no']],$val);
                if($res['code'] != 1) throw new Exception($res['msg']);

                $recieverID   = M('user')->cache(true)->where(['id' => $val['seller_id']])->getField('erp_uid');
            }
            $goods= M('orders_goods')->where(['o_id' => $ors['id']])->field('goods_name,score_type,goods_type')->find();

            $data       = [
                'channelID'     =>  C('DTPAY_CONFIG.channelID'),
                'settleMode'    =>  1,  //1实物商品，2虚拟商品
                'merOrderID'    =>  $list[0]['s_no'], //订单号
                'goodsName'     =>  str_replace($this->filter_string, '', $goods['goods_name']), //商品名称
                'ldbScore'      =>  $ors['score'], //乐兑宝
                'orderAmount'   =>  $ors['pay_price'] * 100, //订单金额，分
                'autoRecieve'   =>  0,  //是否自动收货，0否，1是
                'recieverID'    =>  $recieverID, //收款者ID
                'buyerID'       =>  $this->user['erp_uid'], //买家ID
                'buyerNick'     =>  $this->user['nick'], //买家昵称
                'goodsUrl'      =>  DM('m'),    //商品url地址
                'payAccountCode'=>  $param['account_code'] ? : 'NORMAL', //会员角色 'NORMAL':消费者 'UN':联盟商账号 'BM':业务经理账号 'SOC':运营中心账号 'BC':商务中心账号
                'returnUrl'     =>  DM('cart').'/DtpayReturn/return_multi/terminal/'.$this->terminal,     //同步通知地址
                'notifyUrl'     =>  DM('cart').'/DtpayReturn/notify_multi/terminal/'.$this->terminal,     //异步通知地址
                'onlyPay'       =>  $goods['score_type'],
//                'busID0'        =>  '', //余额
//                'busID1'        =>  '', //金积分
//                'busID2'        =>  '', //银积分
            ];

			
			//现金支付
			if($data['onlyPay'] == 2){
				$data['busID0'] = '110203';
				$data['payAccountCode'] = 'BY';
			}

            //请求接口
            $res = A('Rest2/Erp')->multiOrderPay($data);
            if ($res['code'] == 0) throw new Exception($res['msg']);
            $returnData = [
                'code' => 1,
                'data' => $res['data']
            ];
        } catch (Exception $e) {
            $returnData = [
                'code' => 0,
                'msg'  => $e->getMessage()
            ];
        }
        log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'multi','post' => var_export($data,true), 'return' => $returnData]);
        $this->apiReturn($returnData);
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
        $this->check('openid,s_no,paytype',5);

        if(C('cfg.site')['is_pay'] !=1) $this->apiReturn(['code' => 0,'msg' => C('cfg.site')['is_pay_tips']]);

        $res = $this->_create_single_form($this->post);
        $this->apiReturn($res);
    }

    public function _create_single_form($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        //进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
        $res = A('Rest2/Orders')->_paying(['s_no' => $param['s_no']]);
        if($res['code'] != 1) return $res;

        $ors = M('orders_shop')->where(['uid' => $this->user['id'],'s_no' => $param['s_no']])->field('id,atime,status,s_no,o_id,o_no,shop_id,uid,seller_id,express_price_edit,goods_price_edit,pay_price,inventory_type,score,goods_num,coupon_id')->find();
        if($ors['status'] != 1) return ['code' => 0,'msg' => '未付款的订单才可以执行此操作！'];

        $res = A('Rest2/Orders')->_orders_pay_check(['s_no' => $param['s_no']],$ors);
        if($res['code'] != 1) return $res;

        $tmp        = $this->sub_orders($res['data']['orders'],$res['data']['shop'],$param['paytype']);
        if($tmp['code'] != 1) return $tmp;

        log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'single','post' => var_export($tmp['orders'],true)]);

        $dtpay      = new \dtpaySubmit( 'Single' ,$this->dtpay_config);
        $html_text  = $dtpay->buildRequestForm( $tmp['orders'], "post", "确认" );

        return ['code' => 1,'data' => $html_text];
    }


    /**
     * subject: 子订单付款 - 乐兑
     * api: orders_pay
     * author: Mercury
     * day: 2017-06-22 15:22
     * [字段名,类型,是否必传,说明]\
     */
    public function orders_pay()
    {
        $this->check('openid,s_no',5);
        $param = $this->post;
        try {
            if(C('cfg.dtpay')['is_pay'] != 1) throw new Exception('第三方支付维护中，暂停使用！');

            //进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
            $res = A('Rest2/Orders')->_paying(['s_no' => $param['s_no']]);
            if($res['code'] != 1) throw new Exception($res['msg']);

            $ors = M('orders_shop')->where(['uid' => $this->user['id'],'s_no' => $param['s_no']])->field('id,atime,status,s_no,o_id,o_no,shop_id,uid,seller_id,express_price_edit,goods_price_edit,pay_price,inventory_type,score,goods_num,coupon_id')->find();
            if($ors['status'] != 1) throw new Exception('未付款的订单才可以执行此操作！');

            $res = A('Rest2/Orders')->_orders_pay_check(['s_no' => $param['s_no']],$ors);
            if($res['code'] != 1) throw new Exception($res['msg']);

            $goods= M('orders_goods')->where(['s_id' => $res['data']['orders']['id']])->field('goods_name,score_type,goods_type')->find();

            //$tmp        = $this->sub_orders($res['data']['orders'],$res['data']['shop'],$param['paytype']);
            //if($tmp['code'] != 1) throw new Exception($tmp['msg']);
//            $this->user['erp_uid'] = '0008db6e5c9a27c4234da367d8142850';
//            $recieverID            = $this->user['erp_uid'];
//            $this->user['nick']    = 'jingjing';
            $data       = [
                'channelID'     => C('DTPAY_CONFIG.channelID'),
                'settleMode'    =>  1,  //1实物商品，2虚拟商品
                'merOrderID'    =>  $param['s_no'], //订单号
                'goodsName'     =>  str_replace($this->filter_string, '', $goods['goods_name']), //商品名称
                'ldbScore'      =>  $ors['score'], //乐兑宝
                'orderAmount'   =>  $ors['pay_price'] * 100, //订单金额，分
                'autoRecieve'   =>  0,  //是否自动收货，0否，1是
                'recieverID'    =>  M('user')->where(['id' => $ors['seller_id']])->cache(true)->getField('erp_uid'), //收款者ID
                'buyerID'       =>  $this->user['erp_uid'], //买家ID
                'buyerNick'     =>  $this->user['nick'], //买家昵称
                //'recieverID'    =>  $recieverID, //收款者ID
                'goodsUrl'      =>  DM('m'),    //商品url地址
                'payAccountCode'=>  $param['account_code'] ? : 'NORMAL', //会员角色 'NORMAL':消费者 'UN':联盟商账号 'BM':业务经理账号 'SOC':运营中心账号 'BC':商务中心账号
                'returnUrl'     =>  DM('cart').'/DtpayReturn/return_multi/terminal/'.$this->terminal,     //同步通知地址
                'notifyUrl'     =>  DM('cart').'/DtpayReturn/notify_multi/terminal/'.$this->terminal,     //异步通知地址
                'onlyPay'       =>  $goods['score_type'],
                //'busID'         =>  '',
//                'busID0'        =>  '', //余额
//                'busID1'        =>  '', //金积分
//                'busID2'        =>  '', //银积分
            ];

			
			//现金支付
			if($data['onlyPay'] == 2){
				$data['busID0'] = '110203';
				$data['payAccountCode'] = 'BY';
			}

            //请求接口
            $res = A('Rest2/Erp')->multiOrderPay($data);
            if ($res['code'] == 0) throw new Exception($res['msg']);
            $returnData = [
                'code' => 1,
                'data' => $res['data']
            ];
        } catch (Exception $e) {
            $returnData = [
                'code' => 0,
                'msg'  => $e->getMessage()
            ];
        }
        log_add('erp_pays_post',['atime' => date('Y-m-d H:i:s'),'type' => 'multi','post' => var_export($data,true), 'return' => $returnData]);
        $this->apiReturn($returnData);
    }
    
    

    /**
     * 格式化订单数据
     * Create by lazycat
     * 2017-04-05
     */
    public function sub_orders($ors,$shop,$paytype){
        $goods      = M('orders_goods')->where(['s_id' => $ors['id']])->field('goods_name,score_type')->find();
        $goods_name = $goods['goods_name'].'……共'.$ors['goods_num'].'款商品';
        $remark     = $this->user['nick'].'在['.$shop['shop_name'].']订购了['.$goods['goods_name'].'……]共'.$ors['goods_num'].'款商品';

        $data = array (
            'channelID'     => C('DTPAY_CONFIG.channelID'),            //渠道编号
            'recieverID'    => $shop['erp_uid'],                //卖家UID
            'buyerID'       => $this->user['erp_uid'],                 //买家UID
            'buyerNick'     => $this->user['nick'],
            'merOrderID'    => $ors['s_no'],                           //商家订单号
            'settleMode'    => $ors['inventory_type']==1 ? 1: 2,       //结算模式,1为扣库存积分，2为扣货款
            'buyAgentFee'   => 0,                             //代购费
            'orderAmount'   => $ors['pay_price'] * 100,         //实付金额
            'onlyPay'       => $paytype,        //支付方式
            'giveScore'     => $ors['score'],   //积分
            'autoRecieve'   => 0,                                                           //是否自动收货
            'goodsUrl'      => C('sub_domain.m').'/Orders/view/s_no/'.$ors['orders']['s_no'],    //商品地址
            'goodsName'     => $goods_name,                                     //订购商品名称
            'remark'        => $remark,                                         //备注
            'disabledPay'   => '',
            'returnUrl'     => C('sub_domain.cart').'/DtpayReturn/return_single/terminal/'.$this->terminal,     //同步通知地址
            'notifyUrl'     => C('sub_domain.cart').'/DtpayReturn/notify_single/terminal/'.$this->terminal,     //异步通知地址
            'busID'         => C('busID'),
            //'payAcountCode' => 'TRJ_SHOPPING',  //专用通道标识
        );

        //是否有使用官方优惠券
        $coupon = A('Rest2/Erp')->_coupon_data_format($ors['coupon_id']);
        if($coupon) {
            if($paytype == 2) return ['code' => 0,'msg' => '该订单使用了乐兑官方优惠券不支持唐宝支付！'];

            //提交至ERP
            $tmp = A('Rest2/Erp')->_put_coupon(['coupon' => $coupon]);
            if($tmp['code'] != 1) return $tmp;
        }

        return ['code' => 1,'orders' => $data,'goods_name' => $goods['goods_name']];
    }

    /**
     * subject: 获取支付方式
     * api: /Cashier/paytype
     * author: Lazycat
     * day: 2017-04-05
     *
     * [字段名,类型,是否必传,说明]
     * param: type,int,0,终端1=PC，2=WAP，默认为2
     * param: notid,string,0,要屏蔽的支付方式id，多个用逗号隔开
     */
    public function paytype(){
        $this->check($this->_field('notid,type'),false);
        $res = $this->_paytype($this->post);
        $this->apiReturn($res);
    }

    public function _paytype($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];
        $param['type'] = $param['type'] ? $param['type'] : 2;
        $type = $param['type'] == 2 ? '?type=2' : '';


        $iconfont = [
            1   => 'icon-qianbao',
            2   => 'icon-jifen-copy',
            3   => 'icon-weixin1',
            5   => 'icon-zhifubao',
            7   => 'icon-yinlian1193427easyiconnet',
            8   => 'icon-yinlian1193427easyiconnet',
        ];
        $url    = C('cfg.dtpay')['tangpay_url_paytype'].$type;
        $body   = json_decode($this->curl_get($url),true);

        if($body){
            if($param['notid']) {
                $notid = explode(',',$param['notid']);
                foreach($body as $key => $val){
                    if(in_array($val['pg_id'],$notid)) unset($body[$key]);
                }
                $body = array_values($body);
            }
            foreach($body as &$val){
                $val['iconfont'] = $iconfont[$val['pg_id']];
            }
            return ['code' => 1,'data' => $body];
        }
        return ['code' => 3,'msg' => '找不到支付方式！'];
    }


    /**
     * subject: 合并订单支付 - 授权并生成支付连接（APP使用）
     * api: /Cashier/create_multi_url
     * author: Lazycat
     * day: 2017-04-06
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: o_no,string,1,合并订单号
     * param: paytype,int,1,支付方式
     */
    public function create_multi_url(){
        $this->check('openid,o_no,paytype',5);

        $res = $this->_create_multi_url($this->post);
        $this->ajaxReturn($res);
    }

    public function _create_multi_url($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        $url = C('sub_domain.m').'/Cashier/multi_pay/nobar/1/o_no/'.$param['o_no'].'/paytype/'.$param['paytype'];
        $res = A('Rest2/App')->_token(['erp_uid' => $this->user['erp_uid'],'redirect_url' => $url]);
        return $res;
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
        $this->check('openid,s_no,paytype',5);

        $res = $this->_create_single_url($this->post);
        $this->ajaxReturn($res);
    }

    public function _create_single_url($param){
        if(C('cfg.dtpay')['is_pay'] != 1) return ['code' => 0,'msg' => '第三方支付维护中，暂停使用！'];

        $url = C('sub_domain.m').'/Cashier/single_pay/nobar/1/s_no/'.$param['s_no'].'/paytype/'.$param['paytype'];
        $res = A('Rest2/App')->_token(['erp_uid' => $this->user['erp_uid'],'redirect_url' => $url]);
        return $res;
    }

}