<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家已发货商品退款管理
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-28
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class BuyerRefund2Controller extends OrdersController {
    protected $action_logs = array('add','cancel','send_express','edit');

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
     * subject: 商品退款
     * api: /BuyerRefund2/refund_goods
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
        $rs     = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,price,total_price_edit,score_ratio,score,refund_price,refund_score,refund_num,goods_id,attr_list_id,attr_name,goods_name,images,is_can_refund')->find();

        //取订单运费
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,goods_num,express_price_edit,goods_price_edit,receipt_time')->find();
        if($ors['status'] != 3) return ['code' => 0,'msg' => '该状态下不允许执行退款操作！'];


        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$param['orders_goods_id'],$rs,$ors,[1,2]);
        $rs  = array_merge($rs,$res);

        if($rs['can_refund'] != 1 && $rs['can_refund_express'] != 1){
            return ['code' => 0,'msg' => '不可再次发起退款申请！'];
        }elseif($res['can_refund'] != 1 && $rs['can_refund_express'] = 1){
            $rs['can_num']      = 0;
            $rs['can_price']    = 0;
            //$rs['tips']         = '累积申请退款金额（含已取消）等于订购的商品金额，不可再次发起退款申请！';
        }elseif($rs['can_refund'] != 1 && $rs['can_refund_express'] == 0){
            return ['code' => 0,'msg' => '不可再次发起退款申请！'];
        }


        return ['code' => 1,'data' => $rs];
    }

    /**
     * subject: 已发货商品退款
     * api: /BuyerRefund2/add
     * author: Lazycat
     * day: 2017-02-27
     * content: 退款金额和退费费两项必填一项
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: orders_goods_id,int,1,已购买的商品ID
     * param: num,int,0,退回商品数量
     * param: price,float,0,退款金额
     * param: express_price,float,0,退运费金额
     * param: type,int,1,退款类型，1＝退货并退款，2＝只退款
     * param: reason,string,1,退款原因
     * param: images,string,0,图片证据，多张用逗号隔开
     */
    public function add(){
        if($this->post['type'] == 1){
            $this->check($this->_field('express_price,images','openid,orders_goods_id,type,num,price,reason'));
            if($this->post['price'] <= 0 || $this->post['num'] <= 0){
                $this->apiReturn(['code' => 0,'msg' => '请正确输入退款金额及退回商品数量！']);
            }
        }else{
            $this->check($this->_field('express_price,images,price','openid,orders_goods_id,type,reason'));
            if($this->post['price'] <= 0 && $this->post['express_price'] <= 0){
                $this->apiReturn(['code' => 0,'msg' => '退款金额或退运费金额至少必填一项！']);
            }
        }

        $res = $this->_add($this->post);
        $this->apiReturn($res);
    }

    //兼容旧的退款流程，已发货商品退款中控制退款金额，无须要求退回数量
    public function _add($param){
        //判断是否还有商品可退
        //$tmp    = M('refund')->where(['uid' => $this->user['id'],'orders_goods_id' => $param['orders_goods_id'],'status' => ['not in','20']])->field('count(*) as count,sum(num) as num,sum(money) as money')->find();
        $rs     = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,price,score_ratio,total_price_edit,refund_price,refund_num,goods_id,is_can_refund')->find();

        //取订单运费
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,uid,seller_id,shop_id,goods_num,express_price_edit,goods_price_edit,receipt_time')->find();
        if($ors['status'] != 3) return ['code' => 0,'msg' => '该状态下不允许执行退款操作！'];


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


        if($param['type'] == 1){    //退货并退款
            if($param['num'] > $rs['can_num']) return ['code' => 0,'msg' => '退回数最不可超过'.$rs['can_num'].'件！'];
            if($param['price'] > $rs['can_price']) return ['code' => 0,'msg' => '退款金额不可超过￥'.$rs['can_price']];
            if($param['express_price'] > 0 && $param['express_price'] > $rs['can_express_price']) return ['code' => 0,'msg' => '运费最多可退￥'.$rs['can_express_price']];
        }else{
            $param['num'] = 0;
            if($param['price'] > 0 && $param['price'] > $rs['can_price']) return ['code' => 0,'msg' => '退款金额不可超过￥'.$rs['can_price']];
            if($param['express_price'] > 0 && $param['express_price'] > $rs['can_express_price']) return ['code' => 0,'msg' => '运费最多可退￥'.$rs['can_express_price']];
        }


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
            'money'             => $param['price'],
            'score'             => $param['price'] * $rs['score_ratio'] * 100,
            'refund_express'    => $param['express_price'],
            'type'              => $param['type'],
            'images'            => $param['images'],
            'reason'            => $param['reason'],
            'dotime'			=> date('Y-m-d H:i:s'),
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
        ];


        //日志数据
        $logs=[
            'r_no'          => $data['r_no'],
            'uid'           => $this->user['id'],
            'status'        => 1,
            'type'          => $data['type'],
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
     * subject: 取消退款
     * api: /BuyerRefund2/cancel
     * author: Lazycat
     * day: 2017-03-01
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

        if($rs['orders_status'] != 3) return ['code' => 0,'msg' => '已发货订单状态才可执行此操作！'];

        //退款订单已失效！
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '退款状态已失效！'];


        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 20,'dotime' => date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) {
            $msg = '更新退款记录失败！';
            goto error;
        }

        //$reason = '<p class="strong text_red">买家取消退款</a>';
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
     * subject: 退货寄回商品
     * api: /BuyerRefund2/send_express
     * author: Lazycat
     * day: 2017-03-01
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     * param: express_company_id,int,1,快递公司ID
     * param: express_code,string,1,快递运单号
     * param: remark,string,0,备注或留言
     */
    public function send_express(){
        $this->check($this->_field('remark','openid,r_no,express_company_id,express_code'));

        $res = $this->_send_express($this->post);
        $this->apiReturn($res);
    }

    public function _send_express($param){
        $rs = M('refund')->where(['uid' => $this->user['id'],'r_no' => $param['r_no']])->field('id,uid,shop_id,r_no,type,status,orders_status')->find();
        if($rs['status'] != 4 || $rs['orders_status'] != 3) return ['code' => 0,'msg' => '该状态下不充执行此操作，请重新刷退款订单查看状态！'];

        $ers = M('express_company')->where(['id' => $param['express_company_id']])->field('sub_name')->find();
        $str = $ers['sub_name'].'：'.$param['express_code'];

        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 5,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0])) {
            $msg = '更新退款订单状态失败！';
            goto error;
        }

        //$remark = '<p class="strong text_red">买家寄回商品</p>' . $str. ($param['remark'] ? '<div>'.$param['remark'].'</div>' : '');

        //日志数据
        $logs=[
            'r_id'                  => $rs['id'],
            'r_no'                  => $rs['r_no'],
            'uid'                   => $rs['uid'],
            'status'                => 5,
            'type'                  => $rs['type'],
            'express_company_id'    => $param['express_company_id'],
            'express_code'          => $param['express_code'],
            'remark'                => $param['remark'] ? $param['remark'] : '已寄回商品',
        ];

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $mst = '写入退款处理流程失败！';
            goto error;
        }

        $do->commit();

        //发短信通知
        /*
        $sms_data['mobile'] = M('shop')->where(['id' => $rs['shop_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']=$this->sms_tpl(15,
                    ['{nick}','{express_company}','{express_code}'],
                    [$this->user['nick'],$ers['sub_name'],$param['express_code']]
                );

            sms_send($sms_data);
        }
        */

        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败！'];

    }


    /**
     * subject: 退款详情（用于退修改退款）
     * api: /BuyerRefund2/refund_goods_view
     * author: Lazycat
     * day: 2017-03-01
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款编号
     */
    public function refund_goods_view(){
        $this->check('openid,r_no',false);

        $res = $this->_refund_goods_view($this->post);
        $this->apiReturn($res);
    }

    public function _refund_goods_view($param){
        $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('id,atime,r_no,s_id,s_no,status,orders_goods_id,num,money,refund_express,score,orders_status,type,images,reason,remark')->find();
        if(empty($rs)) return ['code' => 3,'msg' => '退款记录不存在！'];
        $rs['orders_goods'] = M('orders_goods')->where(['id' => $rs['orders_goods_id']])->field('id,atime,s_id,s_no,goods_id,images,attr_name,attr_list_id,price,num,weight,total_price_edit,total_weight,goods_name,refund_price,refund_num,is_can_refund')->find();
        $ors  = M('orders_shop')->where(['s_no' => $rs['s_no']])->field('s_no,status,goods_price_edit,receipt_time,goods_num,express_price_edit')->find();

        $total          = M('refund')->where(['orders_goods_id' => $rs['orders_goods_id'],'status' => ['neq','20'],'id' => ['neq',$rs['id']]])->field('count(*) as count,sum(num) as num,sum(money) as money')->find();
        $refund_express = M('refund')->where(['s_id' => $rs['s_id'],'status' => ['neq','20'],'id' => ['neq',$rs['id']]])->sum('refund_express');

        //可退金额
        if($rs['orders_goods']['is_can_refund'] == 1) {
            $rs['can_num']      = $rs['orders_goods']['num'] - $total['num']; //允许退回的数量
            $rs['unit_price']   = intval(($rs['orders_goods']['total_price_edit'] / $rs['orders_goods']['num']) * 100) / 100;
            $rs['can_price']    = number_formats($rs['orders_goods']['total_price_edit'] - $total['money'],2);
        }else{
            $rs['can_num']      = 0;
            $rs['unit_price']   = 0;
            $rs['can_price']    = 0;
        }

        $rs['can_express_price']    = number_formats($ors['express_price_edit'] - $refund_express,2); //允许退回运费

        if($rs['images'])           $rs['images_list'] = explode(',',$rs['images']);

        return ['code' => 1,'data' => $rs];
    }


    /**
     * subject: 已发货商品修改退款
     * api: /BuyerRefund2/edit
     * author: Lazycat
     * day: 2017-03-01
     * content: 退款金额和退费费两项必填一项
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: r_no,string,1,退款编号
     * param: num,int,0,退回商品数量
     * param: price,float,0,退款金额
     * param: express_price,float,0,退运费金额
     * param: type,int,1,退款类型，1＝退货并退款，2＝只退款
     * param: reason,string,1,退款原因
     * param: images,string,0,图片证据，多张用逗号隔开
     */
    public function edit(){
        if($this->post['type'] == 1){
            $this->check($this->_field('express_price,images','openid,r_no,type,num,price,reason'));
            if($this->post['price'] <= 0 || $this->post['num'] <= 0){
                $this->apiReturn(['code' => 0,'msg' => '请正确输入退款金额及退回商品数量！']);
            }
        }else{
            $this->check($this->_field('express_price,images,price','openid,r_no,type,reason'));
            if($this->post['price'] <= 0 && $this->post['express_price'] <= 0){
                $this->apiReturn(['code' => 0,'msg' => '退款金额或退运费金额至少必填一项！']);
            }
        }

        $res = $this->_edit($this->post);
        $this->apiReturn($res);
    }

    public function _edit($param){
        $do = D('Common/RefundRelation');
        $rs = $do->relation(['orders_goods','orders_shop'])->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('etime,ip',true)->find();

        if(empty($rs)) return ['code' => 0,'msg' => '退款记录不存在！'];
        if($rs['status'] != 2) return ['code' => 0,'msg' => '只有在被拒绝退款的状态下才可以修改！'];

        //当退款为只退运费时，判断是否还可以退货退款
        /*
        if($rs['num'] == 0 && $rs['money'] == 0){
            $res = A('Rest2/BuyerOrders')->_refund_and_service_check($rs['orders_status'],$rs['orders_goods_id'],null,null,[1,2]);
            if($res['can_refind'] != 1 && $param['price'] > 0 || $param['num'] > 0) {
                return ['code' => 0,'msg' => '累积退款申请金额已超过商品订购金额，不允许执行此修改申请！'];
            }
        }
        */


        $total          = M('refund')->where(['uid' => $this->user['id'],'orders_goods_id' => $rs['orders_goods_id'],'status' => ['not in','20'],'id' => ['neq',$rs['id']]])->field('count(*) as count,sum(num) as num,sum(money) as money')->find();
        $refund_express = M('refund')->where(['uid' => $this->user['id'],'s_id' => $rs['s_id'],'status' => ['not in','20'],'id' => ['neq',$rs['id']]])->sum('refund_express');

        //可退金额
        $rs['can_num']              = $rs['orders_goods']['num'] - $total['num']; //允许退回的数量
        $rs['unit_price']           = intval(($rs['orders_goods']['total_price_edit'] / $rs['orders_goods']['num']) * 100) / 100;
        $rs['can_price']            = number_formats($rs['orders_goods']['total_price_edit'] - $total['money'],2);
        $rs['can_express_price']    = number_formats($rs['orders_shop']['express_price_edit'] - $refund_express,2); //允许退回运费

        if($param['type'] == 1){    //退货并退款
            if($param['num'] > $rs['can_num']) return ['code' => 0,'msg' => '退回数最不可超过'.$rs['can_num'].'件！'];
            if($param['price'] > $rs['can_price']) return ['code' => 0,'msg' => '退款金额不可超过￥'.$rs['can_price']];
            if($param['express_price'] > 0 && $param['express_price'] > $rs['can_express_price']) return ['code' => 0,'msg' => '运费最多可退￥'.$rs['can_express_price']];
        }else{
            $param['num'] = 0;
            if($param['price'] > 0 && $param['price'] > $rs['can_price']) return ['code' => 0,'msg' => '退款金额不可超过￥'.$rs['can_price']];
            if($param['express_price'] > 0 && $param['express_price'] > $rs['can_express_price']) return ['code' => 0,'msg' => '运费最多可退￥'.$rs['can_express_price']];
        }

        $data = [
            'status'            => 3,
            'type'              => $param['type'],
            'num'               => $param['num'],
            'money'             => $param['price'],
            'score'             => $param['price'] * $rs['orders_goods']['score_ratio'] * 100,
            'refund_express'    => $param['express_price'],
            'dotime'			=> date('Y-m-d H:i:s'),
            'reason'            => $param['reason'],
            'images'            => $param['images'],
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
            'is_problem'        => 0,
        ];


        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $rs['uid'],
            'status'        => 3,
            'type'          => $param['type'],
            'remark'        => $param['reason'],
            'images'        => $param['images'] ? $param['images'] : '',
            'money'         => $data['money'],
            'refund_express'=> $data['refund_express'],
            'num'           => $data['num'],
            'score'         => $data['score'],
        ];

        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save($data)) {
            $msg = '更新退款记录失败！';
            goto error;
        }

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
		$msg_data = ['tpl_tag'=>'refund_edit','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'data' => ['r_no' => $param['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败'];

    }

}