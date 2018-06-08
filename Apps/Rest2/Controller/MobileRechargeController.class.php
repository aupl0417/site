<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 话费、流量充值 对接www.007ka.com
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-05-09
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class MobileRechargeController extends ApiController {
    protected $action_logs = array('recharge','create_orders','create_single_form');
    protected $config = array();  //接口参数
    private $error_code = array(  //错误代码
        0   => '-',
        1   => '成功',
        2   => '重复订单， 与原交易不一致',
        3   => '单号重复， 交易已经接受',
        4   => '交易正在处理中',
        5   => '错误的交易指令',
        6   => '接口版本错',
        7   => '代理商校验错',
        8   => '不存在的代理商',
        9   => '其他错误',
        10  => '未定义(保留)',
        13  => '面值不正确',
        14  => '交易已经过期',
        17  => '超过约定交易限额',
        18  => '交易结果不能确定',
        20  => '校验失败',
        21  => '代理商已经暂停交易',
        22  => '交易品种没有定义',
        23  => '暂不支持指定号码充值',
        24  => '不能为该用户充值',
        25  => '指定充值号码与指定类别不一致',
        26  => '该代理商未开通该品种',
        28  => '成功金额小于申报金额',
        29  => '成功金额大于申报金额',
        30  => '充值号码错误',
        31  => '交易信息不存在',
        32  => '代理商错误率太高， 暂停',
        33  => '代理商余额不足',
        34  => '扣代理商款项失败',
        36  => '充值金额与交易金额不符',
        50  => '退款中',
        51  => '退款成功',
    );

    private $post_success       = [1,3,4,10,18];    //受理成功编码！
    private $recharge_success   = [1,29];     //充值成功编码！

    private $mobile_type = [    //手机号网络
        1    => [150,151,152,157,158,159,134,135,136,137,138,139,187,188,147,182,183,184,178],    //移动
        2    => [130,131,132,155,156,186,185,176],    //联通
        3    => [133,153,189,180,181,177],    //电信
    ];

    private $status_name        = [0 => '已删除',1 => '已拍下',2 => '已付款',3 => '已发货',4 => '已收货',5 => '已评价',6 => '已归档',10 => '已关闭',11 => '已退款'];
    private $recharge_type_name = ['','话费充值','流量充值'];
    private $type_name          = ['','奖励积分','不奖励积分'];
    private $paytype_name       = [1 => '余额',2 => '唐宝',3 => '微信',5 => '支付宝',7 => '网银',8 => '网银'];
    private $refund_status_name = [1 => '退款中',2 => '被拒绝',20 => '已取消',100 => '已退款'];

    /**
     * subject: 话费订单列表
     * api: /MobileRecharge/orders
     * author: Lazycat
     * day: 2017-05-11
     * content: 订单状态：0=>'已删除' 1=>'已拍下' 2=>'已付款' 3=>'已发货' 4=>'已收货' 5=>'已评价' 6=>'已归档' 10=>'已关闭' 11=>'已退款'
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     * param: status,int,0,订单状态，同时获取多个状态可用逗号隔开
	 * param: recharge_type,int,0,获取话费还是流量，默认获取话费，(1=话费，2=流量)
     */
    public function orders(){
        $this->check($this->_field('p,pagesize,openid'),false);

        $res = $this->_orders($this->post);
        $this->apiReturn($res);
    }

    public function _orders($param){
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 12;
        if ($param['recharge_type'] && $param['recharge_type'] != "") {
            $map['recharge_type'] = $param['recharge_type'];
        } else {
            $map['recharge_type'] = 1;
        }
        $map['uid']     = $this->user['id'];
        if($param['status'] !='') {
            $map['status'] = ['in',$param['status']];
        }
        $map['_string'] = 'status!=10'; //不显示关闭的订单

        $pagelist = pagelist(array(
            'table'     => 'mobile_orders',
            'do'        => 'M',
            'map'       => $map,
            'fields'    => 'id,uid,atime,seller_id,shop_id,s_no,status,mobile,desc,fare,operator,recharge_type,type,pay_price,pay_time,pay_type,score,return_status,transtat',
            'pagesize'  => $pagesize,
            'p'         => $param['p'],
            'order'     => $order,
        ));
		
		// $statusArr = ['关闭','未付款', '交易成功','退款中','退款完成'];
        
		if($pagelist['list']){
            foreach($pagelist['list'] as &$val){
                $val['status_name'] = $this->status_name[$val['status']];
                $val['type_name']   = $this->type_name[$val['type']];
                $val['recharge_type_name']  = $this->recharge_type_name[$val['recharge_type']];
                $val['transtat_name']       = $val['transtat'] > 0 ? (in_array($val['transtat'],$this->post_success) ? '受理成功' : $this->error_code[$val['transtat']]) : '';
                $val['return_status_name']  = $val['return_status'] > 0 ? (in_array($val['return_status'],$this->recharge_success) ? '充值成功' : $this->error_code[$val['return_status']]) : '';
                $val['paytype_name']        = $val['pay_type'] > 0 ? $this->paytype_name[$val['pay_type']] : '';
				$val['mobile_type']         = $this->_mobile_type($val['mobile']);
                $val['refund']              = $this->_refund_status($val['s_no']);

                $val['can_refund'] = 0;
                if($val['status'] == 2){
                    if(!in_array($val['transtat'],[1,3,4,10,18]) && !in_array($val['return_status'],[1,10,28,29])) $val['can_refund'] = 1;
                }
            }

            return ['code' => 1,'data' => $pagelist];
        }

        return ['code' => 3];
    }

    /**
     * subject: 话费订单详情
     * api: /MobileRecharge/view
     * author: Lazycat
     * day: 2017-05-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,int,1,订单号
     */
    public function view(){
        $this->check('openid,s_no',false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param){
        $rs = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $this->user['id']])->field('etime,ip',true)->find();
        if($rs){
            $rs['status_name'] = $this->status_name[$rs['status']];
            $rs['type_name']   = $this->type_name[$rs['type']];
            $rs['recharge_type_name']   = $this->recharge_type_name[$rs['recharge_type']];
            $rs['transtat_name']        = $rs['transtat'] > 0 ? (in_array($rs['transtat'],$this->post_success) ? '受理成功' : $this->error_code[$rs['transtat']]) : '';
            $rs['return_status_name']   = $rs['return_status'] > 0 ? (in_array($rs['return_status'],$this->recharge_success) ? '充值成功' : $this->error_code[$rs['return_status']]) : '';
            $rs['paytype_name']         = $rs['pay_type'] > 0 ? $this->paytype_name[$rs['pay_type']] : '';

            $rs['refund']               = $this->_refund_status($param['s_no']);
            $rs['can_refund'] = 0;
            if($rs['status'] == 2){
                if(!in_array($rs['transtat'],[1,3,4,10,18]) && !in_array($rs['return_status'],[1,10,28,29])) $rs['can_refund'] = 1;
            }
            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];
    }

    /**
     * 订单退款状态
     */
    private function _refund_status($s_no){
        $rs = M('mobile_orders_refund')->where(['s_no' => $s_no])->field('id,r_no,status')->find();
        if($rs){
            $rs['status_name'] = $this->refund_status_name[$rs['status']];
            return $rs;
        }

        return null;
    }


    /**
     * subject: 创建话费充值订单
     * api: /MobileRecharge/create_orders
     * author: Lazycat
     * day: 2017-05-09
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: mobile,string,1,手机号码
     * param: fare,string,1,充值面值
     * param: type,int,1,充值方案，1=面值*1.1并赠送面值*100的积分，如充50付55并赠5000积分，2=不赠送积分，如充50付50
     * param: recharge_type,int,1,充值类型，1=话费充值，2=流量充值
     * param: paytype,int,0,付款方式，如果有传入此值将会返回支付表单
     */

    public function create_orders(){
        $this->check($this->_field('paytype','openid,mobile,fare,type,recharge_type'),3);

        $res = $this->_create_orders($this->post);
        $this->apiReturn($res);
    }

    public function _create_orders($param){
        $this->config = $this->_get_config($param['recharge_type']);    //接口参数
        if(C('cfg.mobile_fare')['uid'] == $this->user['id']) return ['code' => 0,'msg' => '不能订购自己的手机话费、流量商品，您可更换其它账号进行充值！'];

        //检查手机号码格式
        $res = $this->_check_mobile($param['mobile']);
        if($res['code'] != 1) return $res;

        if($param['recharge_type'] == 1){   //话费充值
            if(C('cfg.mobile_fare')['status'] == 0) return ['code' => 0,'msg' => C('cfg.mobile_fare')['stop_tips']];    //充值接口维护中，暂停服务

            //检查充值面值
            /*
            $fares      = eval(html_entity_decode(C('cfg.mobile_fare')['fare_list']));
            $fare       = $fares[$param['fare']];
            $fare_keys  = array_keys($fares);
            if(!in_array($param['fare'],$fare_keys)) return ['code' => 0,'msg' => '错误的充值面值！'];
            */
            $rs = M('mobile_fare')->where(['type' => 1,'operator' => $this->_mobile_type($param['mobile']),'fare' => $param['fare']])->field('fare,price')->find();
            if(!$rs) return ['code' => 0,'msg' => '错误的充值面值！'];

            $fare       = trim(str_replace('元','',$rs['fare']));
            //每个用户每天最多充值金额
            $user_fare = M('mobile_orders')->where(['uid' => $this->user['id'],'status' => 2,'recharge_type' => 1,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.date('Y-m-d').'"'])->sum('fare');
            if(($user_fare + $fare) > $this->config['user_max_fare']) return ['code' => 0,'msg' => '每个用户每天充值面额累积不得超过￥'.$this->config['user_max_fare'].'元'];

            //手机号码每天限额检测
            $mobile_fare = M('mobile_orders')->where(['mobile' => $param['mobile'],'status' => 2,'recharge_type' => 1,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.date('Y-m-d').'"'])->sum('fare');
            if(($mobile_fare + $fare) > $this->config['mb_max_fare']) return ['code' => 0,'msg' => '每个手机号码每天充值面额累积不得超过￥'.$this->config['mb_max_fare'].'元'];


        }else{  //流量充值
            if(C('cfg.mobile_flow')['status'] == 0) return ['code' => 0,'msg' => C('cfg.mobile_flow')['stop_tips']];    //充值接口维护中，暂停服务

            //检查充值面值
            /*
            $fares      = eval(html_entity_decode(C('cfg.mobile_flow')['fare_list']));
            $fare       = str_replace(array('M','G'),array('','000'),$param['fare']);
            $fare_keys  = array_keys($fares);
            if(!in_array($param['fare'],$fare_keys)) return ['code' => 0,'msg' => '错误的流量面值！'];
            */
            $rs = M('mobile_fare')->where(['type' => 2,'operator' => $this->_mobile_type($param['mobile']),'fare' => $param['fare']])->field('fare,price')->find();
            if(!$rs) return ['code' => 0,'msg' => '错误的流量面值！'];

            $fare       = trim(str_replace(array('M','G'),array('','000'),$rs['fare']));
            //每个用户每天最多充值金额
            $user_fare = M('mobile_orders')->where(['uid' => $this->user['id'],'status' => 2,'recharge_type' => 2,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.date('Y-m-d').'"'])->sum('fare');
            if(($user_fare + $fare) > $this->config['user_max_flow']) return ['code' => 0,'msg' => '每个用户每天充值流量累积不得超过'.$this->config['user_max_flow'].'M'];

            //手机号码每天限额检测
            $mobile_fare = M('mobile_orders')->where(['mobile' => $param['mobile'],'status' => 2,'recharge_type' => 2,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.date('Y-m-d').'"'])->sum('fare');
            if(($mobile_fare + $fare) > $this->config['mb_max_flow']) return ['code' => 0,'msg' => '每个手机号码每天充值流量累积不得超过'.$this->config['mb_max_flow'].'M'];
        }

        $pay_price  = $param['type'] == 1 ? $rs['price'] * $this->config['ratio'] : $rs['price'];
        $score      = $param['type'] == 1 ? $rs['price'] * 100 : 0;

        //创建订单
        $data = [
            'uid'           => $this->user['id'],
            'seller_id'     => C('cfg.mobile_fare')['seller_id'],
            'shop_id'       => C('cfg.mobile_fare')['shop_id'],
            's_no'          => $this->create_orderno('MB'),
            'status'        => 1,
            'mobile'        => $param['mobile'],
            'desc'          => $param['fare'],
            'fare'          => $fare,
            'operator'      => $this->_mobile_type($param['mobile']),
            'recharge_type' => $param['recharge_type'],
            'type'          => $param['type'],
            'pay_price'     => $pay_price,
            'score'         => $score,
            'terminal'      => $this->terminal,
            'next_time'     => date('Y-m-d H:i:s',time() + C('cfg.orders')['add']),   //过了这个时间未付款将关闭订单
        ];

        $do = D('MobileOrders');
        if(!$do->create($data)) return ['code' => 0,'msg' => $do->getError()];
        if(!$do->add()) return ['code' => 0,'msg' => '创建充值订单失败！'];

        //当有传递支付方式时则创建收银台支付表单
        $form = '';
        if($param['paytype']) {
            if($param['paytype'] == 2) return ['code' => 0,'msg' => '不支持唐宝支付！'];
            $form = A('Rest2/MobileCashier')->_create_single_form(['s_no' => $data['s_no'],'paytype' => $param['paytype']]);
        }

        return ['code' => 1,'data' => $data,'form' => $form];
    }

    //检查手机号码格式
    private function _check_mobile($mobile){
        $is_mobile = checkform($mobile,'is_mobile2');
        if(!$is_mobile) return ['code' => 0,'msg' => '手机号码格式错误！'];

        $is_mobile = $this->_mobile_type($mobile);
        if($is_mobile == 0) return ['code' => 0,'msg' => '不支持该类型的手机号码充值！'];

        return ['code' => 1];
    }


    /**
     * subject: 话费充值-生成支付表单
     * api: /MobileRecharge/create_single_form
     * author: Lazycat
     * day: 2017-05-09
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: paytype,int,1,付款方式
     * param: terminal,int,1,终端
     */

    public function create_single_form(){
        $this->check($this->_field('terminal','openid,s_no,paytype'));

        $res = $this->_create_single_form($this->post);
        $this->apiReturn($res);
    }

    public function _create_single_form($param){
        if($param['paytype'] == 2) return ['code' => 0,'msg' => '不支持唐宝支付！'];

        $rs = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $this->user['id']])->field('id,status')->find();
        if($rs['status'] != 1) return ['code' => 0,'msg' => '错误的订单状态！'];

        $res = A('Rest2/MobileCashier')->_create_single_form(['s_no' => $param['s_no'],'paytype' => $param['paytype'],'terminal' => $param['terminal']]);
        return $res;
    }


    /**

    话费充值接口返回的数据格式
    object(SimpleXMLElement)#16 (14) {
    ["MerID"] => string(10) "1000000007"
    ["MerAccount"] => string(15) "100000000000007"
    ["OrderID"] => string(26) "MB201705091616583729599173"
    ["TranStat"] => string(1) "1"
    ["TranInfo"] => string(7) "Success"
    ["CardType"] => string(1) "1"
    ["Value"] => string(4) "1000"
    ["Command"] => string(2) "11"
    ["InterfaceName"] => string(8) "007KA_KM"
    ["InterfaceNumber"] => string(7) "1.0.1.2"
    ["Datetime"] => string(14) "20170509165829"
    ["TranOrder"] => string(17) "20149432028919908"
    ["Attach"] => object(SimpleXMLElement)#17 (0) {
    }
    ["Sign"] => string(32) "4219BF0587DBDF449C0ABFF744120141"
    }
     */

    /**
     * subject: 话费充值(对接时无须用到此接口)
     * api: /MobileRecharge/recharge
     * author: Lazycat
     * day: 2017-05-10
     * content: 此接口为提交到充值平台，为内部调用，对接时无须用到此接口
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     */

    public function recharge(){
        $this->check('openid,s_no',3);

        $res = $this->_recharge($this->post);
        $this->apiReturn($res);
    }

    public function _recharge($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        if(C('cfg.mobile_fare')['uid'] == $uid) return ['code' => 0,'msg' => '不能订购自己的手机话费、流量商品，您可更换其它账号进行充值！'];

        $ors = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $uid])->field('id,s_no,fare,mobile,pay_price,recharge_type,return_status,next_time')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if($ors['recharge_type'] == 1) {   //话费充值
            if (C('cfg.mobile_fare')['status'] == 0) return ['code' => 0, 'msg' => C('cfg.mobile_fare')['stop_tips']];    //充值接口维护中，暂停服务
        }else{
            if(C('cfg.mobile_flow')['status'] == 0) return ['code' => 0,'msg' => C('cfg.mobile_flow')['stop_tips']];    //充值接口维护中，暂停服务
        }

        if($ors['next_time'] < date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '重新提交时间已过期！'];

        $this->config = $this->_get_config($ors['recharge_type']);  //接口参数

        $cache_name = 'mobile_recharge_'.$ors['recharge_type'].'_'.$ors['mobile'];
        $tmp = S($cache_name);
        if(!empty($tmp)) return ['code' => 0,'msg' => '重新提交充值间隔须在15秒以上！'];

        $cardtype = $this->_mobile_type($ors['mobile']);
        if($cardtype == 0) return ['code' => 0,'msg' => '不支持该手机号码充值！'];

        if(in_array($ors['return_status'],[1,29])) return ['code' => 0,'msg' => '已充值成功，如话费长时间未到账请联系客服处理！'];

        //较验充值金额
        $res = A('Rest2/Erp')->_orders_in_erp_status($ors['s_no']);
        if($res['code'] != 1) return $res;
        if($res['data']['o_totalMoney'] != $ors['pay_price']) return ['code' => 0,'msg' => '实付金额存在异常！'];

        $options = [
            'MerID'             => $this->config['mid'],
            'MerAccount'        => $this->config['account'],
            'OrderID'           => $ors['s_no'],
            'CardType'          => $cardtype,
            'Value'             => $ors['fare'] * 100,   //以分为单位
            'timeout'           => 2,
            'Province'          => 0,
            'ReplyFormat'       => 'xml',
            'Command'           => $ors['recharge_type'] == 1 ? 9 : 37,
            'InterFaceName'     => '007KA_KM',
            'InterFaceNumber'   => $ors['recharge_type'] == 1 ? '1.0.1.2' : '1.0.1.3',
            'CardSn'            => '',
            'CardKey'           => '',
            'ChargeNo'          => $ors['mobile'],
            'Datetime'          => date('YmdHis'),
            'TranOrder'         => '',
            'MerUrl'            => C('sub_domain.m').'/MobileReturn/callback',
            'Attrach'           => '',
        ];

        //接口接收的数据编码为gb2312，如果Attrach含有中文，须转成gb2312编码，接口返回数据同样也是gb2312编码
        $data['Orderinfo']  = implode('|',$options);
        $data['Sign']       = strtoupper(md5($data['Orderinfo'].'|'.$this->config['key']));

        $res = $this->curl_post($this->config['trade_url'],$data);
        $res = objectToArray(simplexml_load_string($res));

        S($cache_name,1,15);    //缓存15秒
        M('mobile_orders')->where(['id' => $ors['id']])->save(['transtat' => $res['TranStat'],'dotime' => date('Y-m-d H:i:s')]);

        log_add('mobile_recharge_post',['atime' => date('Y-m-d H:i:s'),'subject' => ($ors['recharge_type'] == 1 ? '话费' : '流量').'充值','recharge_type' => $ors['recharge_type'],'post' => var_export($options,true),'res' => var_export($res,true),'repost' => $param['repost'] ? $param['repost'] : 0]);
        return ['code' => 1,'data' => ['status' => $res['TranStat'],'msg' => $this->error_code[$res['TranStat']]],'msg' => '已提交至充值中心，请等待处理！'];
    }


    /**
     * subject: 话费充值-关闭订单
     * api: /MobileRecharge/orders_close
     * author: Lazycat
     * day: 2017-05-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     */

    public function orders_close(){
        $this->check('openid,s_no');

        $res = $this->_orders_close($this->post);
        $this->apiReturn($res);
    }

    public function _orders_close($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $ors = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $uid])->field('id,s_no,status,fare,mobile,pay_price,recharge_type,return_status,next_time')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if(!in_array($ors['status'],[1,2])) return ['code' => 0,'msg' => '当前状态不允许关闭订单！'];

        if(M('mobile_orders')->where(['id' => $ors['id'],'status' => $ors['status']])->save(['status' => 10,'close_time' => date('Y-m-d H:i:s')])){
            return ['code' => 1,'msg' => '关闭订单成功！'];
        }

        return ['code' => 0,'msg' => '关闭订单失败！'];
    }


    /**
     * 确认收货，ERP执行收货后再执行此接口更新订单状态
     * @param $ors
     */
    public function _orders_confirm($ors){
        //退款列表
        $refund = M('mobile_orders_refund')->where(['s_id' => $ors['id'],'status' => ['not in','20,100']])->field('id,r_no,uid')->select();

        $do = M();
        $do->startTrans();  //事务开始

        //如果存在着退款，即将退款取消
        if($refund){
            if(!$this->sw[] = M('mobile_orders_refund')->where(['s_id' => $ors['id'],'status' => ['not in','20,100']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])) goto error;
            //日志
            foreach($refund as $val){
                //日志数据
                $logs=[
                    'r_id'          => $val['id'],
                    'r_no'          => $val['r_no'],
                    'uid'           => $val['uid'],
                    'status'        => 20,
                    'remark'        => '买家确认收货，默认取消退款！', //买家取消退款！
                    'money'         => $rs['money'],
                    'score'         => $rs['score'],
                ];

                if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->create($logs)){
                    $msg = D('Common/MobileOrdersRefundLogs')->getError();
                    goto error;
                }

                if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->add()){
                    $msg = '创建退款记录失败！';
                    goto error;
                }
            }
        }


        //更新订单
        if(!$this->sw[] = M('mobile_orders')->where(['id' => $ors['id'],'status' => 3])->save(['status' => 4,'receipt_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['rate_add']),'is_problem' => 0])){
            $msg = '更新订单状态失败！';
            goto error;
        }


        $do->commit();
        return ['code' => 1,'msg' => '确认收货成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => !empty($msg) ? $msg : '确认收货失败！'];

    }


    /**
     * subject: 话费退款
     * api: /MobileRecharge/refund
     * author: Lazycat
     * day: 2017-05-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     */

    public function refund_add(){
        $this->check('openid,s_no',3);

        $res = $this->_refund_add($this->post);
        $this->apiReturn($res);
    }

    public function _refund_add($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $ors = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $uid])->field('id,uid,seller_id,shop_id,s_no,status,fare,mobile,pay_price,score,recharge_type,return_status,transtat,next_time')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if($ors['status'] != 2) return ['code' => 0,'msg' => '订单状态错误！'];

        //考虑到队列处理可能存在延时问题，所以时间延后10分钟才可申请退款
        //$next_time = date('Y-m-d H:i:s',strtotime($ors['next_time']) + 600);
        if($ors['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '必须在'.$ors['next_time'].'后方可申请退款！'];

        //transtat及return_status为第三方接口返回的状态，请对照第三方文档
        //if(in_array($ors['transtat'],[1,3,4,10,18]) || in_array($ors['return_status'],[1,10,28,29])) return ['code' => 0,'msg' => '充值订单已被受理，不支持退款，有疑问请联系客服处理！'];
        if(in_array($ors['transtat'],[1,3,4,10,18]) && !in_array($ors['return_status'],[9,14,23,24,51])) return ['code' => 0,'msg' => '充值订单已被受理，不支持退款，有疑问请联系客服处理！'];
        if(in_array($ors['return_status'],[1,10,28,29])) return ['code' => 0,'msg' => '充值订单已被受理，不支持退款，有疑问请联系客服处理！'];

        $id = M('mobile_orders_refund')->where(['s_no' => $param['s_no']])->getField('id');
        if($id) return ['code' => 0,'msg' => '已申请过退款，不可再次发起退款申请！'];

        $do = M();
        $do->startTrans();
        $data = [
            'r_no'          => $this->create_orderno('MT'),
            'uid'           => $ors['uid'],
            'seller_id'     => $ors['seller_id'],
            'shop_id'       => $ors['shop_id'],
            's_id'          => $ors['id'],
            's_no'          => $ors['s_no'],
            'money'         => $ors['pay_price'],
            'score'         => $ors['score'],
            'status'        => 1,
            'orders_status' => $ors['status'],
            'reason'        => '话费未到账',
            'next_time'     => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
        ];

        //创建退款记录
        if(!$this->sw[] = D('Common/MobileOrdersRefund')->create($data)){
            $msg = D('Common/MobileOrdersRefund')->getError();
            goto error;
        }

        if(!$this->sw[] = $data['id'] = D('Common/MobileOrdersRefund')->add()){
            $msg = '创建退款记录失败！';
            goto error;
        }

        //创建退款日志
        $logs = [
            'r_id'      => $data['id'],
            'r_no'      => $data['r_no'],
            'uid'       => $ors['uid'],
            'status'    => $data['status'],
            'remark'    => $data['reason'],
            'money'     => $data['money'],
            'score'     => $data['score'],
        ];

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->create($logs)){
            $msg = D('Common/MobileOrdersRefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->add()){
            $msg = '创建退款记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => ['r_no' => $data['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败！'];
    }


    /**
     * 自动退款
     * Create by lazycat
     * 2017-05-13
     * @param int       $uid    用户ID
     * @param string    $s_no   订单号
     */
    public function _auto_refund($param){
        $r_no = M('mobile_orders_refund')->where(['uid' => $param['uid'],'s_no' => $param['s_no']])->getField('r_no');

        if(empty($r_no)) {
            $res = $this->_refund_add($param);
            if ($res['code'] != 1) return $res;

            $r_no = $res['data']['r_no'];
        }

        $res = A('Rest2/Erp')->_mobile_recharge_refund(['uid' => $param['uid'],'r_no' => $r_no]);
        return $res;
    }

    /**
     * subject: 话费退款详情
     * api: /MobileRecharge/refund_view
     * author: Lazycat
     * day: 2017-05-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function refund_view(){
        $this->check($this->_field('is_logs','openid,r_no'),false);

        $res = $this->_refund_view($this->post);
        $this->apiReturn($res);
    }

    public function _refund_view($param){
        $rs = M('mobile_orders_refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('id,r_no,uid,seller_id,shop_id,s_no,money,score,status,orders_status,reason,cancel_time,accept_time,next_time')->find();
        if($rs){
            $rs['orders_status_name']   = $this->status_name[$rs['orders_status']];
            $rs['status_name']          = $this->refund_status_name[$rs['status']];

            $rs['logs'] = null;
            if($param['is_logs'] == 1) {
                $tmp = $this->_refund_logs(['r_no' => $param['r_no']]);
                if($tmp['code'] == 1) $rs['logs'] = $tmp['data'];
            }
            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];
    }

    /**
     * subject: 话费取消退款
     * api: /MobileRecharge/refund_cancel
     * author: Lazycat
     * day: 2017-05-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function refund_cancel(){
        $this->check('openid,r_no',false);

        $res = $this->_refund_cancel($this->post);
        $this->apiReturn($res);
    }

    public function _refund_cancel($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $rs = M('mobile_orders_refund')->where(['uid' => $uid,'r_no' => $param['r_no']])->field('id,r_no,uid,seller_id,shop_id,s_no,money,score,status,orders_status,reason,cancel_time,accept_time,next_time')->find();
        if(!in_array($rs['status'],[1,2])) return ['code' => 0,'msg' => '当前状态下不允许取消退款！'];

        $do = M();
        $do->startTrans();

        if(!$this->sw[] = M('mobile_orders_refund')->where(['id' => $rs['id']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])){
            $msg = '列新退款订单失败！';
            goto error;
        }

        //创建退款日志
        $logs = [
            'r_id'      => $rs['id'],
            'r_no'      => $rs['r_no'],
            'uid'       => $rs['uid'],
            'status'    => 20,
            'remark'    => '买家取消退款！',
            'money'     => $rs['money'],
            'score'     => $rs['score'],
        ];

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->create($logs)){
            $msg = D('Common/MobileOrdersRefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->add()){
            $msg = '创建退款记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => ['r_no' => $rs['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败！'];
    }

    /**
     * subject: 话费充值-拒绝退款
     * api: /MobileRecharge/refund_reject
     * author: Lazycat
     * day: 2017-05-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function refund_reject(){
        $this->check('openid,r_no',false);

        $res = $this->_refund_reject($this->post);
        $this->apiReturn($res);
    }

    public function _refund_reject($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $rs = M('mobile_orders_refund')->where(['uid' => $uid,'r_no' => $param['r_no']])->field('id,r_no,uid,seller_id,shop_id,s_no,money,score,status,orders_status,reason,cancel_time,accept_time,next_time')->find();
        if($rs['status'] != 1) return ['code' => 0,'msg' => '当前状态下不允许拒绝退款！'];

        $do = M();
        $do->startTrans();

        if(!$this->sw[] = M('mobile_orders_refund')->where(['id' => $rs['id']])->save(['status' => 2,'dotime' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express'])])){
            $msg = '列新退款订单失败！';
            goto error;
        }

        //创建退款日志
        $logs = [
            'r_id'      => $rs['id'],
            'r_no'      => $rs['r_no'],
            'uid'       => $rs['uid'],
            'status'    => 2,
            'remark'    => '卖家拒绝退款！',
            'money'     => $rs['money'],
            'score'     => $rs['score'],
        ];

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->create($logs)){
            $msg = D('Common/MobileOrdersRefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->add()){
            $msg = '创建退款记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => ['r_no' => $rs['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败！'];
    }

    /**
     * 话费退款，用于请求ERP后再请求此接口更新订单状态
     * @param $rs
     * @return array
     */
    public function _refund_accept($rs){
        $do = M();
        $do->startTrans();
        if(!$this->sw[] = M('mobile_orders')->where(['s_no' => $rs['s_no'],'status' => $rs['orders_status']])->save(['status' => 11,'refund_time' => date('Y-m-d H:i:s')])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        if(!$this->sw[] = M('mobile_orders_refund')->where(['id' => $rs['id'],'status' => $rs['status']])->save(['status' => 100,'accept_time' => date('Y-m-d H:i:s')])){
            $msg = '更新退款订单状态失败！';
            goto error;
        }

        //创建退款日志
        $logs = [
            'r_id'      => $rs['id'],
            'r_no'      => $rs['r_no'],
            'uid'       => $rs['uid'],
            'status'    => 100,
            'remark'    => '卖家同意退款！',
            'money'     => $rs['money'],
            'score'     => $rs['score'],
        ];

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->create($logs)){
            $msg = D('Common/MobileOrdersRefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/MobileOrdersRefundLogs')->add()){
            $msg = '创建退款记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => ['r_no' => $rs['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '退款失败！'];
    }


    /**
     * subject: 退款协商详情
     * api: /MobileRecharge/refund_logs
     * author: Lazycat
     * day: 2017-03-10
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function refund_logs(){
        $this->check('openid,r_no',false);

        $res = $this->_refund_logs($this->post);
        $this->apiReturn($res);
    }
    public function _refund_logs($param){
        $list = D('Common/MobileOrdersRefundLogsRelation')->relation(true)->where(['uid' => $this->user['id'],'r_no' => $param['r_no']])->field('id,r_id,r_no,uid,a_uid,status,remark,money,score')->order('id desc')->select();
        if($list) {
            foreach($list as &$val){
                $val['status_name'] = $this->refund_status_name[$val['status']];
            }
            return ['code' => 1, 'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * subject: 添加退款留言
     * api: /MobileRecharge/refund_logs_add
     * author: Lazycat
     * day: 2017-03-10
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     * param: remark,string,1,留言
     * param: images,string,0,凭证图片，多张用逗号隔开
     */
    public function refund_logs_add(){
        $this->check($this->_field('images','openid,r_no,remark'));

        $res = $this->_refund_logs_add($this->post);
        $this->apiReturn($res);
    }

    public function _refund_logs_add($param){
        $rs = M('mobile_orders_refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('id,uid,r_no,status')->find();
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '退款已关闭，不能发表留言！'];

        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $rs['uid'],
            'status'        => $rs['status'],
            'remark'        => $param['remark'],
            'images'        => $param['images'],
        ];

        if(!D('Common/MobileOrdersRefundLogs')->create($logs)) return ['code' => 0,'msg' => D('Common/MobileOrdersRefundLogs')->getError()];

        if($logs_id = D('Common/MobileOrdersRefundLogs')->add()) return ['code' => 1,'data' => ['logs_id' => $logs_id]];

        return ['code' => 0,'msg' => '添加留言失败！'];
    }

    /**
     * subject: 检查某个订单与ERP数据对比是否正常，如有异常将自动修复
     * api: /MobileRecharge/check_ordres_in_erp
     * author: lazycat
     * day: 2017-05-12
     * content: 主要针对ERP接口返回超时后订单没更改状态的情况，此为工具类接口
     *
     * [字段名,类型,是否必传,说明]
     * param: s_no,string,1,订单号
     */
    public function check_ordres_in_erp(){
        $this->check('s_no',false);

        $res = $this->_check_ordres_in_erp($this->post);
        $this->apiReturn($res);
    }

    public function _check_ordres_in_erp($param){
        $ors = M('mobile_orders')->where(['s_no' => $param['s_no']])->field('id,uid,s_no,status')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '找不到订单记录！'];

        $res = A('Rest2/Erp')->_orders_in_erp_status($param['s_no']);
        if($res['code'] != 1) return ['code' => 0,'msg' => '获取ERP订单状态失败！'];

        switch ($ors['status']){
            //状态为未付款，实际ERP中已付款
            case 1:
            case 20:
                if($res['data']['o_orderState'] > 0){
                    $ret = $this->_fix_orders($param['s_no'],$res['data']);

                    $logs_data = [
                        'atime'         => NOW_TIME,
                        'subject'       => 'ERP已付款成功但商城状态还是未付款',
                        's_no'          => $ors['s_no'],
                        'status'        => $ors['status'],
                        'res'           => $ret['code'],
                        'fix_status'    => $ret['code'] == 1 ? '修复成功！' : $ret['msg'],
                    ];
                    log_add('orders_fix',$logs_data);
                    return ['code' => 1,'msg' => '订单已修正！','data' => ['s_no' => $param['s_no']]];
                }
                break;
        }

        return ['code' => 1,'msg' => '订单状态正常！','data' => ['s_no' => $param['s_no']]];
    }

    /**
     * 修复订单
     * Create by lazycat
     * 2017-05-10
     * @param string $s_no 订单号
     * @param array $status 在ERP中的状态
     * @param int $terminal 操作终端
     */
    public function _fix_orders($s_no,$status,$terminal=1){
        $ors = M('mobile_orders')->lock(true)->where(['s_no' => $s_no])->find();
        if($ors['status'] != 1) {
            log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复失败（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 0,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '错误的订单状态！' ,'sw' => '']);
            return false;
        }

        $this->config = $this->_get_config($ors['recharge_type']);  //接口参数

        //更新订单
        //$do = M();
        //$do->startTrans();

        if(!$this->sw[] = M('mobile_orders')->where(['id' => $ors['id']])->save(['status' => 2,'pay_time' => date('Y-m-d H:i:s'),'pay_type' => $status['o_payType'],'dtpay_no' => $status['o_id'],'next_time' => date('Y-m-d H:i:s',time() + $this->config['limit_time']),'terminal' => $terminal])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        log_add('mobile_success',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复成功（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 1,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '支付成功！' ,'sw' => @implode(',',$this->sw)]);

        //提交至充值平台进行充值，是否充值成功，可通过队列检测处理
        $this->_recharge(['uid' => $ors['uid'],'s_no' => $ors['s_no']]);

        return true;

        error:
        log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复失败（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 0,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '支付成功！' ,'sw' => @implode(',',$this->sw)]);
        return false;
    }


    //根据手机号码返回运营商代码
    //移动： 1 ； 联通： 2； 电信： 3
    public function _mobile_type($mobile){
        $prev = substr($mobile,0,3);
        if(in_array($prev,$this->mobile_type[1])) return 1;
        if(in_array($prev,$this->mobile_type[2])) return 2;
        if(in_array($prev,$this->mobile_type[3])) return 3;

        return 0;   //表示不支持该手机号码充值
    }

    /**
     * subject: 支持的充值面值
     * api: /MobileRecharge/fare_list
     * author: Lazycat
     * day: 2017-05-09
     * content: fare为话费面值，flow为流量
     *
     * [字段名,类型,是否必传,说明]
     */
    public function fare_list(){
        $res = $this->_fare_list();
        $this->apiReturn($res);
    }
    public function _fare_list(){
        /*
        $fare    = eval(html_entity_decode(C('cfg.mobile_fare')['fare_list']));
        $flow    = eval(html_entity_decode(C('cfg.mobile_flow')['fare_list']));

        foreach($fare as $key => $val){
            $res['fare'][] = [
                'fare'  => $key,
                'price' => $val,
            ];
        }

        foreach($flow as $key => $val){
            $res['flow'][] = [
                'fare'  => $key,
                'price' => $val,
            ];
        }
        */

        $key = ['','chinamobile','unicom','telecom'];

        $list = M('mobile_fare')->cache(true,30)->where(['status' => 1])->field('type,operator,fare,price')->order('sort asc,id asc')->select();

        $res  = [];
        foreach($list as $val){
            if($val['type'] == 1){
                $res['fare'][$key[$val['operator']]][] = $val;
            }else{
                $res['flow'][$key[$val['operator']]][] = $val;
            }
        }

        return ['code' => 1,'data' => $res];
    }

    /**
     * 获取接口参数
     * @param int $recharge_type 1=话费充值，2=流量充值
     */
    private function _get_config($recharge_type=1){
        if($recharge_type == 1){
            if(C('cfg.mobile_fare')['status'] == 1){    //正式环境
                $config = [
                    'mb_max_fare'       => C('cfg.mobile_fare')['mb_max_fare'],
                    'user_max_fare'     => C('cfg.mobile_fare')['user_max_fare'],
                    'limit_time'        => C('cfg.mobile_fare')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_fare')['confirm_time'],
                    'ratio'             => C('cfg.mobile_fare')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_fare')['trade_url'],
                    'query_url'         => C('cfg.mobile_fare')['query_url'],
                    'mid'               => C('cfg.mobile_fare')['mid'],
                    'account'           => C('cfg.mobile_fare')['account'],
                    'key'               => C('cfg.mobile_fare')['key'],
                ];
            }else{  //测试环境
                $config = [
                    'mb_max_fare'       => C('cfg.mobile_fare')['mb_max_fare'],
                    'user_max_fare'     => C('cfg.mobile_fare')['user_max_fare'],
                    'limit_time'        => C('cfg.mobile_fare')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_fare')['confirm_time'],
                    'ratio'             => C('cfg.mobile_fare')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_fare')['test_trade_url'],
                    'query_url'         => C('cfg.mobile_fare')['test_query_url'],
                    'mid'               => C('cfg.mobile_fare')['test_mid'],
                    'account'           => C('cfg.mobile_fare')['test_account'],
                    'key'               => C('cfg.mobile_fare')['test_key'],
                ];
            }
        }else{
            if(C('cfg.mobile_flow')['status'] == 1){    //正式环境
                $config = [
                    'mb_max_flow'       => C('cfg.mobile_flow')['mb_max_flow'],
                    'user_max_flow'     => C('cfg.mobile_flow')['user_max_flow'],
                    'limit_time'        => C('cfg.mobile_flow')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_flow')['confirm_time'],
                    'ratio'             => C('cfg.mobile_flow')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_flow')['trade_url'],
                    'query_url'         => C('cfg.mobile_flow')['query_url'],
                    'mid'               => C('cfg.mobile_flow')['mid'],
                    'account'           => C('cfg.mobile_flow')['account'],
                    'key'               => C('cfg.mobile_flow')['key'],
                ];
            }else{  //测试环境
                $config = [
                    'mb_max_flow'       => C('cfg.mobile_flow')['mb_max_flow'],
                    'user_max_flow'     => C('cfg.mobile_flow')['user_max_flow'],
                    'limit_time'        => C('cfg.mobile_flow')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_flow')['confirm_time'],
                    'ratio'             => C('cfg.mobile_flow')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_flow')['test_trade_url'],
                    'query_url'         => C('cfg.mobile_flow')['test_query_url'],
                    'mid'               => C('cfg.mobile_flow')['test_mid'],
                    'account'           => C('cfg.mobile_flow')['test_account'],
                    'key'               => C('cfg.mobile_flow')['test_key'],
                ];
            }
        }

        return $config;
    }
}