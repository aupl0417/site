<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家订单管理接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-20
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class BuyerOrdersController extends OrdersController {
    protected $action_logs = array('rate_save','close');

    /**
     * subject: 买家订单列表
     * api: /BuyerOrders/orders
     * author: Lazycat
     * day: 2017-02-20
     * content: 订单状态：0=>'已删除' 1=>'已拍下' 2=>'已付款' 3=>'已发货' 4=>'已收货' 5=>'已评价' 6=>'已归档' 10=>'已关闭' 11=>'退款完成'
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     * param: status,int,0,订单状态
     */
    public function orders(){
        $this->check($this->_field('q,p,pagesize,o_no,s_no','openid'),false);

        $res = $this->_orders($this->post);
        $this->apiReturn($res);
    }

    public function _orders($param){
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 12;

        $map['uid']     = $this->user['id'];
        if($param['status'] !='') {
            $map['status'] = ['in',$param['status']];
            if($param['status'] == 4){
                $map['_string'] = 'refund_price < goods_price_edit';
            }
        }

        $pagelist = pagelist(array(
            'table'     => 'Common/OrdersShopBuyerRelation',
            'do'        => 'D',
            'map'       => $map,
            //'fields'    => 'id,atime,status,terminal,o_id,o_no,s_no,shop_id,uid,seller_id,goods_price_edit,coupon_price,express_price_edit,total_price,pay_price,pay_type,pay_time,express_type,express_time,receipt_time,goods_num,rate_time,score,inventory_type,',
            'pagesize'  => $pagesize,
            'p'         => $param['p'],
            'order'     => $order,
            'relation'  => true,
        ));

        if($pagelist['list']){
            $area = $this->cache_table('area');
            foreach($pagelist['list'] as $key=>$val){
				$val['shop']['shop_url']         = shop_url($val['shop']['id'],$val['shop']['domain']);
                $val['status_name']              = $this->orders_status[$val['status']];
                $val['orders']['province_name']  = $area[$val['orders']['province']];
                $val['orders']['city_name']      = $area[$val['orders']['city']];
                $val['orders']['district_name']  = $area[$val['orders']['district']];
                $val['orders']['town_name']      = $area[$val['orders']['town']];
                $val['pay_typename']             = $pay_typename[$val['pay_type']];

                //如果商品退完只剩运费是不可以评价的
                if($val['status'] == 4 && $val['goods_price_edit'] > $val['refund_price']){
                    $val['is_rate']     = 1;
                }else $val['is_rate']   = 0;

                $val['refund_in']   = 0;    //是否有退款中
                $val['service_in']  = 0;    //是否有售后中

                if(in_array($val['status'],[2,3])) {
                    $tmp = M('refund')->where(['s_id' => $val['id'], 'status' => ['not in', '20,100'], 'orders_status' => ['in', '2,3']])->count();
                    if ($tmp > 0) $val['refund_in'] = 1;
                }

                if(in_array($val['status'],[4,5])){
                    $tmp = M('refund')->where(['s_id' => $val['id'], 'status' => ['not in', '20,100'], 'orders_status' => ['in', '4,5']])->count();
                    if ($tmp > 0) $val['service_in'] = 1;
                }

                //如果使用官方优惠券，则不支持唐宝支付
                if($val['status'] == 1 && $val['coupon_id']){
                    $val['is_tangbao_pay']  = 1;
                    if(M('coupon')->where(['type' => 2,'id' => ['in',$val['coupon_id']]])->count() > 0) $val['is_tangbao_pay'] = 0;
                }

                $pagelist['list'][$key] = $val;
            }
			
            $pagelist['count']  = $this->_buyer_orders_count($this->user['id']);
            return ['code' => 1,'data' => $pagelist];
        }

        return ['code' => 3];
    }


    /**
     * subject: 买家关闭订单
     * api: /BuyerOrders/close
     * author: Lazycat
     * day: 2017-02-20
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     * param: reason,string,1,关闭原因
     */
    public function close(){
        $this->check('openid,s_no,reason');

        $res = $this->_close($this->post);
        $this->apiReturn($res);
    }

    public function _close($param){
        $rs = M('orders_shop')->where(['s_no' => $param['s_no'],'uid' => $this->user['id']])->field('id,s_no,o_id,o_no,status,goods_num,pay_price,score')->find();
        if(empty($rs)) return ['code' => 0,'msg' => '订单不存在！'];
        if($rs['status'] != 1) return ['code' => 0,'msg' => '错误的订单状态！'];

        //订单正在支付流程中时不可以关闭
        $tmp = S('paying_'.$param['s_no']);
        if($tmp) return ['code' => 0,'msg' => '订单正在支付流程中，请稍候后再试！'];

        $do=M();
        $do->startTrans();

        //订单日志
        $logs_data=array(
            'o_id'      => $rs['o_id'],
            'o_no'      => $rs['o_no'],
            's_id'      => $rs['id'],
            's_no'      => $rs['s_no'],
            'status'    => 10,
            'remark'    => '买家关闭订单',
            'reason'    => $param['reason'],
            'is_sys'    => $param['is_sys'] ? $param['is_sys'] : 0,
        );

        if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
            $msg = D('Common/OrdersLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/OrdersLogs')->add()){
            $msg = '添加订单日志失败！';
            goto error;
        }

        //更新商家订单
        if(!$this->sw[] = M('orders_shop')->where(['id' => $rs['id'],'status' => 1])->save(['status' => 10,'close_time' => date('Y-m-d H:i:s')])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        //获取合并订单
        $ors = M('orders')->where(['id' => $rs['o_id']])->field('shop_num')->find();
        if($ors['shop_num'] > 1){
            $sql = 'update '.C('DB_PREFIX').'orders set shop_num=shop_num-1,goods_num=goods_num-'.$rs['goods_num'].',pay_price=pay_price-'.$rs['pay_price'].',score=score-'.$rs['score'].',status=10,close_time=now() where id = '.$rs['o_id'];
            if(!$do->execute($sql)){
                $msg = '更新合并订单状态失败！';
                goto error;
            }
        }else{
            if(!$this->sw[] = M('orders')->where(['id' => $rs['o_id']])->save(['status' => 10,'close_time' => date('Y-m-d H:i:s')])){
                $msg = '更新合并订单状态失败！';
                goto error;
            }
        }

        $do->commit();
        return ['code' => 1,'msg' => '关闭订单成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '关闭订单失败！'];
    }



    /**
     * subject: 订单详情
     * api: /BuyerOrders/view
     * author: Lazycat
     * day: 2017-02-20
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     * param: is_love,int,0,返回可能想买的商品
     */
    public function view(){
        $this->check('openid,s_no',false);
        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param){
        $rs = D('Common/OrdersShopBuyerRelation')->relation(true)->where(['uid' => $this->user['id'],'s_no' => $param['s_no']])->field('etime,ip',true)->find();
        if(empty($rs)) return ['code' => 3];

        $rs['status_name']          = $this->orders_status[$rs['status']];
        $rs['shop']['shop_url']     = shop_url($rs['shop']['id'],$rs['shop']['domain']);
        $rs['express_type_name']    = $rs['express_type'] == 2 ? 'EMS' : '快递';

        $area = $this->cache_table('area');
        $rs['orders']['province_name']  = $area[$rs['orders']['province']];
        $rs['orders']['city_name']      = $area[$rs['orders']['city']];
        $rs['orders']['district_name']  = $area[$rs['orders']['district']];
        $rs['orders']['town_name']      = $area[$rs['orders']['town']];
        $rs['score_type']               = $rs['orders_goods'][0]['score_type'];
        $rs['express_company']          = '';
        if($rs['express_company_id']){
            $rs['express_company'] = M('express_company')->cache(true)->where(['id' => $rs['express_company_id']])->field('id,company,logo,sub_name,code')->find();
        }
		
		$rs['strtotime'] = strtotime($rs['receipt_time']);
        //商品是否存在退款记录
        $goods_ids = array();
        foreach($rs['orders_goods'] as $key => $val){
            //用于获取您可能喜欢的商品
            if(!in_array($val['goods_id'],$goods_ids)) $goods_ids[]= $val['goods_id'];

            //退款及售后检测
            $tmp = $this->_refund_and_service_check($rs['status'],$val['id'],$val,$rs);

            $rs['orders_goods'][$key] = array_merge($val,$tmp);
        }

        //是否还有运费可以退
        if(in_array($rs['status'],[2,3])) {
            $tmp = $this->_refund_express_check($rs['s_no'],$rs['status'],$rs['express_price_edit']);
            $rs['can_refund_express'] = $tmp['can_refund_express'];
        }

        //如果商品退完只剩运费是不可以评价的
        if($rs['status'] == 4 && $rs['goods_price_edit'] > $rs['refund_price']){
            $rs['is_rate'] = 1;
        }

        //是否使用了官方优惠券
        $rs['is_official_coupon'] = 0;
        if($rs['coupon_id']){
            $official_coupon_count      = M('coupon')->where(['id' => ['in',$rs['coupon_id']],'type' => 2])->count();
            $rs['is_official_coupon']   = $official_coupon_count > 0 ? 1 : 0;
        }

        //计算订单下一步操作剩余时间
        $rs['limit_time']   = 0;
        switch($rs['status']){
            case 1:
                $next_time              = strtotime($rs['atime']) + C('cfg.orders')['add'];
                $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                $rs['limit_time_name']  = '剩'.diff_time($next_time).'自动关闭';

                //如果使用官方优惠券是不支持唐宝付款
                $rs['is_tangbao_pay']   = 1;
                if($rs['coupon_id']){
                    $count = M('coupon')->where(['type' => 2,'id' => ['in',$rs['coupon_id']]])->count();
                    if($count > 0) $rs['is_tangbao_pay'] = 0;
                }

                break;
            case 3:
                $next_time              = strtotime($rs['express_time']) + C('cfg.orders')['confirm_orders'];
                $rs['limit_time']       = $next_time > time() ? $next_time - time() : 0;
                $rs['limit_time_name']  = '还剩'.diff_time($next_time).'自动确认收货';
                break;
        }


        //返回可能想买的商品
        if($param['is_love'] == 1){
            $cid = M('goods')->Distinct(true)->where(['id' => ['in',$goods_ids]])->getField('category_id',true);
            $tmp = A('Rest2/Search')->_love(['cid' => $cid,'num' => 12,'not_ids' => $goods_ids]);
            $rs['love_goods']   = $tmp['data'];
        }
        return ['code' => 1,'data' => $rs];
    }

    /**
     * 判断商品是否可以退款或售后
     * Create by Lazycat
     * 2017-03-23
     * @param int   $orders_status      订单状态
     * @param date  $receipt_time       确认收货时间
     * @param int   $orders_goods_id    已订购的商品ID
     * @param array $orders_goods       已订购的商品记录
     * @param array $ors                订单记录
     * @param array|string $check_type  检查类型，1=退款检查，2=退运费检查，3=售后检查
     */
    public function _refund_and_service_check($orders_status='',$orders_goods_id,$orders_goods=null,$ors=null,$check_type=array(1,2,3)){
        if(is_null($orders_goods) || empty($orders_goods)){
            $orders_goods = M('orders_goods')->where(['id' => $orders_goods_id])->field('etime,ip',true)->find();
        }

        if(is_null($ors) || empty($ors)){
            $ors = M('orders_shop')->where(['s_no' => $orders_status['s_no']])->field('s_no,status,goods_price_edit,receipt_time')->find();
        }

        $orders_status = empty($orders_status) ? $ors['status'] : $orders_status;
        if(!is_array($check_type)) $check_type = explode(',',$check_type);

        $res = [
            'refund_in'         => 0,   //是否存在退款记录，需要显示退款详情连接
            'can_refund'        => 0,   //是否还可以退款，需要显示申请退款连接
            'can_price'         => 0,   //可退款商品金额
            'can_num'           => 0,   //可退数量
            'unit_price'        => 0,   //平均单价
        ];

        //商品金额是否可退
        if(in_array(1,$check_type)) {
            //未发货订单，是否存在退款
            if ($orders_status == 2) {
                if ($orders_goods['refund_num'] == $orders_goods['num']) $res['refund_in'] = 2;
                else {
                    $tmp_count = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => 2])->count();
                    if ($tmp_count > 0) $res['refund_in'] = 1;

                    $tmp = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => 2, 'status' => ['neq', 20]])->field('sum(num) as num,sum(money) as money')->find();
                    if ($tmp['num'] < $orders_goods['num']) {
                        $res['can_refund']  = 1;
                        $res['can_num']     = $orders_goods['num'] - $tmp['num'];
                        $res['can_price']   = number_formats($orders_goods['total_price_edit'] - $tmp['money'],2);
                        $res['unit_price']  = number_formats($orders_goods['total_price_edit'] / $orders_goods['num'],2);
                        if($res['can_num'] == 1 && $res['unit_price'] != $res['can_price']) $res['unit_price'] = $res['can_price']; //金额较对
                        if($res['can_num'] <=0 && $res['can_price'] <= 0) $res['can_refund'] = 0;
                    }
                }
            }

            //已发货订单，是否存在退款
            if (in_array($orders_status, [3, 4, 5, 6, 11])) {
                if ($orders_goods['total_price_edit'] == $orders_goods['refund_price']) {   //已全部退完
                    $res['refund_in'] = 2;
                } else {
                    $tmp_count = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => ['in', '2,3']])->count();
                    if ($tmp_count > 0) $res['refund_in'] = 1;

                    if ($orders_status == 3 && $orders_goods['is_can_refund'] == 1) {
                        //已发货的退款，申请退款金额（含取消的）累积，如果==商品金额即不充许再次申请退款
                        $tmp_money = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => 3])->sum('money');
                        if ($tmp_money < $orders_goods['total_price_edit']) {
                            $tmp = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => ['in','2,3'], 'status' => ['neq', 20]])->field('sum(num) as num,sum(money) as money')->find();
                            if ($tmp['money'] < $orders_goods['total_price_edit']) {
                                $res['can_refund']  = 1;
                                $res['can_num']     = $orders_goods['num'] - $tmp['num'];
                                $res['can_price']   = number_formats($orders_goods['total_price_edit'] - $tmp['money'],2);
                                $res['unit_price']  = number_formats($orders_goods['total_price_edit'] / $orders_goods['num'],2);

                                if($res['can_num'] == 1 && $res['unit_price'] != $res['can_price']) $res['unit_price'] = $res['can_price']; //金额较对
                                if($res['can_num'] <=0 && $res['can_price'] <= 0) $res['can_refund'] = 0;
                            }
                        }
                    }
                }
            }
        }

        //运费是否可退
        if(in_array(2,$check_type)) {

            $tmp = $this->_refund_express_check($ors['s_no'],$orders_status,$ors['express_price_edit']);
            $res = array_merge($res,$tmp);
        }

        //是否存在售后
        if(in_array(3,$check_type)) {

            $res['service_in']        = 0;   //是否存在售后，需要显示售后详情连接
            $res['can_service']       = 0;   //是否还可以申请售后，需要显示申请售后连接
            $res['can_service_num']   = 0;   //可售后商品数量

            $service_can_num = $orders_goods['num'] - $orders_goods['refund_num']; //可售后的数量
            $sub_price = $orders_goods['total_price_edit'] - $orders_goods['refund_price'];   //钱有没被退完
            if (in_array($orders_status, [4, 5]) && $sub_price > 0 && $service_can_num > 0 && $orders_goods['goods_service_days'] > 0 && !empty($ors['receipt_time']) && $ors['receipt_time'] != '0000-00-00 00:00:00') {
                $stmp = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => ['in', '4,5']])->field('count(*) as count,sum(num) as num')->find();
                if ($stmp['count'] > 0) $res['service_in'] = 1;

                //如果申请售后累积（含取消的）售后商品数量等于订购的商品数量则不可以再次申请售后
                if ($stmp['num'] < $service_can_num && date('Y-m-d H:i:s', strtotime($ors['receipt_time']) + $orders_goods['goods_service_days'] * 86400) > date('Y-m-d H:i:s')) {
                    $res['can_service'] = 1;

                    $snum = M('refund')->where(['orders_goods_id' => $orders_goods_id, 'orders_status' => ['in', '4,5'],'status' => ['neq',20]])->sum('num');
                    $res['can_service_num'] = $service_can_num - $snum;
                }
            }
        }

        return $res;
    }

    /**
     * 判断是否还可以退运费，商品不充许退款时再执行此判断
     * @param string $s_no  订单号
     * @param int    $orders_status 订单状态
     * @param float  $express_price 订单运费
     */
    public function _refund_express_check($s_no,$orders_status,$express_price){
        $res['can_refund_express']  = 0;    //是否允许退款
        $res['can_express_price']   = 0;    //可退金额
        $max_num                    = 2;   //含有退运费的申请记录最多不可超过max_num;

        if($orders_status == 2){    //未发货的退款，不限退款次数
            $tmp = M('refund')->where(['s_no' => $s_no,'orders_status' => $orders_status,'status' => ['neq',20]])->field('count(*) as count,sum(refund_express) as refund_express')->find();
            $res['can_express_price']    = $express_price - $tmp['refund_express'];
            if($express_price > $tmp['refund_express'] && $res['can_express_price'] > 0) {
                $res['can_refund_express']  = 1;
            }

        }elseif($orders_status == 3){   //已发货的退款
            $count = M('refund')->where(['s_no' => $s_no,'orders_status' => $orders_status,'num' => 0,'money' => 0,'refund_express' => ['gt',0]])->count(); //单独申请退运费的次数（含取消及完成）
            if($count < $max_num){
                $tmp = M('refund')->where(['s_no' => $s_no,'status' => ['neq',20]])->field('sum(refund_express) as refund_express')->find();
                $res['can_express_price']   = $express_price - $tmp['refund_express'];
                if($express_price > $tmp['refund_express'] && $res['can_express_price'] > 0){
                    $res['can_refund_express']  = 1;
                }
            }
        }
        return $res;
    }


    /**
     * subject: 买家查询订单物流信息
     * api: /BuyerOrders/logistics_info
     * author: Lazycat
     * day: 2017-02-20
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     */
    public function logistics_info(){
        $this->check('openid,s_no',false);

        $res = $this->_logistics_orders($this->post);
        $this->apiReturn($res);
    }

    public function _logistics_orders(){
        $rs = M('orders_shop')->where(['uid' => $this->user['id'],'s_no' => $this->post['s_no']])->field('s_no,express_company_id,express_code,express_time')->find();

        if($rs['express_company_id'] > 0){
            $res = $this->_logistics_info($rs);
            return $res;
        }

        return ['code' => 0,'msg' => '找不到物流记录！'];
    }
	
    /**
     * subject: 买家通过阿里云查询订单物流信息
     * api: /BuyerOrders/logistics_info_aliyun
     * author: Lizuheng
     * day: 2017-04-14
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     */
    public function logistics_info_aliyun(){
        $this->check('openid,s_no',false);

        $res = $this->_logistics_orders_aliyun($this->post);
        $this->apiReturn($res);
    }

    public function _logistics_orders_aliyun(){
        $rs = M('orders_shop')->where(['uid' => $this->user['id'],'s_no' => $this->post['s_no']])->field('s_no,express_company_id,express_code,express_time')->find();

        if($rs['express_company_id'] > 0){
            $res = $this->_logistics_info_aliyun($rs);
            return $res;
        }

        return ['code' => 0,'msg' => '找不到物流记录！'];
    }
    /**
     * subject: 买家订单统计
     * api: /BuyerOrders/total
     * author: Lazycat
     * day: 2017-02-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function total(){
        $this->check('openid',false);

        $res = $this->_buyer_orders_count($this->user['id']);
        $this->apiReturn(['code' => 1,'data' => $res]);
    }

    /**
     * subject: 获取待评价商品
     * api: /BuyerOrders/wait_rate_goods
     * author: Lazycat
     * day: 2017-02-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     */
    public function wait_rate_goods(){
        $this->check('openid,s_no',false);
        $res = $this->_wait_rate_goods($this->post);

        $this->apiReturn($res);
    }

    //已退款的商品不允许评价
    public function _wait_rate_goods($param){
        $do = D('Common/OrdersGoodsOrdersShopView');

        $map['uid']                 = $this->user['id'];
        $map['s_no']                = $param['s_no'];
        $map['is_rate']             = 0;
        $map['orders_shop.status']  = 4;
        $map['_string']             = 'orders_goods.refund_price < orders_goods.total_price_edit';

        $list = $do->where($map)->field('id,s_id,s_no,goods_id,attr_list_id,attr_name,price,num,goods_name,images')->select();

        if($list){
            return ['code' => 1,'data' => $list];
        }
        return ['code' => 3];
    }

    /**
     * subject: 订单评价
     * api: /BuyerOrders/rate_save
     * author: Lazycat
     * day: 2017-02-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,string,1,订单号
     * param: fraction_speed,int,1,卖家发货速度评分
     * param: fraction_service,int,1,卖家服务态度评分
     * param: is_anonymous,int,1,是否匿名评价
     * param: goods_rate,string,1,序列化后的商品评价内容，没序列化前格式如：
     */

    public function rate_save(){
        $this->post['goods_rate']   = html_entity_decode($this->post['goods_rate']);
        $this->check('openid,s_no,fraction_speed,fraction_service,goods_rate');

        $tmp   = unserialize($this->post['goods_rate']);
        if(empty($tmp)) $tmp = json_decode($this->post['goods_rate'],true);
        $this->post['goods_rate'] = $tmp;

        //数据验证
        foreach($this->post['goods_rate'] as $key => $val){
            if(empty((int) $val['orders_goods_id']))    $this->apiReturn(['code' => 0,'msg' => '第'.($key+1).'款商品ID不能为空！']);
            if(empty((int) $val['fraction_desc']))      $this->apiReturn(['code' => 0,'msg' => '第'.($key+1).'款商品描述评分不能为空！']);
            if(empty($val['content']))                  $this->apiReturn(['code' => 0,'msg' => '第'.($key+1).'款商品评价内容不能为空！']);
        }

        $res = $this->_rate_save($this->post);
        $this->apiReturn($res);
    }

    //兼容旧版本每个商品独立评价的方式
    public function _rate_save($param){
        //订单是否已评价
        $ors = M('orders_shop')->where(['uid' => $this->user['id'],'s_no' => $param['s_no']])->field('id,status,s_no,uid,seller_id,shop_id,is_shuadan')->find();

        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if($ors['status'] != 4) return ['code' => 0,'msg' => '只有已收货的订单方可评价！'];

        //未评价且没退完款的商品
        $orders_goods_list = M('orders_goods')->where(['s_id' => $ors['id'],'_string' => 'total_price_edit > 0 and total_price_edit > refund_price'])->getField('id,is_rate,rate,goods_id,attr_list_id,fraction_desc',true);

        $do = M();
        $do->startTrans(); //事务开始

        $fraction_desc = [];
        foreach($param['goods_rate'] as $key => $val){
            $orders_goods = $orders_goods_list[$val['orders_goods_id']];
            if($orders_goods['is_rate'] == 0){
                $fraction_desc[] = $val['fraction_desc'];

                //兼容旧的评价方式（好、中、差评）;
                $rate = 1;  //4~5分为好评
                if($val['fraction_desc'] < 2) $rate = -1; //1分为差评
                elseif($val['fraction_desc'] < 4) $rate = 0;  //2~3分为中评

                $point = ($rate == 1 && $ors['is_shuadan'] > 0) ? 0 : $rate;    //得分，用于计算店铺等级
                $data = array(
                    's_id'              => $ors['id'],
                    's_no'              => $ors['s_no'],
                    'status'            => 1,
                    'shop_id'           => $ors['shop_id'],
                    'uid'               => $ors['uid'],
                    'seller_id'         => $ors['seller_id'],
                    'orders_goods_id'   => $orders_goods['id'],
                    'goods_id'          => $orders_goods['goods_id'],
                    'attr_list_id'      => $orders_goods['attr_list_id'],
                    'rate'              => $rate,
                    'fraction_desc'     => $val['fraction_desc'],
                    'is_anonymous'      => $param['is_anonymous'] ? $param['is_anonymous'] : 0,
                    'content'           => $val['content'],
                    'images'            => $val['images'],
                    'point'             => $point,
                    'is_shuadan'        => $ors['is_shuadan'],
                    'is_sys'            => $param['is_sys'] ? $param['is_sys'] : 0
                );
                //print_r($data);

                if(!$this->sw[] = D('Common/OrdersGoodsComment')->create($data)){
                    $msg = D('Common/OrdersGoodsComment')->getError();
                    goto error;
                }

                if(!$this->sw[] = D('Common/OrdersGoodsComment')->add()){
                    goto error;
                }

                if(!$this->sw[] = M('orders_goods')->where(array(['id' => $orders_goods['id']]))->save(['is_rate' => 1,'rate' => $rate,'rate_time' => date('Y-m-d H:i:s')])){
                    $msg = '更新订单商品评价状态失败！';
                    goto error;
                }

                //更新商品库存评价，卖家可能会删除库存，所以可不列入事务一定要执行成功
                $rate_field = ['1' => 'rate_good','0' => 'rate_middle','-1' => 'rate_bad'];
                M('goods_attr_list')->where(['id' => $orders_goods['attr_list_id']])->setInc($rate_field[$rate],1);

                //更新商品好评率
                $grs = M('goods')->lock(true)->where(['id' => $orders_goods['goods_id']])->field('rate_num,rate_good,rate_middle,rate_bad')->find();
                $grs[$rate_field[$rate]]++;
                $grs['rate_num']++;

                $grs['fraction']    = ($grs['rate_good']+100) / ($grs['rate_num']+100);
                if(!$this->sw[] = M('goods')->where(['id' => $orders_goods['goods_id']])->save($grs)){
                    $msg = '更新商品好评率失败！';
                    goto error;
                }

            }else{  //兼容旧评价方式
                $tmp = [0 => '2',1 => 5,'-1' => 3];
                $fraction_desc[] = $tmp[$orders_goods['rate']];
            }
        }


        //该笔订单综合评分
        $fraction_desc  = array_sum($fraction_desc) / count($fraction_desc);
        $fraction       = ($param['fraction_speed'] + $param['fraction_service'] + $fraction_desc) /3;
        $data = [
            's_id'              => $ors['id'],
            's_no'              => $ors['s_no'],
            'shop_id'           => $ors['shop_id'],
            'uid'               => $ors['uid'],
            'seller_id'         => $ors['seller_id'],
            'content'           => '默认评价！',
            'fraction_speed'    => $param['fraction_speed'],
            'fraction_service'  => $param['fraction_service'],
            'fraction_desc'     => $fraction_desc,
            'fraction'          => $fraction,
            'is_sys'            => $param['is_sys'] ? $param['is_sys'] : 0
        ];

        //print_r($data);

        if(!$this->sw[] = D('Common/OrdersShopComment')->create($data)){
            $msg = D('Common/OrdersShopComment')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/OrdersShopComment')->add()) {
            $msg = '创建店铺评价失败！';
            goto error;
        }

        //店铺总体综合评价
        $shop_rate = $do->query('select count(*) as num,sum(fraction_speed) as fraction_speed,sum(fraction_service) as fraction_service,sum(fraction_desc) as fraction_desc,sum(fraction) as fraction from '.C('DB_PREFIX').'orders_shop_comment where shop_id='.$ors['shop_id']);
        //print_r($shop_rate[0]);

        //系统默认赠送8个5分,2个4分（即10笔=48分，相当于默认评分为4.8分），避免评价少时计算出来的分数太差，
        $give   = 10;
        $tmp    = [];
        $tmp['fraction_speed']      = ($shop_rate[0]['fraction_speed'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction_service']    = ($shop_rate[0]['fraction_service'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction_desc']       = ($shop_rate[0]['fraction_desc'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction']            = ($shop_rate[0]['fraction'] + 48) / ($give + $shop_rate[0]['num']);

        if($this->sw[] = false === M('shop')->where(['id' => $ors['shop_id']])->save($tmp)){
            //print_r(M('shop')->getLastSql());
            $msg = '更新店铺评分失败！';
            goto error;
        }

        //更新订单
        if(!$this->sw[] = M('orders_shop')->where(['id' => $ors['id'],'status' => 4])->save(['status' => 5,'rate_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['orders_history']),'is_problem' => 0])){
            $msg = '更新订单评价状态失败！';
            goto error;
        }

        $do->commit();
		
		/*
        $rs = M('refund')->where(['uid' => $this->user['id'],'s_no' => $param['s_no'],'orders_status' =>'4'])->field('id,uid,r_no,s_no,type,status,orders_status')->find();
        if($rs && $rs['orders_status'] == 4){
			$result = M('refund')->where(['uid' => $this->user['id'],'s_no' => $param['s_no'],'orders_status' =>'4'])->save(['orders_status'=>'5']);
		}
		*/

		//检测刷单、评价检测、店铺计分
        A('Rest2/ToolsRate')->_check_orders_shuadan(['s_no' => $param['s_no']]);


		return ['code' => 1,'msg' => '评价成功！'];


        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '评价失败！','data' => $this->sw];

    }

}