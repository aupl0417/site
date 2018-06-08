<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2016/12/19
 * Time: 10:59
 */

namespace Cart\Controller;


use Common\Builder\Activity;
use Common\Common\Tangpay;
use Think\Controller;

class TangpayController extends Controller
{
    protected $_data    =   [];
    protected $_config  =   [];
    protected $sw       =   [];
    protected function _initialize() {
        $this->_config = include_once( VENDOR_PATH . "cashierSDK/lib/dtpay_config.php" );
    }

    /**
     * 支付
     */
    public function index() {
        if (IS_POST) {
            $orderNo = I('post.o_no');
            $payType = I('post.paytype', 0, 'int');
            //log_add('erp_pays_post', array_merge($_POST, include VENDOR_PATH . 'cashierSDK/lib/config.php'));   //记录日志
            if (!in_array($payType, Tangpay::PAY_TYPE))  $this->ajaxReturn(['code' => 0, 'msg' => '支付方式不正确']);

            if (!$orderNo) $this->ajaxReturn(['code' => 0, 'msg' => '订单号错误']);

            if (empty($_SESSION['user']['openid'])) $this->ajaxReturn(['code' => 0, 'msg' => '请登录']);

            $ret = $this->curl('/erp/orders_group_pay2', ['o_no' => $orderNo, 'pay_type' => $payType, 'openid' => $_SESSION['user']['openid']], 1);
            if ($ret['code'] != 1) $this->ajaxReturn($ret);

            $orders = $ret['data'];
            $ordersCnt = count($orders);
            $configs= getSiteConfig('dtpay');
            if ($ordersCnt > 1) {
                $order = array(
                    //业务类型：“余额业务ID,唐宝业务ID”
                    'busID'         => $configs['tangpay_busID'],
                    //来源渠道
                    'channelID'     => $configs['tangpay_channelID'],

                    'buyerID'       => $ret['buyUid'],
                    'buyerNick'     => $ret['nick'],

                    'payChannel'    => array_search($payType, Tangpay::PAY_TYPE),

                    //组合订单号
                    'gOrderID'      => $orderNo,
                    //TODO:订单数量
                    'orderNum'      => $ordersCnt,
                    //TODO:组合订单数据
                    'orders'        => json_encode( $orders ),
                    //不允许用户使用的支付方式：余额Money，唐宝Tangbao，第三方支付Alipay,Wingpay,Wxpay,Unionpay。多个值用英文逗号组合 Money,Tangbao,Wxpay。区分大小写。可为空
                    'disabledPay'   => '',
                    //TODO:所有分订单的 代购手续费 之和。以“分”为单位。没有则传0
                    'buyAgentFee'   => array_sum( array_column( $orders, 'buyAgentFee' ) ),
                    //TODO:所有分订单的 订单金额 之和。人民币，以分为单位
                    'orderAmount'   => array_sum( array_column( $orders, 'orderAmount' ) ),
                    //TODO:所有分订单的 赠送积分 之和
                    'giveScore'     => array_sum( array_column( $orders, 'giveScore' ) ),
                    //商品名称
                    'goodsName'     => implode(',', array_column($orders, 'goodsName')),
                    //订单备注
                    'remark'        => implode(',', array_column($orders, 'remark')),
                    //同步通知地址
                    'returnUrl'     => DM('cart', '/tangpay/returnUrl'),     //同步通知地址
                    //异步通知地址
                    'notifyUrl'     => DM('cart', '/tangpay/notifyUrl'),     //异步通知地址
                    //时间戳
                    //'timestamp'     => NOW_TIME,
                );

                $conf = 'Multi';
            } else {
                $order = $ret['data'][0];
                $conf = 'Single';
            }
            log_add('erp_pays_post', $order);   //记录日志


            require_once( VENDOR_PATH . "cashierSDK/lib/submit.class.php" );

            //建立请求
            $dtpaySubmit = new \dtpaySubmit( $conf );
            $html_text = $dtpaySubmit->buildRequestForm( $order, "post", "确认" );
            echo $html_text;
        }
    }

