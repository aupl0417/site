<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家未发货商品退款管理
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-24
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class BuyerRefundController extends OrdersController {
    protected $action_logs = array('add','cancel','logs_add');

    protected $refund_status = [ //退款状态
        1       => '买家申请退款',
        2       => '卖家拒绝退款',
        3       => '买家修改退款',
        4       => '卖家同意退款退货',
        5       => '买家寄回退货',
        10      => '等待仲裁',
        20      => '取消退款',
        100     => '退款成功'
    ];

    protected $refund_type = ['','退货并退款','只退款','只退运费']; //退款类型

    /**
     * subject: 退款列表
     * api: /BuyerRefund/refund_list
     * author: Lazycat
     * day: 2017-02-24
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: status,int|string,0,退款状态，同时获取多个状态用逗号隔开
     * param: pagesize,int,0,分页数量
     * param: p,int,0,获取第p页数据
     * param: orders_goods_id,int,0,退款商品ID
     * param: s_no,string,0,订单号，根据订单号筛选数据
     */
    public function refund_list(){
        $this->check($this->_field('status,p,pagesize,s_no','openid'),false);

        $res = $this->_refund_list($this->post);
        $this->apiReturn($res);
    }

    public function _refund_list($param){
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 15;

        $map['uid']             = $this->user['id'];
        $map['orders_status']   = ['lt', 4];
        if($param['status'] != '')  $map['status'] = ['in',$param['status']];
        if($param['orders_goods_id']) $map['orders_goods_id'] = $param['orders_goods_id'];
        if($param['s_no']) $map['s_no'] = $param['s_no'];

        $list = pagelist(array(
            'table'     		=> 'Common/RefundRelation',
            'do'        		=> 'D',
            'map'       		=> $map,
            'fields'            => 'id,atime,r_no,seller_id,shop_id,s_id,s_no,orders_goods_id,num,money,refund_express,orders_status,status,type',
            'order'     		=> 'id desc',
            'pagesize'  		=> $pagesize,
            'relation'  		=> ['seller','shop','orders_goods'],
            'p'                 => $param['p'],
        ));

        if($list['list']){
            foreach($list['list'] as $key => $val){
                $val['status_name']         = $this->refund_status[$val['status']];
                $val['type_name']           = $this->refund_type[$val['type']];
                $val['shop']['shop_url']    = shop_url($val['shop']['id'],$val['shop']['domain']);

                $val['refund_money']        = $val['money'] + $val['refund_express'];
                $list['list'][$key]         = $val;
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '暂无退款记录！'];
    }


    /**
     * subject: 退款详情
     * api: /BuyerRefund/view
     * author: Lazycat
     * day: 2017-02-24
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款编号
     */
    public function view(){
        $this->check('openid,r_no',false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param){
        $do = D('Common/RefundRelation');
        $rs = $do->relation(['seller','shop','orders_goods','logs','orders_shop'])->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('etime,ip',true)->find();
        if(empty($rs)) return ['code' => 3,'msg' => '退款记录不存在！'];


        if($rs['images']) $rs['images'] = explode(',',rtrim($rs['images'], ','));
        $rs['logs']                 = D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->field('etime,ip',true)->order('id desc')->select();
        //数据格式化
        foreach($rs['logs'] as $i => $val){
            if($val['remark']) $rs['logs'][$i]['remark'] = html_entity_decode($val['remark']);
            if($val['images']) $rs['logs'][$i]['images'] = explode(',',rtrim($val['images'], ','));
            if(!empty($val['express_company_id']) && !empty($val['express_code'])) {
                $rs['logs'][$i]['express_company'] = M('express_company')->cache(true)->where(['id' => $val['express_company_id']])->field('id,company,logo,sub_name,code')->find();

            }

            $rs['logs'][$i]['status_name']  = $this->refund_status[$val['status']];
        }

        $rs['shop']['shop_url'] = shop_url($rs['shop']['id'],$rs['shop']['domain']);
        $rs['type_name']	    = $this->refund_type[$rs['type']];
        $rs['status_name']	    = $this->refund_status[$rs['status']];
        $rs['refund_money']     = $rs['money'] + $rs['refund_express'];


        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($rs['orders_status'],$rs['orders_goods_id'],$rs['orders_goods'],$rs['orders_shop'],[1,2]);
        $rs  = array_merge($rs,$res);

        //计算订单下一步操作剩余时间
        $rs['limit_time']   = 0;
        $next_time          = strtotime($rs['next_time']);
        if($rs['orders_status'] == 2){  //未发货状态下
            switch($rs['status']){
                case 1: //申请退款
                        //$next_time = strtotime($rs['atime']) + C('cfg.orders')['refund_not_express'];
                        $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                        $rs['limit_time_name']  = '剩'.diff_time($next_time).'将自动退款';
                    break;
            }

        }elseif($rs['orders_status'] == 3){  //已发货状态下
            switch($rs['status']){
                case 1: //申请退款
                    //$next_time = strtotime($rs['atime']) + C('cfg.orders')['refund_express'];
                    $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                    $rs['limit_time_name']  = '剩'.diff_time($next_time).'将自动同意退款';
                    break;
                case 2: //卖家拒绝
                    //$next_time = strtotime($rs['dotime']) + C('cfg.orders')['refund_express'];
                    $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                    $rs['limit_time_name']  = '剩'.diff_time($next_time).'自动取消退款';
                    break;
                case 3: //买家修改退款
                    //$next_time = strtotime($rs['dotime']) + C('cfg.orders')['refund_express'];
                    $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                    $rs['limit_time_name']  = '剩'.diff_time($next_time).'将自动同意退款';
                    break;
                case 4: //买家寄回退货
                    //$next_time = strtotime($rs['dotime']) + C('cfg.orders')['refund_express'];
                    $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                    $rs['limit_time_name']  = '剩'.diff_time($next_time).'未寄回商品将自动取消退款';
                    break;
                case 5: //待卖家收货
                    //$next_time = strtotime($rs['dotime']) + C('cfg.orders')['confirm_orders'];
                    $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                    $rs['limit_time_name']  = '剩'.diff_time($next_time).'待卖家收货';
                    break;
            }

        }


        return ['code' => 1,'data' => $rs];

    }


    /**
     * subject: 商品退款
     * api: /BuyerRefund/refund_goods
     * author: Lazycat
     * day: 2017-02-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: orders_goods_id,int,1,已订购的商品ID
     */
    public function refund_goods(){
        $this->check('openid,orders_goods_id',false);

        $res = $this->_refund_goods($this->post);
        $this->apiReturn($res);
    }

    //未发货退款只能选择退回数量进行退款，不可自行填写金额
    public function _refund_goods($param){
        $rs     = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,price,total_price_edit,score_ratio,score,refund_price,refund_score,refund_num,goods_id,attr_list_id,attr_name,goods_name,images')->find();

        //取订单运费
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,goods_num,express_price_edit,goods_price_edit,receipt_time')->find();
        if($ors['status'] != 2) return ['code' => 0,'msg' => '该状态下不允许执行退款操作！'];


        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$param['orders_goods_id'],$rs,$ors,[1,2]);
        $rs  = array_merge($rs,$res);

        if($rs['can_refund'] != 1 && $rs['can_refund_express'] != 1){
            return ['code' => 0,'msg' => '不可再次发起退款申请！'];
        }elseif($res['can_refund'] != 1 && $rs['can_refund_express'] = 1){
            $rs['can_num']      = 0;
            $rs['can_price']    = 0;
            //$rs['tips']         = '累积申请退款金额（含已取消）等于订购的商品金额，不可再次发起退款申请！';
        }elseif($rs['can_refund'] != 1 && $rs['can_refund_express'] != 1){
            return ['code' => 0,'msg' => '不可再次发起退款申请！'];
        }

        return ['code' => 1,'data' => $rs];
    }

    /**
     * subject: 未发货商品退款
     * api: /BuyerRefund/add
     * author: Lazycat
     * day: 2017-02-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: orders_goods_id,int,1,已购买的商品ID
     * param: num,int,1,退回商品数量
     * param: express_price,float,0,退运费金额
     * param: reason,string,1,退款原因
     */
    public function add(){
        $this->check($this->_field('express_price','openid,num,orders_goods_id,reason'));

        if($this->post['num'] <= 0) $this->apiReturn(['code' => 0,'msg' => '请输入退款商品数量！']);

        $res = $this->_add($this->post);
        $this->apiReturn($res);
    }

    //兼容旧的退款流程
    public function _add($param){
        //判断是否还有商品可退
        $rs     = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,price,score_ratio,total_price_edit,refund_price,refund_num,goods_id')->find();

        //取订单运费
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,uid,seller_id,shop_id,goods_num,express_price_edit,goods_price_edit,receipt_time')->find();
        if($ors['status'] != 2) return ['code' => 0,'msg' => '该状态下不允许执行退款操作！'];


        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$param['orders_goods_id'],$rs,$ors,[1,2]);
        $rs  = array_merge($rs,$res);

        if($rs['can_refund'] != 1 && $rs['can_refund_express'] != 1){
            return ['code' => 0,'msg' => '不可再次发起退款申请！'];
        }elseif($res['can_refund'] != 1 && $rs['can_refund_express'] = 1){
            $rs['can_num']      = 0;
            $rs['can_price']    = 0;
            //$rs['tips']         = '累积申请退款金额（含已取消）等于订购的商品金额，不可再次发起退款申请！';
        }

        //超过最大可退数量
        if($param['num'] > $rs['can_num']) return ['code' => 0,'msg' => '最多只能退'.$rs['can_num'].'件！'];

        if($param['express_price'] > 0 && $param['express_price'] > $rs['can_express_price']) {
            return ['code' => 0,'msg' => '最多可退运费￥'.$rs['can_express_price']];
        }

        //当为最后一笔退款是一起退掉
        /*
        $anum = M('refund')->where(['s_id' => $ors['id'],'status' => ['not in','20']])->sum('num');
        if(($anum + $param['num']) == $ors['goods_num']){
            $param['refund_express'] = $ors['express_price_edit'] - $refund_express;
        }
        */

        $price      = $param['num'] * $rs['unit_price'];
        if($param['num'] == $rs['can_num'] && $price < $rs['can_price']) $price = $rs['can_price'];
        $score      = $rs['score_ratio'] * $price * 100;

        $data = [
            'r_no'              => $this->create_orderno('TK',$ors['uid']),
            'uid'               => $ors['uid'],
            'seller_id'         => $ors['seller_id'],
            'shop_id'           => $ors['shop_id'],
            's_id'              => $ors['id'],
            's_no'              => $ors['s_no'],
            'orders_status'     => $ors['status'],
            'status'            => 1,
            'orders_goods_id'   => $param['orders_goods_id'],
            'num'               => $param['num'],
            'money'             => $price,
            'score'             => $score,
            'refund_express'    => $param['express_price'] > 0 ? $param['express_price'] : 0,
            'type'              => 2,
            'reason'            => $param['reason'],
            'dotime'			=> date('Y-m-d H:i:s'),
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_not_express']),
        ];

        /*
        $param['reason']        =  $param['reason'].'<p class="strong text_red">买家申请退款</p>申请退款金额为<strong class="text_red">￥ ' . $price . ' </strong>元退货数量为<strong class="text_red"> ' . ($param['num'] ? $param['num'] : 0) . ' </strong>';
        if ($param['refund_express'] > 0) {  //如果有退运费则将运费写入
            $param['reason']        .=   '并申请了<strong class="text_red">￥ ' . $param['refund_express'] . ' </strong>元邮费退款';
        }
        */

        //日志数据
        $logs=[
            'r_no'          => $data['r_no'],
            'uid'           => $this->user['id'],
            'status'        => 1,
            'type'          => 2,
            'remark'        => $param['reason'],
            'money'         => $data['money'],
            'refund_express'=> $data['refund_express'],
            'num'           => $data['num'],
            'score'         => $data['score'],
        ];

        $do=M();
        $do->startTrans();
        //创建退款订单
        if(!$this->sw[] = D('Common/Refund')->create($data)){
            $msg = D('Common/Refund')->getError();
            goto error;
        }

        if(!$this->sw[]=D('Common/Refund')->add()) {
            $msg = '创建退款记录失败！';
            goto error;
        }

        //创建退款日志
        $logs['r_id']   = D('Common/Refund')->getLastInsID();
        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $msg = '创建退款日志失败！';
            goto error;
        }

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_apply','s_no'=>$ors['s_no'],'r_no'=>$data['r_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'data' => ['r_no' => $data['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '退款失败！'];

    }


    /**
     * subject: 某款商品的退款记录
     * api: /BuyerRefund/refund_goods_list
     * author: Lazycat
     * day: 2017-02-27
     * content: orders_goods_id 和 s_no 两项必须至少填一项
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: orders_goods_id,int,0,退款商品ID
     * param: s_no,string,0,订单号
     */
    public function refund_goods_list(){
        $this->check($this->_field('s_no,orders_goods_id','openid'),false);

        if(empty($this->post['s_no']) && empty($this->post['orders_goods_id'])) $this->apiReturn(['code' => 0,'msg' => '缺少参数s_no或orders_goods_id']);

        $res = $this->_refund_goods_list($this->post);
        $this->apiReturn($res);
    }

    public function _refund_goods_list($param){
        $map['uid']             = $this->user['id'];
        $map['orders_status']   = ['lt', 4];
        if($param['orders_goods_id']) $map['orders_goods_id'] = $param['orders_goods_id'];
        if($param['s_no']) $map['s_no'] = $param['s_no'];


        $list = D('Common/RefundRelation')->relation(['seller','shop','orders_goods'])->where($map)->field('id,atime,r_no,seller_id,shop_id,s_id,s_no,orders_goods_id,num,money,refund_express,orders_status,status,type')->order('id desc')->select();

        if($list){
            foreach($list as $key => $val){
                $val['status_name']         = $this->refund_status[$val['status']];
                $val['type_name']           = $this->refund_type[$val['type']];
                $val['shop']['shop_url']    = shop_url($val['shop']['id'],$val['shop']['domain']);

                $val['refund_money']        = $val['money'] + $val['refund_express'];
                $list[$key]                 = $val;
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '暂无退款记录！'];
    }


    /**
     * subject: 取消退款
     * api: /BuyerRefund/cancel
     * author: Lazycat
     * day: 2017-02-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function cancel(){
        $this->check('openid,r_no',false);

        $res = $this->_cancel($this->post);
        $this->apiReturn($res);
    }

    public function _cancel($param){
        $rs = M('refund')->where(['uid' => $this->user['id'],'r_no' => $param['r_no']])->field('id,uid,s_id,r_no,status,orders_status,type')->find();
        if(empty($rs)) return ['code' => 0,'msg' => '退款记录不存在！'];

        if($rs['orders_status'] != 2) return ['code' => 0,'msg' => '未发货订单状态才可执行此操作！'];

        //退款订单已失效！
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '退款状态已失效！'];


        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 20,'dotime' => date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) {
            $msg = '更新退款记录失败！';
            goto error;
        }

        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $rs['uid'],
            'status'        => 20,
            'type'          => $rs['type'],
            'remark'        => '买家取消退款', //买家取消退款！
        ];

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $msg = '创建退款处理记录失败！';
            goto error;
        }

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_cancel','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'msg' => '取消退款成功！'];

        error:
        $do->rollback();
        return ['code' => 1,'msg' => $msg ? $msg : '取消退款失败！'];
    }

    /**
     * subject: 退款协商详情
     * api: /BuyerRefund/logs
     * author: Lazycat
     * day: 2017-03-10
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     */
    public function logs(){
        $this->check('openid,r_no',false);

        $res = $this->_logs($this->post);
        $this->apiReturn($res);
    }
    public function _logs($param){
        $list = D('Common/RefundLogsRelation')->relation(true)->where(['r_no' => $param['r_no']])->field('etime,ip',true)->order('id desc')->select();

        if($list) {
            //数据格式化
            foreach ($list as $key => $val) {
                if ($val['remark']) $val['remark'] = html_entity_decode($val['remark']);
                if ($val['images']) $val['images'] = explode(',', rtrim($val['images'], ','));

                if (!empty($val['express_company_id']) && !empty($val['express_code'])) {
                    $val['express_company'] = M('express_company')->cache(true)->where(['id' => $val['express_company_id']])->field('id,company,logo,sub_name,code')->find();
                }

                $val['status_name'] = $this->refund_status[$val['status']];
                $list[$key]         = $val;
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }


    /**
     * subject: 添加退款留言
     * api: /BuyerRefund/logs_add
     * author: Lazycat
     * day: 2017-03-10
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     * param: remark,string,1,留言
     * param: images,string,0,凭证图片，多张用逗号隔开
     */
    public function logs_add(){
        $this->check($this->_field('images','openid,r_no,remark'));

        $res = $this->_logs_add($this->post);
        $this->apiReturn($res);
    }

    public function _logs_add($param){
        $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('id,uid,r_no,status,type')->find();
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '退款已关闭，不能发表留言！'];

        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $rs['uid'],
            'status'        => $rs['status'],
            'type'          => $rs['type'],
            'remark'        => $param['remark'],
            'images'        => $param['images'],
        ];

        if(!D('Common/RefundLogs')->create($logs)) return ['code' => 0,'msg' => D('Common/RefundLogs')->getError()];

        if($logs_id = D('Common/RefundLogs')->add()) return ['code' => 1,'data' => ['logs_id' => $logs_id]];

        return ['code' => 0,'msg' => '添加留言失败！'];
    }


    /**
     * subject: 读取订单中可申请退款的商品
     * api: /BuyerRefund/can_refund_goods
     * author: Lazycat
     * day: 2017-03-28
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     */
    public function can_refund_goods(){
        $this->check('openid,s_no');

        $res = $this->_can_refund_goods($this->post);
        $this->apiReturn($res);
    }

    public function _can_refund_goods($param){
        $ors = M('orders_shop')->where(['s_no' => $param['s_no'],'uid' => $this->user['id']])->field('id,status,s_no,goods_price_edit,express_price_edit,receipt_time')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if(!in_array($ors['status'],[2,3])) return ['code' => 0,'msg' => '只有已付款或已发货的订单才可以执行此操作！'];

        $orders_goods = M('orders_goods')->where(['s_id' => $ors['id']])->field('id,attr_list_id,goods_id,attr_name,price,num,weight,total_price_edit,total_weight,goods_name,images,refund_num,refund_price')->select();
        //$list = array();
        foreach($orders_goods as $key => $val){
            $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$val['id'],$val,$ors,[1,2]);

            //if($res['can_refund'] == 1){
                //$list[] = array_merge($val,$res);
            //}
            $orders_goods[$key] = array_merge($val,$res);
        }

        if($orders_goods){
            return ['code' => 1,'data' => $orders_goods];
        }

        return ['code' => 3];
    }
}