<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 售后管理
 * ----------------------------------------------------------
 * Author:lizuheng <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-24
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class ServiceController extends OrdersController {
    protected $action_logs      = array('appeal','edit','add','cancel','buyer_send_express','buyer_receive','logs_add');
    protected $service_status   = [ //售后状态
        1       => '买家申请售后',
        2       => '卖家拒绝售后',
        3       => '卖家同意售后',
        4       => '买家寄出商品',
        5       => '卖家收到商品',
        6       => '卖家寄回商品',
        10      => '等待仲裁',
        20      => '取消售后',
        100     => '售后完成'
    ];

    protected $service_type_name = ['','换货/维修','维修']; //售后类型



    /**
     * subject: 商品申请售后 - 读取商品资料
     * api: /Service/service_goods
     * author: Lazycat
     * day: 2017-03-25
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: orders_goods_id,int,1,已订购的商品ID
     */
    public function service_goods(){
        $this->check('openid,orders_goods_id',false);

        $res = $this->_service_goods($this->post);
        $this->apiReturn($res);
    }

    public function _service_goods($param){
        $rs  = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,price,total_price_edit,score_ratio,score,refund_price,refund_score,refund_num,service_num,goods_service_days,goods_id,attr_list_id,attr_name,goods_name,images')->find();
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,goods_num,receipt_time')->find();
        if(!in_array($ors['status'],[4,5])) return ['code' => 0,'msg' => '只有已收货的订单方可申请售后！'];

        $rs['service_eday'] = date('Y-m-d H:i:s', strtotime($ors['receipt_time']) + $rs['goods_service_days'] * 86400);   //售后截止时间
        if($rs['service_eday'] < date('Y-m-d H:i:s')){
            return ['code' => 0,'msg' => '申请售后失败，售后时间截止至'.$rs['service_eday']];
        }

        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$param['orders_goods_id'],$rs,$ors,3);
        $rs  = array_merge($rs,$res);

        if($rs['can_service'] != 1) return ['code' => 0,'msg' => '不允许再次发起售后申请！'];

        return ['code' => 1,'data' => $rs];
    }



    /**
     * subject: 保存售后申请
     * api: /Service/add
     * author: Lazycat
     * day: 2017-03-25
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: orders_goods_id,int,1,已购买的商品ID
     * param: num,int,0,售后商品数量
     * param: reason,string,1,售后描述或备注
     * param: images,string,0,图片证据，多张用逗号隔开
     */
    public function add(){
        $this->check($this->_field('images','openid,orders_goods_id,num,reason'));

        $res = $this->_add($this->post);
        $this->apiReturn($res);
    }

    //兼容旧的退款流程，已发货商品退款中控制退款金额，无须要求退回数量
    public function _add($param){
        $rs  = M('orders_goods')->where(['uid' => $this->user['id'],'id' => $param['orders_goods_id']])->field('id,s_id,s_no,num,service_num,goods_service_days,total_price_edit,refund_num,refund_price')->find();
        $ors = M('orders_shop')->where(['id' => $rs['s_id']])->field('id,s_no,status,uid,seller_id,shop_id,goods_num,receipt_time')->find();
        if(!in_array($ors['status'],[4,5])) return ['code' => 0,'msg' => '只有已收货的订单方可申请售后！'];

        $rs['service_eday'] = date('Y-m-d H:i:s', strtotime($ors['receipt_time']) + $rs['goods_service_days'] * 86400);   //售后截止时间
        if($rs['service_eday'] < date('Y-m-d H:i:s')){
            return ['code' => 0,'msg' => '申请售后失败，售后时间截止至'.$rs['service_eday']];
        }

        //判断是否可以再次发起退款申请
        $res = A('Rest2/BuyerOrders')->_refund_and_service_check($ors['status'],$param['orders_goods_id'],$rs,$ors,3);
        $rs  = array_merge($rs,$res);

        if($rs['can_service'] != 1) return ['code' => 0,'msg' => '不允许再次发起售后申请！'];
        if($param['num'] > $rs['can_service_num']) return ['code' => 0,'msg' => '可售后商品数量最多'.$rs['can_service_num'].'件！'];

        $data = [
            'r_no'              => $this->create_orderno('SH',$ors['uid']),
            'uid'               => $ors['uid'],
            'seller_id'         => $ors['seller_id'],
            'shop_id'           => $ors['shop_id'],
            's_id'              => $ors['id'],
            's_no'              => $ors['s_no'],
            'orders_status'     => $ors['status'],
            'status'            => 1,
            'orders_goods_id'   => $param['orders_goods_id'],
            'num'               => $param['num'],
            'money'             => 0,
            'score'             => 0,
            'refund_express'    => 0,
            'type'              => 1,
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
            'type'          => 1,
            'remark'        => $param['reason'],
            'money'         => 0,
            'refund_express'=> 0,
            'num'           => $data['num'],
            'score'         => 0,
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
            $msg = '创建售后日志失败！';
            goto error;
        }

        $do->commit();
		
		//发送售后消息
		$msg_data = ['tpl_tag'=>'service_apply','s_no'=>$ors['s_no'],'r_no'=>$data['r_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'data' => ['r_no' => $data['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '申请售后失败！'];

    }


    /**
     * subject: 某款商品的售后记录
     * api: /Service/service_goods_list
     * author: Lazycat
     * day: 2017-03-25
     * content: orders_goods_id 和 s_no 两项必须至少填一项
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: orders_goods_id,int,0,退款商品ID
     * param: s_no,string,0,订单号
     */
    public function service_goods_list(){
        $this->check($this->_field('s_no,orders_goods_id','openid'),false);

        if(empty($this->post['s_no']) && empty($this->post['orders_goods_id'])) $this->apiReturn(['code' => 0,'msg' => '缺少参数s_no或orders_goods_id']);

        $res = $this->_service_goods_list($this->post);
        $this->apiReturn($res);
    }

    public function _service_goods_list($param){
        $map['uid']             = $this->user['id'];
        $map['orders_status']   = ['in', '4,5'];
        if($param['orders_goods_id']) $map['orders_goods_id'] = $param['orders_goods_id'];
        if($param['s_no']) $map['s_no'] = $param['s_no'];


        $list = D('Common/RefundRelation')->relation(['seller','shop','orders_goods'])->where($map)->field('id,atime,r_no,seller_id,shop_id,s_id,s_no,orders_goods_id,num,money,refund_express,orders_status,status,type')->order('id desc')->select();

        if($list){
            foreach($list as $key => $val){
                $val['status_name']         = $this->service_status[$val['status']];
                $val['type_name']           = $this->service_type_name[$val['type']];
                $val['shop']['shop_url']    = shop_url($val['shop']['id'],$val['shop']['domain']);
                $list[$key]                 = $val;
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '暂无售后记录！'];
    }



    /**
     * subject: 修改售后 - 获取待修改的售后详情
     * api: /Service/service_goods_view
     * author: Lazycat
     * day: 2017-03-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: r_no,int,1,售后编号
     */
    public function service_goods_view(){
        $this->check('openid,r_no',false);

        $res = $this->_service_goods_view($this->post);
        $this->apiReturn($res);
    }

    public function _service_goods_view($param){
        $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id'],'orders_status' => ['in','4,5']])->field('atime,etime',true)->find();
        if(empty($rs)) return ['code' => 0,'msg' => '售后记录不存在！'];

        $rs['orders_goods']     = M('orders_goods')->where(['id' => $rs['orders_goods_id']])->field('id,s_id,s_no,num,price,total_price_edit,score_ratio,score,refund_price,refund_score,refund_num,service_num,goods_service_days,goods_id,attr_list_id,attr_name,goods_name,images')->find();
        $num = M('refund')->where(['orders_goods_id' => $rs['orders_goods_id'],'orders_status' => ['in','4,5'],'id' => ['neq',$rs['id']],'status' => ['neq',20]])->sum('num');
        $rs['can_service_num'] = $rs['orders_goods']['num'] - $rs['orders_goods']['refund_num'] - $num;
        if($rs['images']) $rs['images_list'] = explode(',',$rs['images']);

        return ['code' => 1,'data' => $rs];
    }


    /**
     * subject: 修改售后申请
     * api: /Service/edit
     * author: layzcat
     * day: 2017-03-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,售后单号
     * param: reason,string,1,原因
     * param: num,string,1,申请数量
     * param: images,string,0,图片
     */
    public function edit(){
        $this->check($this->_field('images','openid,r_no,num,reason'));

        $res = $this->_edit($this->post);
        $this->apiReturn($res);
    }

    public function _edit($param){
        $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id'],'orders_status' => ['in','4,5']])->field('atime,etime',true)->find();
        if(empty($rs)) return ['code' => 0,'msg' => '售后记录不存在！'];
        if($rs['status'] != 2) return ['code' => 0,'msg' => '只有被拒绝售后申请后才可以重新编辑！'];

        $rs['orders_goods'] = M('orders_goods')->where(['id' => $rs['orders_goods_id']])->field('id,s_id,s_no,num,refund_num')->find();
        $num = M('refund')->where(['orders_goods_id' => $rs['orders_goods_id'],'orders_status' => ['in','4,5'],'id' => ['neq',$rs['id']],'status' => ['neq',20]])->sum('num');
        $rs['can_service_num'] = $rs['orders_goods']['num'] - $rs['orders_goods']['refund_num'] - $num;

        if($param['num'] > $rs['can_service_num']) return ['code' => 0,'msg' => '可售后商品数量最多'.$rs['can_service_num'].'件！'];

        $param['reason'] = '买家修改售后申请；'.$param['reason'];
        $data = [
            'status'            => 1,
            'type'              => 1,
            'num'               => $param['num'],
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
            'status'        => 1,
            'type'          => 1,
            'remark'        => $param['reason'],
            'images'        => $param['images'] ? $param['images'] : '',
            'num'           => $data['num'],
        ];

        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save($data)) {
            $msg = '更新售后记录失败！';
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $msg = '创建售后处理记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => ['r_no' => $param['r_no']]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '操作失败'];

    }





	/**
     * subject: 售后订单列表
     * api: /Service/service_list
     * author: lizuheng
     * day: 2017-02-24
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: status,int|string,0,售后状态，同时获取多个状态用逗号隔开
     * param: pagesize,int,0,分页数量
     * param: p,int,0,获取第p页数据
     * param: orders_goods_id,int,0,售后商品ID
     * param: s_no,string,0,订单号，根据订单号筛选数据
     */
    public function service_list(){
		$this->check('openid',false);
		
		$res = $this->_service_list($this->post);
        $this->apiReturn($res);		
	}
	
    public function _service_list($param){
        $pagesize               = $param['pagesize'] ? $param['pagesize'] : 15;
		$map['uid']             = $this->user['id'];
        $map['orders_status']   = ['in','4,5'];
        if($param['status'] != '')  $map['status'] = ['in',$param['status']];
        if($param['orders_goods_id']) $map['orders_goods_id'] = $param['orders_goods_id'];
        if($param['s_no']) $map['s_no'] = $param['s_no'];

        if (!empty($param['eday']) || !empty($param['eday'])) {
            if (empty($param['eday'])) {
                $map['atime'] = ['lt', $param['eday']];
            } elseif (empty($param['sday'])) {
                $map['atime'] = ['gt', $param['sday']];
            } else {
                $map['atime'] = ['between', $param['eday'] . ',' . $param['sday']];
            }
        }

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
                $val['status_name']         = $this->service_status[$val['status']];
                $val['type_name']           = $this->service_type_name[$val['type']];
                $val['shop']['shop_url']    = shop_url($val['shop']['id'],$val['shop']['domain']);

                $list['list'][$key]         = $val;
            }

            $list['count']  = $this->_buyer_other_count($this->user['id']);

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '暂无售后记录！'];
	}	

	/**
     * subject: 售后详情
     * api: /Service/view
     * author: Lazycat
     * day: 2017-03-27
     *
     * [字段名,类型,是否必传,说明]
	 * param: r_no,string,1,售后编号
     * param: openid,string,1,用户openid
     */
    public function view() {
		$field = 'r_no,sign,openid';
		$this->check($field,false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }
	public function _view($param){
        $do = D('Common/RefundRelation');
        $rs = $do->relation(['seller','shop','orders_goods','logs','orders_shop'])->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('etime,ip',true)->find();
        if(empty($rs)) return ['code' => 3,'msg' => '售后记录不存在！'];
		
		if($rs['images'] && $rs['images'] != ""){
			$rs['images'] = explode(',',rtrim($rs['images'], ','));
		}else{
			$rs['images'] = null;
		}
		
        $rs['status_name']  = $this->service_status[$rs['status']];
        $rs['type_name']    = $this->service_type_name[$rs['type']];
        $rs['logs'] = D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->field('etime,ip',true)->order('id desc')->select();

        //数据格式化
        foreach($rs['logs'] as $i => $val){
            if($val['remark']) $rs['logs'][$i]['remark'] = html_entity_decode($val['remark']);
			if($val['images'] && $val['images'] != ""){
				$rs['logs'][$i]['images'] = explode(',',rtrim($val['images'], ','));
			}else{
				$rs['logs'][$i]['images'] = null;
			}
            if(!empty($val['express_company_id']) && !empty($val['express_code'])) {
                $rs['logs'][$i]['express_company'] = M('express_company')->cache(true)->where(['id' => $val['express_company_id']])->field('id,company,logo,sub_name,code')->find();

            }
            $rs['logs'][$i]['status_name']  = $this->service_status[$val['status']];
        }

        if($rs['images']) $rs['images'] = explode(',',rtrim($rs['images'], ','));


		//计算订单下一步操作剩余时间
        $rs['limit_time']   = 0;
        $next_time          = strtotime($rs['next_time']);
		switch($rs['status']){
			case 1: //申请售后
				//$next_time = strtotime($rs['atime']) + C('cfg.orders')['refund_express'];
				$rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
				$rs['limit_time_name']  = '剩'.diff_time($next_time).'将自动取消售后';
				break;
			case 2: //卖家拒绝
				//$next_time = strtotime($rs['dotime']) + C('cfg.orders')['refund_express'];
				$rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
				$rs['limit_time_name']  = '剩'.diff_time($next_time).'将自动取消售后';
				break;
			case 3: //卖家同意售后
				//$next_time = strtotime($rs['dotime']) + C('cfg.orders')['refund_express'];
				$rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
				$rs['limit_time_name']  = '剩'.diff_time($next_time).'未寄出商品将自动取消售后';
				break;
			case 4: //买家寄回售后
				//$next_time = strtotime($rs['dotime']) + C('cfg.orders')['confirm_orders'];
				$rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
				$rs['limit_time_name']  = '剩'.diff_time($next_time).'待卖家收货';
				break;
			case 5: //卖家收到商品
				$rs['limit_time_name']  = '卖家已收到商品，售后服务进行中';
				break;
			case 6: //卖家寄出商品
				//$next_time = strtotime($rs['dotime']) + C('cfg.orders')['confirm_orders'];
				$rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
				$rs['limit_time_name']  = '剩'.diff_time($next_time).'待买家收货';
				break;
            case 20:
                $rs['limit_time_name']  = '买家取消售后申请';
                break;
            case 10:
                $rs['limit_time_name']  = '买家发起申诉';
                break;
            case 100:
                $rs['limit_time_name']  = '买家已收到换货/维修的商品，售后完成';
                break;
		}
		return ['code' => 1,'data' => $rs];
    }	
	
	/**
     * subject: 取消售后
     * api: /Service/cancel
     * author: lizuheng
     * day: 2017-02-25
     *
     * [字段名,类型,是否必传,说明]
	 * param: r_no,string,1,售后流水号
	 * param: openid,string,1,用户openid
     */
    public function cancel() {
		$this->check('r_no,openid');

        $res = $this->_cancel($this->post);
        $this->apiReturn($res);
    }
	public function _cancel($param) {
        $rs = M('refund')->where(['uid' => $this->user['id'],'r_no' => $param['r_no'],'orders_status' => ['in','4,5']])->field('id,uid,s_id,r_no,status,orders_status,type')->find();
        if(empty($rs)) return ['code' => 0,'msg' => '售后记录不存在！'];

        //售后状态已失效！
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '售后状态已失效！'];


        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 20,'dotime' => date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) {
            $msg = '更新售后记录失败！';
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
            'remark'        => '买家取消售后申请', //买家取消退款！
        ];

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $msg = '创建售后处理记录失败！';
            goto error;
        }

        $do->commit();
		
		//发送售后消息
		$msg_data = ['tpl_tag'=>'service_cancel','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'msg' => '取消售后成功！'];

        error:
        $do->rollback();
        return ['code' => 1,'msg' => $msg ? $msg : '取消售后失败！'];
    }


	/**
     * subject: 买家收到已售后完成的商品
     * api: /Service/accept
     * author: lizuheng
     * day: 2017-02-25
     *
     * [字段名,类型,是否必传,说明]
	 * param: r_no,string,1,退款流水号
	 * param: pay_password,string,1,安全密码
	 * param: openid,string,1,用户openid
     */
    public function buyer_receive() {
		$this->check('pay_password,r_no,openid');

        $res = $this->_check_pay_password($this->post['pay_password']);
        if($res['code'] != 1) $this->apiReturn($res);

        $res = $this->_buyer_receive($this->post);
        $this->apiReturn($res);
    }
	public function _buyer_receive($param) {
        $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id'],'orders_status' => ['in','4,5']])->field('id,r_no,uid,status,type')->find();
        if(empty($rs)) return ['code' => 0,'msg' => '找不到售后记录！'];
        if($rs['status'] != 6) return ['code' => 0,'msg' => '只有卖家完成售后服务并寄回商品后方可执行确认收货！'];

        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 100,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) {
            $msg = '更新售后记录失败！';
            goto error;
        }

        //$reason = '<p class="strong text_red">买家取消退款</a>';
        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $rs['uid'],
            'status'        => 100,
            'type'          => $rs['type'],
            'remark'        => '买家已收到商品且无异议，售后完成',
        ];

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $msg = '创建售后处理记录失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'msg' => '操作成功！'];

        error:
        $do->rollback();
        return ['code' => 1,'msg' => $msg ? $msg : '操作失败！'];
    }


	/**
     * subject: 买家寄出商品
     * api: /Service/buyer_send_express
     * author: lizuheng
     * day: 2017-03-27
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,售后编号
     * param: express_company_id,int,1,快递公司ID
     * param: express_code,string,1,快递运单号
     * param: remark,string,0,备注或留言
     */
	public function buyer_send_express(){
        $this->check($this->_field('remark','openid,r_no,express_company_id,express_code'));

        $res = $this->_buyer_send_express($this->post);
        $this->apiReturn($res);			   
	}
	public function _buyer_send_express($param){
	    $rs = M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->user['id']])->field('id,s_id,s_no,r_no,uid,type,status,orders_status')->find();
        if($rs['status'] !=  3) return ['code' => 0,'msg' => '只有卖家同意售后的状态下方可寄出商品！'];
        if(!in_array($rs['orders_status'],[4,5])) return ['code' => 0,'msg' => '订单状态错误！'];

        //获取买家收货地址
        $oid            = M('orders_shop')->cache(true)->where(['id' => $rs['s_id']])->getField('o_id');
        $address        = M('orders')->cache(true)->where(['id' => $oid])->field('province,city,district,town,street,linkname,tel,mobile,postcode')->find();
        $area           = $this->cache_table('area');
        $address_str    = $address['linkname'].'，'.$address['mobile'].($address['tel'] ? '，'.$address['tel'] : '').'，'.$area[$address['province']].' '.$area[$address['city']].' '.$area[$address['district']].' '.$area[$address['town']].' '.$address['street'].($address['postcode'] ? '('.$address['postcode'].')' : '');


        $ers = M('express_company')->where(['id' => $param['express_company_id']])->field('sub_name')->find();
        $str = $ers['sub_name'].'：'.$param['express_code'];

        $do=M();
        $do->startTrans();

        if(!$this->sw[] = M('refund')->where(['id' => $rs['id']])->save(['status' => 4,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0])) {
            $msg = '更新售后记录状态失败！';
            goto error;
        }

        //日志数据
        $logs=[
            'r_id'                  => $rs['id'],
            'r_no'                  => $rs['r_no'],
            'uid'                   => $rs['uid'],
            'status'                => 4,
            'type'                  => $rs['type'],
            'express_company_id'    => $param['express_company_id'],
            'express_code'          => $param['express_code'],
            'address'               => $address_str,
            'remark'                => $param['remark'] ? $param['remark'] : '买家已寄出商品',
        ];

        if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
            $msg = D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/RefundLogs')->add()) {
            $mst = '写入售后处理流程失败！';
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
     * subject: 申诉
     * api: /Service/appeal
     * author: lizuheng
     * day: 2017-02-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: r_no,string,1,退款单号
	 * param: remark,string,1,备注
	 * param: images,string,0,图片
     */
	public function appeal(){
		$field = 'openid,s_no,r_no,remark,sign';		
        $this->check($field);

        $res = $this->_appeal($this->post);
        $this->apiReturn($res);			   
	}
	public function _appeal($param){
        $data   =   ['uid' => $this->user['id'], 'check_type' => 2];
        $res    =   (new \Common\Controller\AppealController(array_merge($data, $param)))->run();
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res];
		}else{
			return ['code' => 0,'msg' => $res['msg']];
		}		   
	}


	/**
     * subject: 售后协商详情
     * api: /Service/logs
     * author: lizuheng
     * day: 2017-03-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,售后编号
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

                $val['status_name'] = $this->service_status[$val['status']];
                $list[$key]         = $val;
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }
	/**
     * subject: 添加售后留言
     * api: /Service/logs_add
     * author: lizuheng
     * day: 2017-03-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,售后编号
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
        if(in_array($rs['status'],[20,100])) return ['code' => 0,'msg' => '订单已关闭，不能添加留言！'];

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
}