    /**
     * 异步通知
     */
    public function notifyUrl() {
//        $data = [];
//        $post = [
//            'orderID'     => &$data[ 'p2_id' ],          //---支付平台订单号
//            'channelID'   => &$data[ 'p2_channelID' ],   //---商家订单号
//            'shopOrderID' => &$data[ 'p2_shopOrderID' ], //---商家订单号
//            'payTime'     => &$data[ 'p2_payTime' ],     //---支付时间
//            'recieverID'  => &$data[ 'p2_recipientID' ], //---付款人ID
//            'payerID'     => &$data[ 'p2_payerID' ],     //---付款人ID
//            'orderAmount' => &$data[ 'p2_orderAmount' ], //---订单金额，单位“分”
//            'agentAmount' => &$data[ 'p2_agentAmount' ], //---代购手续费，单位“分”
//            'totalMoney'  => &$data[ 'p2_totalMoney' ],  //---订单总金额，单位“分”
//            'payAmount'   => &$data[ 'p2_payAmount' ],   //---实际支付金额，单位“分”
//            'payChannel'  => &$data[ 'p2_payChannel' ],  //---支付方式：money余额，tangbao唐宝
//            'orderRemark' => &$data[ 'p2_orderRemark' ], //---订单备注，原样返回
//            'busID'       => &$data[ 'p2_busID' ],       //---业务ID
//            'returnUrl'   => &$data[ 'p2_returnUrl' ],   //---同步地址
//            'notifyUrl'   => &$data[ 'p2_notifyUrl' ],   //---异步地址
//            'state'       => &$data[ 'p2_orderState' ],  //---订单状态：0未支付，1已支付
//            'orderIds'    => &$data[ 'p2_subOrderIDs' ], //---商家订单，格式：1,2,3,4,5
//        ];
        $post = I('post.');
        require_once( VENDOR_PATH . "cashierSDK/lib/notify.class.php" );
        $conf = empty($post['subOrderIDs']) ? 'Single' : 'Multi';
        $notify = new \dtpayNotify($conf);
        $checkResult = $notify->verifySign($post);
        log_add('erp_pays_notify', array_merge($post, ['checkResult' => $checkResult]));  //写入日志
        if ($checkResult) {
            if ($post['state'] == 0) {  //未支付
                //TO DO YOUR CODING
            } elseif ($post['state'] == 3) {    //已支付

                if (empty($post['subOrderIDs'])) {   //单订单
                    self::signleOrder($post);
                } else {    //多订单
                    self::mutilOrder($post);
                }
            }

            echo "success";		//请不要修改或删除

        } else {
            //验证失败

            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    /**
     * 同步通知
     */
    public function returnUrl() {
        //https://cart.trj.cc/tangpay/returnUrl.html?id=2016122915505945147922&_s=b99dcd34c5fbcf21d0b333e7b46e45a6
        $id     = I('get.id');
        $token  = I('get._s');
        if (self::verifyNextStepToken($id, $token) === true) {
            $this->redirect('/success');
            die;
        }
        $this->redirect('/success/error');
    }

    /**
     * 获取商品属性
     *
     * @param $s_no
     * @return array
     */
    private static function getGoodsAttr($s_no) {
        $list=M('orders_goods')->where(['s_no' => $s_no])->field('id,s_no,uid,goods_id,attr_list_id,attr_name,price,num,goods_name,images,score_ratio,officialactivity_id,officialactivity_join_id')->select();
        $ids=arr_id(['plist' => $list,'field' => 'attr_list_id']);

        $do=D('Common/GoodsAttrListUpRelation');
        $tmp=$do->relation(true)->where(['id' => ['in',$ids]])->field('id,goods_id,price,num')->select();
        foreach($tmp as $val){
            $goods[$val['id']]=$val;
        }

        $status_name = [
            'price'             =>'价格已变更，请重新下单付款！',
            'activity_start'    =>'活动还款开始！',
            'activity_over'     =>'活动已结束！',
            'activity_max_num'  =>'订购的商品超过活动限够数量！',
            'delete'            =>'商品库存属性已删除！'
        ];
        $status = array();

        $result=array();
        foreach($list as $i => $val){
            if(isset($goods[$val['attr_list_id']])){
                $tmp = $goods[$val['attr_list_id']];
                if($tmp['goods']['status'] !=1) $result['offline'][]     =   $val;   //已下架
                elseif($tmp['num'] < $val['num']) $result['inventory'][]  =   $val;   //库存不足
                elseif($tmp['goods']['score_ratio'] != $val['score_ratio']) $result['score_ratio'][]  =   $val;   //积分赠送比例已变更
                elseif($tmp['price'] != $val['price'] && $val['officialactivity_join_id']==0) { //价格已变更
                    $result['price'][] = $val;
                    if(!in_array('price',$status)) $msg[] = $status_name['price'];
                }

                //是否参与官方活动
                if($val['officialactivity_join_id'] > 0){
                    $officialactivity = D('Common/OfficialactivityJoinUpRelation')->relation(true)->where(['id' => $val['officialactivity_join_id']])->field('activity_id,day,time')->find();
                    $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();

                    if($time_dif > 0) {//活动还款开始
                        $result['activity_start'][] = $val;
                        if(!in_array('activity_start',$status)) $msg[] = $status_name['activity_start'];
                    }

                    if($time_dif < -86400) {//活动已结束
                        $result['activity_over'][]  = $val;
                        if(!in_array('activity_over',$status)) $msg[] = $status_name['activity_over'];
                    }

                    //是否超过限购数量
                    $val['max_buy'] = M('orders_goods')->where(['uid' => $val['uid'],'officialactivity_join_id' => $val['officialactivity_join_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in (2,3,4,5,6,11))'])->sum('num');
                    if($officialactivity['officialactivity']['max_buy'] < ($val['num']+$val['max_buy'])) {//超过限购数量
                        $result['activity_max_num'][]   = $val;
                        if(!in_array('activity_max_num',$status)) $msg[] = $status_name['activity_max_num'];
                    }
                }
            }else{
                //属性已被删除
                $result['delete'][]=$val;
                if(!in_array('delete',$status)) $msg[] = $status_name['delete'];
            }
        }

        if(empty($result)) return ['code' => 1, 'data' => $list];

        //订单中部分商品已下架、库存不足或属性已变更，请重新下单！
        return ['code' => 0, 'data' => $result, 'msg' => implode(',',$msg)];
    }

    /**
     * 单订单付款
     *
     * @param $post array
     */
    private static function signleOrder($post) {
        $do=M('orders_shop');
        $s_no = $post['shopOrderID'];
        if (M('orders_shop')->where(['s_no' => $s_no, 'status' => ['gt', 1], 'is_pay' => 1])->find()) return;    //如果状态为已付款则不更改状态
        $orders = $do->where(['s_no' => $s_no])->field('etime,ip',true)->find();
        $do->startTrans();
        $pay_price  = $orders['pay_price'];
        $score      = $orders['score'];
        $checkAttr  = self::getGoodsAttr($s_no);
        if ($checkAttr['code'] != 1) $msg[$s_no][] = $checkAttr['msg']; //写日志

        if ($post['payChannel'] == 'Tangbao') {  //如果使用的唐宝支付，则查看是否有参与活动
            $activity = Activity::tangPaysActivity($orders);
            if ($activity == false) {
                $activity =   Activity::getActivityByShopOrders($orders, 4);
            }
            if ($activity) {
                $pay_price    =   $activity['pay_price'];
                $score        =   $activity['score'];
            }
        }

        //如果有唐宝支付折扣则更新所有商品价格
        if (!empty($activity) && $activity['full_value'] > 0 && $post['payChannel'] == 'Tangbao') {
            $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
                . ($activity['full_value']) . ',score = score_ratio * score * '. ($activity['full_value']) .' WHERE s_id = ' . $orders['id'] . ' AND s_no = '.$orders['s_no'];
            if ($do->execute($sql) == false) {
                $msg[$s_no][] =   '参与促销时订单商品更新失败';
                //goto error;
            }
            //修改商家订单
            if(M('orders_shop')->where(['id' => $orders['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => Tangpay::PAY_TYPE[$post['payChannel']], 'money' => $pay_price, 'notify_type' => 1, 'is_pay' => 1]) == false) {
                $msg[$s_no][] =   '参与促销时订单更新失败';
                //goto error;
            }
        } else {
            //更新订单状态
            if(!$sw[]=M('orders_shop')->where(['id' => $orders['id']])->save(['pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => Tangpay::PAY_TYPE[$post['payChannel']], 'money' => $pay_price, 'is_pay' => 1])) {
                $msg[$s_no][]  =   '未参与促销时更新订单失败';
            }
        }

        //写入日志
        $logs_data = array(
            'o_id'		=> $orders['o_id'],
            'o_no'		=> $orders['o_no'],
            's_id'		=> $orders['id'],
            's_no'		=> $orders['s_no'],
            'status'	=> 2,
            'remark'	=> '买家已付款'
        );

        /**
         * 创建日志数据
         */
        if(!$sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg[$s_no][] = D('Common/OrdersLogs')->getError();
            //goto error;
        }

        /**
         * 添加日志数据
         */
        if(!$sw[]=D('Common/OrdersLogs')->add()) {
            $msg[$s_no][]  = '添加日志时失败';
            //goto error;
        }


        //如果code==1的时候则更新库存
        $num = 0;
        if ($checkAttr['code'] == 1) {
            //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
            foreach($checkAttr['data'] as $i => $val){
                $goods_id[] = $val['goods_id'];
                $num 	+=	$val['num'];
                $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
                //更新销量
                $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
            }
            $goods_id = array_unique($goods_id);
            goods_pr($goods_id);				//更新宝贝PR
        }
        //更新店铺销量
        if(!$sw[]=M('shop')->where(['id' => $orders['shop_id']])->setInc('sale_num',$num)) {
            $msg[$s_no][]   = '更新店铺销量失败';
            //goto error;
        }


        //付款加1
        if (Activity::activityInc($sql, 'payment_num') == false) {
            //goto error;
        }
        //设置参与者状态
        if (Activity::setStatus($s_no, $orders['uid'], 1, (!empty($activity) ? null : 4)) === false) {
            //goto error;
        }


        //发短信通知
        $sms_data['mobile'] = M('shop')->cache(true)->where(['id' => $orders['shop_id']])->getField('mobile');
        $buyNick    = M('user')->cache(true)->where(['id' => $orders['uid']])->getField('nick');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']    = call_user_func_array([new self(), 'sms_tpl'], [14, ['{nick}','{orderno}','{money}','{goods_num}'], [$buyNick,$orders['s_no'],$orders['pay_price'],$orders['goods_num']]]);
            if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
        }
        shop_pr($orders['shop_id']);	//更新店铺PR
        if (!empty($msg)) {
            log_add('erp_pays_notify_error', $msg);
        }
        $do->commit();  //提交数据
    }

    /**
     * 多订单数据更改
     *
     * @param $post array
     */
    private static function mutilOrder($post) {
        //$s_no = explode(',', trim($_POST['p2_subOrderIDs'], ',')); //子订单
        $do=M('orders_shop');
        $orders = $do->where(['s_no' => ['in', trim($post['subOrderIDs'], ',')]])->field('etime,ip',true)->select();
        $do->startTrans();

        //$pay_price  = 0;    //当个订单支付金额
        //$score      = 0;    //当个订单奖励积分
        $activity   = [];   //当个订单所参与的促销活动
        $msg        = [];   //操作返回msg
        //$checkAttr  = [];   //商品是否有变更
        //碰到一个问题,如果有一个订单更新失败或者属性有问题怎么办？。
        foreach ($orders as $k => $v) {
            if (M('orders_shop')->where(['s_no' => $v['s_no'], 'status' => ['gt', 1], 'is_pay' => 1])->find()) continue;    //如果状态为已付款则不更改状态

            $pay_price  = $v['pay_price'];
            $score      = $v['score'];
            $checkAttr  = self::getGoodsAttr($v['s_no']);

            if ($checkAttr['code'] != 1) $msg[$v['s_no']][] = $checkAttr['msg']; //写日志

            if ($post['payChannel'] == 'Tangbao') {  //如果使用的唐宝支付，则查看是否有参与活动
                $activity = Activity::tangPaysActivity($v);
                if ($activity == false) {
                    $activity =   Activity::getActivityByShopOrders($v, 4);
                }
                if ($activity) {
                    $pay_price    =   $activity['pay_price'];
                    $score        =   $activity['score'];
                }
            }


            //如果有唐宝支付折扣则更新所有商品价格
            if (!empty($activity) && $activity['full_value'] > 0 && $post['payChannel'] == 'Tangbao') {
                $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
                    . ($activity['full_value']) . ',score = score_ratio * score * '. ($activity['full_value']) .' WHERE s_id = ' . $v['id'] . ' AND s_no = '.$v['s_no'];
                if ($do->execute($sql) == false) {
                    $msg[$v['s_no']][] =   '参与促销时订单商品更新失败';
                    //goto error;
                }
                //修改商家订单
                if(M('orders_shop')->where(['id' => $v['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => Tangpay::PAY_TYPE[$post['payChannel']], 'money' => $pay_price, 'notify_type' => 1, 'is_pay' => 1]) == false) {
                    $msg[$v['s_no']][] =   '参与促销时订单更新失败';
                    //goto error;
                }
            } else {
                //更新订单状态
                if(!$sw[]=M('orders_shop')->where(['s_no' => $v['s_no']])->save(['pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => Tangpay::PAY_TYPE[$post['payChannel']], 'money' => $pay_price, 'is_pay' => 1])) {
                    $msg[$v['s_no']][]  =   '未参与促销时更新订单失败';
                }
            }

            //写入日志
            $logs_data = array(
                'o_id'		=> $v['o_id'],
                'o_no'		=> $v['o_no'],
                's_id'		=> $v['id'],
                's_no'		=> $v['s_no'],
                'status'	=> 2,
                'remark'	=> '买家已付款'
            );

            /**
             * 创建日志数据
             */
            if(!$sw[]=D('Common/OrdersLogs')->create($logs_data)){
                $msg[$v['s_no']][] = D('Common/OrdersLogs')->getError();
                //goto error;
            }

            /**
             * 添加日志数据
             */
            if(!$sw[]=D('Common/OrdersLogs')->add()) {
                $msg[$v['s_no']][]  = '添加日志时失败';
                //goto error;
            }


            //如果code==1的时候则更新库存
            $num = 0;
            if ($checkAttr['code'] == 1) {
                //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
                foreach($checkAttr['data'] as $i => $val){
                    $goods_id[] = $val['goods_id'];
                    $num 	+=	$val['num'];
                    $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
                    //更新销量
                    $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
                }
                $goods_id = array_unique($goods_id);
                goods_pr($goods_id);				//更新宝贝PR
            }
            //更新店铺销量
            if(!$sw[]=M('shop')->where(['id' => $v['shop_id']])->setInc('sale_num',$num)) {
                $msg[$v['s_no']][]   = '更新店铺销量失败';
                //goto error;
            }


            //付款加1
            if (Activity::activityInc($v['s_no'], 'payment_num') == false) {
                //goto error;
            }
            //设置参与者状态
            if (Activity::setStatus($v['s_no'], $v['uid'], 1, (!empty($activity) ? null : 4)) === false) {
                //goto error;
            }


            //发短信通知
            $sms_data['mobile'] = M('shop')->cache(true)->where(['id' => $v['shop_id']])->getField('mobile');
            $buyNick    = M('user')->cache(true)->where(['id' => $v['uid']])->getField('nick');
            if(!empty($sms_data['mobile'])){
                $sms_data['content']    = call_user_func_array([new self(), 'sms_tpl'], [14, ['{nick}','{orderno}','{money}','{goods_num}'], [$buyNick,$v['s_no'],$v['pay_price'],$v['goods_num']]]);
                if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
            }
            shop_pr($v['shop_id']);	//更新店铺PR
        }

        if (!empty($msg)) {
            log_add('erp_pays_notify_error', $msg);
        }
        $do->commit();  //提交数据
    }

    /**
     * 同步认证
     *
     * @param $string
     * @param $token
     * @return bool
     */
    private static function verifyNextStepToken($string, $token)
    {
        return md5( $string . '0.13820200 1481726001' ) === $token;
    }

    public function post() {
//        $a = 89.6;
//        $b = [
//            25.41,
//            18.34,
//            22.13,
//            23.72,
//        ];
//        $d = 0;
//        foreach ($b as $v) {
//            $c = bcdiv($v, $a, 2);
//            echo number_formats($c)  . '<br>';
//            $d += $c;
//        }
//        echo $d;

        //echo bcdiv(100.155415, 2, 2);
        //$res = $this->curl('https://cart.trj.cc/tangpay/notifyUrl');
        //print_r($res);
    }
}