<?php
/**
 * 从支付开始算
 */
namespace Common\Builder;
class Activity {
    protected $_ids;        //促销活动ID，多个 string
    protected $_goods;      //促销商品ID单个 int
    protected $_num;        //用户下单数量
    protected $_step    =   1;  //所在步骤，1为加入购物车，2为生成订单，3支付
    
    function __construct($ids, $goods, $num, $step = null) {
        $this->_ids     =   $ids;
        $this->_goods   =   $goods;
        $this->_num     =   $num;
        if (!is_null($step)) $this->_step =   $step;
    }
    
    /**
     * 获取能够参与的活动
     * @return string
     */
    public function getActivitys() {
        $map    =   [
            'id'            =>  ['in', $this->_ids],
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],    //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
        ];
        $data   =   M('activity')->cache(true)->where($map)->order('id asc')->field('type_id,id,shop_id,full_money,full_value,max_num,sku_num,sale_num,goods')->select();
        $ids    =   null;
        foreach ($data as &$value) {
            //如果有设置最大购买量的时候，当当前用户购买已超过最大购买量，则移除当前活动
            if (($value['max_num'] > 0 && ($value['max_num'] < $this->_num))) {
                unset($value);
            } else {
                $ids    .=  $value['id'] . ',';
            }
        }
        if ($ids) {
            return trim($ids, ',');
        }
        return false;
    }
    
    /**
     * 为商家订单绑定促销活动ID
     * @param unknown $shop
     * @param unknown $totalPrice
     */
    public static function getActivity($shop, $totalPrice) {
        $map    =   [
            'shop_id'       =>  $shop,
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],    //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
            'type_id'       =>  ['in', '1,2,3'],
        ];
        $data   =   M('activity')->cache(true)->where($map)->order('id asc')->field('type_id,id,shop_id,full_money,full_value,max_num,sku_num,sale_num')->order('full_money desc')->select();
        if ($data) {
            $tmp    =   [];
            foreach($data as &$v) {
                //if (($v['sku_num'] > 0 && ($v['sku_num'] < $v['sale_num'])) || ($v['full_money'] > 0 && $v['full_money'] > $totalPrice))
                if (($v['full_money'] > 0 && $v['full_money'] > $totalPrice)) {
                    unset($v);
                } else {
                    $tmp[]    =   $v;
                }
            }
            return $tmp;
        }
        return null;
    }

    /**
     * 通过订单金额获取当前商家的促销信息
     *
     * @param $shopId
     * @param $totalPrice
     * @return array|bool
     */
    static public function calcTotalPrice($shopId, $totalPrice) {
        $map   =   [
            //'id'    =>  ['in', $activitys],
            'type_id'       =>  ['in', '1,2,3'],
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],    //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
            'full_money'    =>  ['elt', $totalPrice],
            'shop_id'       =>  $shopId,
        ];
        $data  =   D('ActivityView')->where($map)->field('activity_name,type_id,id,full_money,full_value,highest,is_accumulation')->order('type_id asc')->select();
        $res   =   [];
        $lessMoney = 0;
        if ($data) {
            foreach ($data as $v) {
                switch ($v['type_id']) {
                    case 1:
                        $res['express'] = 1;    //包邮
                        break;
                    case 2:
                        $res['gift']    =   getActivityFullvalueGoods($v['full_value'], $shopId);
                        break;
                    case 3:
                        if ($v['is_accumulation'] == 1) {  //是否为累积
                            //to do codeing...
                            $multiple = floor($totalPrice / $v['full_money']);
                            $lessMoney= round($multiple * $v['full_value'], 2);
                            if ($lessMoney > $v['highest'] && $v['highest'] > 0) {
                                $lessMoney = $v['highest'];
                            }
                            //$goods_price_edit           =   ($totalPrice - $lessMoney);
                        } else {
                            //to do your codeing ...
                            //$goods_price_edit           =   ($totalPrice - $v['full_value']);
                            $lessMoney = $v['full_value'];
                        }
                        $res['less']    =   $lessMoney;  //满减
                        break;
                }
            }
            unset($v);
            if ($lessMoney > 0) {   //如果大于0则证明有满减，需判断减去满减是否还满足其他促销
                foreach ($data as $v) {
                    if (($totalPrice - $lessMoney) < $v['full_money'] && $v['type_id'] != 3) {
                        switch ($v['type_id']) {
                            case 1:
                                unset($res['express']);
                                break;
                            case 2:
                                unset($res['gift']);
                                break;
                        }
                    }
                }
            }
            unset($v,$data);
            return $res;
        }
        return false;
    }

    /**
     * 获取使用唐宝支付的促销
     * @param unknown $orderData    订单
     * @param bool $isData        为true的情况下则是直接返回当前活动
     * @return boolean|Ambigous <mixed, boolean, NULL, string, unknown, multitype:, object>|Ambigous <boolean, number>
     */
    public static function tangPaysActivity($orderData, $isData = false) {
        if (empty($orderData)) return false;
        if (M('activity_participate')->cache(true)->where(['s_no' => $orderData['s_no'], 'type_id' => ['in', '4,5,6']])->getField('id') > 0) return false;//如果当前订单有参与0元购，秒杀等活动，则不能参与唐宝折扣优惠。
        $map    =   [
            'shop_id'       =>  $orderData['shop_id'],
            'type_id'       =>  4,
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],      //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
        ];
        $data   =   M('activity')->cache(true)->where($map)->field('type_id,id,shop_id,full_money,full_value,max_num,sku_num,sale_num')->find();
        if ($isData && $data) {
            return $data;
        }
        $res    =   false;
        if($data) {
            $res    =   self::tangPaysActivityEditData($orderData, $data);
        }
        return $res;
    }
    
    /**
     * 唐宝支付数据修改
     * @param unknown $orders
     * @param unknown $data
     */
    public static function tangPaysActivityEditData($orders, $data) {
        $model                      =   M('orders_shop');
        $pay_price                  =   round($orders['pay_price'], 2);       //默认支付金额
        $score                      =   $orders['score'];           //默认赠送积分数量
        $full_value                 =   number_formats($data['full_value'], 3);        //折扣
        $goods_price_edit           =   round($orders['goods_price_edit'], 2);//修改后的商品价格 
        $aData['calc_before_money'] =   $pay_price;
        $aData['uid']               =   $orders['uid'];
        $aData['s_no']              =   $orders['s_no'];
        $aData['type_id']           =   $data['type_id'];
        $aData['activity_id']       =   $data['id'];
        $aData['full_value']        =   $data['full_value'];
        $aData['remark']            =   '享受了唐宝支付' . $data['full_value'] . '折优惠';
        $aData['max_num']           =   $data['max_num'];
        $aData['full_money']        =   $data['full_money'];
        $aData['shop_id']           =   $orders['shop_id'];
        $goods_price_edit           =   number_formats(($goods_price_edit) * ($full_value * 0.1), 2);
        $pay_price                  =   round($goods_price_edit + $orders['express_price_edit'] + $orders['daigou_cost'], 2);
        $score                      =   intval($full_value * 0.1 * $score * 100) / 100;
        $aData['calc_after_money']  =   $pay_price;
        $model->startTrans();
        //参与者加+
        if (self::activitySaleInc($data['id']) == false) {
            goto error;
        }
        
        //加入参与记录
        if (self::isRecor($orders['s_no'], $data['type_id'], $orders['uid']) == true) {//未添加至参与记录则添加至参与记录
            if (self::recording($aData) == false) {
                goto error;
            }
        }
        
        
        //修改所有商品
        //$fullValue  =   ($full_value * 0.1);
        
        
        //查看是否已经有参与累积升级活动，如果有则关闭
//         if ($activity7 = M('activity_participate')->where(['s_no' => $orders['s_no'], 'uid' => $orders['uid'], 'type_id' => 7])->getField('id')) {
//             if (M('activity_participate')->where(['id' => $activity7])->save(['status' => 2]) == false) {
//                 goto error;
//             }
//         }
        
        $model->commit();
        $res['score']               =   $score;         //赠送积分
        $res['pay_price']           =   $pay_price;     //支付金额
        $res['full_value']          =   number_formats(($full_value * 0.1), 3);  //折扣
        $res['goods_price_edit']    =   $goods_price_edit;  //商品修改后的金额
        S(md5('shop_orders_s_no' . $orders['s_no']), 1);
        return $res;
        error:
            $model->rollback();
            return false;
    }
    
    /**
     * 获取参与的活动
     * @param unknown $s_no
     * @param unknown $type_id
     */
    public static function getActivityByShopOrders($orders, $type_id) {
        if (M('activity_participate')->cache(true)->where(['s_no' => $orders['s_no'], 'type_id' => ['in', '4,5,6']])->getField('id') > 0) return false;//如果当前订单有参与0元购，秒杀等活动，则不能参与唐宝折扣优惠。
        $map['s_no']    =   $orders['s_no'];
        $map['status']  =   0;
        $map['type_id'] =   strpos($type_id, ',') !== false ? ['in', $type_id] : $type_id;
        $data           =   M('activity_participate')->field('atime,etime,remark',true)->where($map)->find();
        if ($data == false && $type_id == 4) { //如果取值不成功，并且是唐宝支付有折扣的活动，则重新取一次
            $data = M('activity')->where(['shop_id' => $orders['shop_id'], 'type_id' => $type_id, 'status' => 1])->find();
        }
        if($data) {
            $pay_price                  =   round($orders['pay_price'], 2);       //默认支付金额
            $score                      =   $orders['score'];           //默认赠送积分数量
            $full_value                 =   round($data['full_value'], 2);        //折扣
            $goods_price_edit           =   round($orders['goods_price_edit'], 2);//修改后的商品价格
            $data['goods_price_edit']   =   round(($goods_price_edit - $orders['daigou_cost']) * ($full_value * 0.1), 2);
            $data['pay_price']          =   round($data['goods_price_edit'] + $orders['express_price_edit'] + $orders['daigou_cost'], 2);
            $data['score']              =   round(round(($full_value * 0.1), 2) * $score, 0);
            return $data;
        }
        return false;
    }

    /**
     * 取出当前商家的唐宝支付有折扣
     *
     * @param $orders
     * @return bool|mixed
     */
    public static function getTangpayActivityByOrdersShop($orders) {
        if (M('activity_participate')->cache(true)->where(['s_no' => $orders['s_no'], 'type_id' => ['in', '5,6']])->getField('id') > 0) return false;//如果当前订单有参与0元购，秒杀等活动，则不能参与唐宝折扣优惠。
        $map['s_no']    =   $orders['s_no'];
        $map['status']  =   0;
        $map['type_id'] =   4;
        $data           =   M('activity_participate')->field('atime,etime,remark',true)->where($map)->find();
        if ($data == false) { //如果取值不成功，并且是唐宝支付有折扣的活动，则重新取一次
            $data = M('activity')->where(['shop_id' => $orders['shop_id'], 'type_id' => 4, 'status' => 1])->find();
        }
        if($data) {
            $pay_price                  =   round($orders['pay_price'], 2);       //默认支付金额
            $score                      =   $orders['score'];           //默认赠送积分数量
            $full_value                 =   round($data['full_value'], 2);        //折扣
            $goods_price_edit           =   round($orders['goods_price_edit'], 2);//修改后的商品价格
            $data['goods_price_edit']   =   round(($goods_price_edit - $orders['daigou_cost']) * ($full_value * 0.1), 2);
            $data['pay_price']          =   round($data['goods_price_edit'] + $orders['express_price_edit'] + $orders['daigou_cost'], 2);
            $data['score']              =   round(round(($full_value * 0.1), 2) * $score, 0);
            return $data;
        }
        return false;
    }
    
    /**
     * 记录
     */
    public static function recording($data) {
        $model  =   D('ActivityParticipate');
        $model->startTrans();
        if (!$data  =   $model->create($data)) {
            return false;
        }
        $flag   =   $model->add();
        
        if ($flag) {
            $model->commit();
            return $flag;
        }
        $model->rollback();
        return false;
    }
    
    /**
     * 根据订单号获取已参加的促销活动
     * @param unknown $s_no
     */
    public static function getActivityByOrder($s_no) {
        if (M('activity_participate')->where(['s_no' => $s_no])->count() > 0) {
            $data   =   M('activity_participate')->cache(true)->where(['s_no' => $s_no, 'status' => ['in', '0,1']])->order('id asc')->select();
            foreach ($data as $k => &$val) {
                if ($val['type_id'] == 2) {
                    $val['goods']   =   getActivityFullvalueGoods($val['full_value'], $val['shop_id']);
                }
                if ($val['status'] == 0 && $val['type_id'] == 4) {  //如果唐宝折扣促销状态为0则删除
                    unset($data[$k]);
                }
            }
            return $data;
        }
        return false;
    }
    
    /**
     * 检查是否已记录
     * @param 商家订单号 $sno    string
     * @param 活动类型 $typeId  int
     * @param 用户id          int
     */
    public static function isRecor($sno, $typeId, $uid) {
        $map    =   [
            's_no'      =>  $sno,
            'type_id'   =>  $typeId,
            'uid'       =>  $uid,
        ];
        if(M('activity_participate')->where($map)->count() > 0) {
            return false;
        }
        return true;
    }
    
    /**
     * 更新参与状态
     * @param unknown $map
     */
    public static function setStatus($sno, $uid, $status = 1, $notin = null) {
        $map    =   [
            's_no'      =>  $sno,
            'uid'       =>  $uid,
        ];
        //修改已完成则累积升级不更改状态
        if ($status == 1) {
            $map['type_id'] =   ['notin', '7']; //设置状态的时候累积升级不纳入状态修改，调至确认收货的时候修改
        }
        if (!is_null($notin)) {
            if ($status == 1) $notin .= ',7';
            $map['type_id'] =   ['notin', $notin];
        }
        if (false === M('activity_participate')->where($map)->save(['status' => $status])) {
            return false;
        }
        if ($status == 2) {
            //0元购或者秒杀则减购买的数量，其他促销直接减1
            $num    =   M('activity_participate')->where(['type_id' => ['in', '5,6'], 'uid' => $uid, 's_no' => $sno])->getField('buy_num');
            $num    =   $num > 0 ? $num : 1;
            if ($num > 0) { //减数量
                $aId    =   M('activity_participate')->where($map)->getField('activity_id');
                if ($aId) {
                    $ordersId = M('orders_shop')->where(['s_no' => $sno, 'status' => 1])->field('id')->find();
                    if ($ordersId > 0) { //未付款
                        $sql    =   'UPDATE ' . C('DB_PREFIX') . 'activity SET `sale_num` = sale_num-' . $num . ' WHERE `id` = ' . $aId;
                    } else {
                        $sql    =   'UPDATE ' . C('DB_PREFIX') . 'activity SET `payment_num` = payment_num-1, `sale_num` = sale_num-' . $num . ' WHERE `id` = ' . $aId;
                    }
                    if (M()->execute($sql) === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * 参与付款成功+1
     * @param string $s_no  订单号
     * @param string $field 支付数量payment_num
     * @return boolean
     */
    public static function activityInc($s_no, $field = 'payment_num') {
        $getSales   =   M('activity_participate')->where(['s_no' => $s_no])->getField('id,activity_id');
        if ($getSales) {
            $aids       =   '';
            foreach ($getSales as $v) {
                $aids .= $v .',';
            };
            
            $map    =   [
                'id'    =>  ['in', $aids],
            ];
            
            if (false == M('activity')->where($map)->setInc($field, 1)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 参与销量+1
     * @param unknown $id
     */
    public static function activitySaleInc($id, $num = 1) {
        //当促销活动为0元购或者秒杀则直接加订单商品的数量，其他促销则直接加1
        if (false == M('activity')->where(['id' => $id])->setInc('sale_num', $num)) {
            return false;
        }

        return true;
    }
    
    /**
     * 查询可以参加活动
     * @param array $data
     * @param bool  $isCoupon   是否有使用优惠券
     * @return Ambigous <boolean, string>
     */
    public static function participate($data, $isCoupon = 0) {
        $map    =   [
            'shop_id'       =>  $data['shop_id'],
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],    //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
            //'type_id'       =>  ['in', '1,2,3,4'],
        ];
        
        $fulLessData    =   [];
        if ($isCoupon == 0) {    //有使用优惠券则不参与满减活动
            //满就减
            $fulLessMap =   array_merge($map, ['type_id' => 3, 'full_money' => ['elt', $data['goods_price_edit']]]);
            
            //满减
            $fulLessData=   M('activity')->cache(true)->where($fulLessMap)->order('id asc')->field('type_id,id,shop_id,full_money,full_value,max_num,sku_num,sale_num,is_accumulation,highest')->order('full_money desc')->find();
        }
        
        if (!empty($fulLessData)) { //如果满减存在找出满减后能够参与的活动
            $otherMap   =   ['full_money' => ['elt', (round($data['goods_price_edit'] - round($fulLessData['full_value'], 2), 2))], 'type_id' => ['in', '1,2,7']];
            $map        =   array_merge($map, $otherMap);
        } else {
            $map['type_id']     =   ['in', '1,2,7'];
            $map['full_money']  =   ['elt', $data['goods_price_edit']];
        }
        //去除其他活动
        $activityData   =   M('activity')->where($map)->field('type_id,id,shop_id,full_money,full_value,max_num,sku_num,sale_num,is_accumulation,highest')->order('full_money desc')->select();

        if ($activityData && $fulLessData) {
            //array_push($fulLessData,$activityData);
            array_unshift($activityData, $fulLessData);
        } else if ($fulLessData) {
            $activityData[0]    =   $fulLessData;
        }
        
        $res    =   false;
        
        if ($activityData) {
            $res = self::insertData($activityData, $data);
        }
        return $res;
    }
    
    /**
     * 记录参与者
     * @param unknown $tmp
     * @param unknown $data
     */
    public static function insertData($tmp, $data) {
        $model  =   D('ActivityParticipate');
        $model->startTrans();
        $ids    =   '';
        $express_price_edit =   round($data['express_price_edit'], 2);      //默认邮费    
        $total_price        =   round($data['total_price'], 2);             //默认总金额
        $score              =   $data['score'];                                     //默认赠送积分
        $coupon_percentage  =   1;                                                  //默认百分百
        $pay_price          =   $data['pay_price'];                                 //默认支付金额
        $goods_price_edit   =   round($data['goods_price_edit'], 2);        //所有商品总金额
        $coupon_price       =   0;
        foreach ($tmp as $val) {
            //参与者+1更新失败 或者已记录参与数据  则返回false
            if (self::activitySaleInc($val['id']) == false || self::isRecor($data['s_no'], $val['type_id']) == false) {
                goto error;
            }
            $aData['uid']               =   $data['uid'];
            $aData['s_no']              =   $data['s_no'];
            $aData['type_id']           =   $val['type_id'];
            $aData['activity_id']       =   $val['id'];
            $aData['max_num']           =   $val['max_num'];
            $aData['full_money']        =   $val['full_money'];
            $aData['shop_id']           =   $data['shop_id'];
            $aData['calc_before_money'] =   $total_price;
            switch ($val['type_id']) {
                case 1: //包邮
                    $aData['remark']            =   '享受了免' . $express_price_edit . '元邮费优惠';
                    $aData['calc_after_money']  =   $total_price - $express_price_edit;
                    $aData['full_value']        =   $express_price_edit;
                    $express_price_edit         =   0;
                    break;
                case 2: //满就送
                    $aData['full_value']        =   $val['full_value'];
                    $aData['remark']            =   $val['full_value'];
                    $aData['calc_after_money']  =   $total_price;
                    break;
                case 3: //满就减  
                    $val['full_value']          =   round($val['full_value'], 2);

                    if ($val['is_accumulation'] == 1) {  //是否为累积
                        //to do codeing...
                        $multiple = floor($goods_price_edit / $val['full_money']);
                        $lessMoney= round($multiple * $val['full_value'], 2);
                        if ($lessMoney > $val['highest'] && $val['highest'] > 0) {
                            $lessMoney = $val['highest'];
                        }
                        $goods_price_edit           =   ($goods_price_edit - $lessMoney);
                    } else {
                        //to do your codeing ...
                        $goods_price_edit           =   ($goods_price_edit - $val['full_value']);
                    }
                    $coupon_percentage          =   substr(($val['full_value'] / $goods_price_edit), 0, strpos(($val['full_value'] / $goods_price_edit), '.') + 5);
                    $score                     -=   $val['full_value']*100;
                    $score                      =   $data['score']>0?$data['score']:0;
                    $coupon_price               =   $val['full_value'];
                    $aData['full_value']        =   $val['full_value'];
                    //$aData['remark']            =   '享受了满' . round($val['full_money'], 2) . '元减' . $val['full_value'] . '元优惠';


                    $aData['remark']    =   $val['full_money'] > 0 ? '享受了' . ($val['is_accumulation'] == 1 ? '每满 ' : '满 ') . $val['full_money'] . ' 减 ' . number_format($val['full_value'], 2) .' 元' : '立减 ' . number_format($val['full_value'], 2).' 元';
                    if ($val['is_accumulation'] == 1) {
                        if ($val['highest'] > 0) {
                            $aData['remark'] .= ' 最高可减 ' . $val['highest'] . ' 元';
                        } else {
                            $aData['remark'] .= ' 上不封顶';
                        }
                    }


                    $aData['calc_after_money']  =   $goods_price_edit + $data['express_price_edit'];
                    break;
                case 7: //消费累积升级
                    $aData['full_value']        =   $goods_price_edit;//round($pay_price - $data['express_price_edit'], 2);
                    $aData['remark']            =   '享受了消费金额累积升级活动';
                    $aData['calc_after_money']  =   $pay_price;
                    break;
            }
            if (!$aData  =   $model->create($aData)) {
                goto error;
            }
            if (!$model->add()) {
                goto error;
            }
            $ids    .=   $val['id'] . ',';
        }
        $model->commit();
        $res['ids']                 =   trim($ids, ',');                                //所有活动的ID
        $res['express_price_edit']  =   $express_price_edit;                            //修改后的邮费
        $res['total_price']         =   $total_price;                                   //订单总金额
        $res['score']               =   $score;                                         //订单总积分
        $res['coupon_percentage']   =   $coupon_percentage;                             //优惠百分比
        $res['pay_price']           =   round($pay_price, 2);                   //支付金额
        $res['goods_price_edit']    =   round($goods_price_edit, 2);            //商品修改后的价格
        $res['coupon_price']        =   $coupon_price;
        return $res;
        error:
            $model->rollback();
            return false;
    }
    
    /**
     * 退款
     * @param string $s_no      申请退款订单号
     * @param float $money      申请退款金额
     * @param int $goods        申请退款的商品
     */
    public static function refundActivity($s_no, $money, $goodsId = null) {
        $map    =   [
            's_no'          =>  $s_no,
            'type_id'       =>  ['in', $goodsId ? '1,2,3,4' : '1,2,3'], //如果有传goodsid则为结算
            'status'        =>  1,
            'is_refund'     =>  0,
        ];
        if (!$goodsId) {
            $map['full_money']  =   ['gt', '0'];
        }
        
        
        
        //获取当前订单历史退款金额  只获取已经同意的退款
        $oldRefundMoney     =   M('orders_goods')->where(['s_no' => $s_no])->field('SUM(refund_price) as oldprice')->find();

        //获取订单
        $orders             =   M('orders_shop')->where(['s_no' => $s_no])->field('atime,etime,ip', true)->find();  //订单详情
        if (!$orders) {
            return false;
        }
        
        //状态小于3为已发货
        if ($orders['status'] < 3) {
            $map['type_id'] =   ['in', '3,4'];  //未发货只找出满减活动及折扣活动
        }
        
        //获取参与活动
        $data               =   [];
        if (M('activity_participate')->where($map)->count() > 0) {
            $data           =   M('activity_participate')->where($map)->field('atime,etime', true)->select();
        }
        //获取商品
        if ($goodsId) {
            $goods              =   M('orders_goods')->where(['id' => $goodsId, 's_no' => $s_no])->find();
            if (!$goods) {
                return false;
            }
            $baifenbi   =   round(round($goods['total_price'] / $orders['goods_price'], 2) + 1, 2);
            if (!empty($data)) {
                foreach ($data as $v) {
                    if ($v['type_id'] == 3) {
                        $couponMoney    =   round(round($money / $orders['goods_price'], 2) * $v['full_value'], 2);
                        $couponMoneys   =   round(($baifenbi * $couponMoney), 2);
                    }
                    
                    if ($v['type_id'] == 4) {
                        $tangCoupon     =   round(($money) * round(1 - (round($v['full_value'], 2) * 0.1), 2), 2);
                        $tangCoupon    +=   $couponMoneys;
                    }
                }
            }
            $money   =  ($money + $tangCoupon + $couponMoneys);
        }
        if (!empty($data)) {
            $money             +=   $oldRefundMoney['oldprice'];                        //当前订单历史退款金额
            $refundAfterMoney   =   round($orders['goods_price'] - $money, 2);  //退后还剩下多少金额
            $returnData         =   [];
            foreach ($data as $key => $val) {
                
                switch ($val['type_id']) {
                    case 1: //包邮
                        if ($refundAfterMoney < $val['full_money']) {
                            $returnData['remark'][$key]['msg']    =   '退款后，订单总金额已低于' . $val['full_money'] . '元,您将不能享受满' . $val['full_money'] . '元免邮活动。';
                            $returnData['remark'][$key]['msg']   .=   '系统将自动扣出' . $orders['express_price'] . '元邮费,谢谢。';
                            $returnData['remark'][$key]['money']  =   $orders['express_price'];
                            $returnData['remark'][$key]['id']     =   $val['id'];
                        }
                        break;
                    case 2: //满送
                        if ($refundAfterMoney < $val['full_money']) {
                            $expressPrice   =   self::getExpressPrice($val['full_value'], $orders['express_id'], $orders['o_id'], $orders['express_type'], $orders['seller_id']);
                            $returnData['remark'][$key]['msg']    =   '退款后，订单总金额已低于' . $val['full_money'] . '元,您将不能享受满' . $val['full_money'] . '赠送礼品活动。';
                            $returnData['remark'][$key]['msg']   .=   '系统将自动扣出' . round($expressPrice, 2) . '元当做礼品邮费,谢谢。';
                            $returnData['remark'][$key]['money']  =   round($expressPrice, 2);
                            $returnData['remark'][$key]['id']     =   $val['id'];
                        }
                        break;
                    case 3: //满减
                        if ($refundAfterMoney < $val['full_money']) {
                            $fullValue  =   round($val['full_value'], 2);
                            $returnData['remark'][$key]['msg']          =   '退款后，订单总金额已低于' . $val['full_money'] . '元,您将不能享受满' . $val['full_money'] . '立减'.$fullValue.'元活动。';
                            $returnData['remark'][$key]['msg']         .=   '系统将自动扣出' . $fullValue . '元立减活动费用,谢谢。';
                            $returnData['remark'][$key]['money']        =   $fullValue;
                            $returnData['remark'][$key]['percentage']   =   round($fullValue * round(($goods['total_price'] / $orders['goods_price']), 2), 2);
                            $returnData['remark'][$key]['id']           =   $val['id'];
                            //$returnData['remark'][$key]['percentage']   =   1 - ($fullValue * 0.1);   //唐宝支付
                        }
                        break;
                    /*case 4: //唐宝支付
                        $fullValue  =   round($val['full_value'], 2);
                        $returnData['remark'][$key]['percentage']       =   round(1 - ($fullValue * 0.1), 2);   //唐宝支付
                        break;*/
                }
                if ($returnData['remark'][$key]['money']) {
                    $returnData['ids']          .=  $returnData['remark'][$key]['id'] . ',';
                    $returnData['lessMoney']    +=  $returnData['remark'][$key]['money'];   //不能退金额
                    $returnData['msg']          .=  '<br />' . $returnData['remark'][$key]['msg'];
                }
            }
            if (!empty($returnData)) {
                return $returnData;
            }
        }
        return false;
    }
    
    /**
     * 更新退款状态
     * @param staring $ids
     */
    public static function setRefundStatus($ids) {
        $model  =   M('activity_participate');
        $model->startTrans();
        if ($model->where(['id' => ['in', $ids]])->save(['is_refund' => 1])) {
            $model->commit();
            return true;
        }
        $model->rollback();
        return false;
    }
    
    /**
     * 查看当前商家是否已参与消费升级活动
     * @param unknown $data
     */
    public static function getUpgradeActivity($orders, $pay_price) {
        if (empty($orders)) return false;   //如果订单为空则返回false
        if (M('activity_participate')->where(['s_no' => $orders['s_no'], 'type_id' => ['in', '5,6']])->getField('id') > 0) return false;    //如果订单有参与0元购或秒杀则不参与消费升级促销
        $map    =   [
            'shop_id'       =>  $orders['shop_id'],
            'type_id'       =>  7,
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],      //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
        ];
        $data   =   M('activity')->where($map)->find();
        if (!$data) goto error;
        //参与者加+
        if (self::activitySaleInc($data['id']) == false) {
            goto error;
        }
        
        $aData['calc_before_money'] =   $pay_price;
        $aData['uid']               =   $orders['uid'];
        $aData['s_no']              =   $orders['s_no'];
        $aData['type_id']           =   $data['type_id'];
        $aData['activity_id']       =   $data['id'];
        $aData['full_value']        =   $pay_price - $orders['express_price_edit'];
        $aData['remark']            =   '享受了消费金额累积升级活动';
        $aData['max_num']           =   $data['max_num'];
        $aData['full_money']        =   $data['full_money'];
        $aData['shop_id']           =   $orders['shop_id'];
        $aData['calc_after_money']  =   $pay_price;
        //加入参与记录
        if (self::isRecor($orders['s_no'], $data['type_id'], $orders['uid']) == true) {//未添加至参与记录则添加至参与记录
            if (self::recording($aData) == false) {
                goto error;
            }
        }
        return true;
        error:
            return false;
    }
    
    /**
     * 判断当前商品是否参与0元购或者限购活动
     * @param int $shop_id
     * @param int $goods_id
     * @return boolean
     */
    public static function getSpikeAndRestriction($shop_id, $goods_id, $orders = null, $uid = null) {
        $map    =   [
            'shop_id'       =>  $shop_id,
            'type_id'       =>  ['in', '5,6'],                              //5为0元购，6为秒杀
            'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],      //开始时间小于当前时间
            'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
            'status'        =>  1,
            '_string'       =>  'FIND_IN_SET("'.$goods_id.'", full_value) AND (sku_num = 0 OR sku_num > sale_num)',   //商品ID
        ];
        $data   =   M('activity')->where($map)->field('atime,goods', true)->order('type_id asc')->find();
        if (!$data) goto error;
        if ($data['sku_num'] > 0) {
            $hasNum =   $data['sku_num'] - $data['sale_num'];   //还剩多少商品
            if ($hasNum <= 0) goto error;
        }
        $uid    =   $uid ? : $orders['uid'];
        if (!$uid) return $data;
        //$maxNum =   D('ActivityRestrictionView')->where(['activity_id' => $data['id'], 'uid' => $uid, 'status' => ['in', '0,1']])->field('max_num,SUM(num) as num,goods_id')->find();
        $maxNum =   M('activity_participate')->where(['activity_id' => $data['id'], 'uid' => $uid, 'status' => ['in', '0,1']])->field('SUM(buy_num) as num')->select();
        $maxNum =   $maxNum[0];
        //默认为单个
        if ($data['is_single'] == 0 && $data['max_num'] > 0) {
            //多个同时进行
            $ordersData     =   D('ActivityOrdersShopGoodsView')->where(['activity_id' => $data['id'], 'uid' => $uid, 'status' => ['neq', 10]])->field('goods_id,num')->select();
            $maxNum['num']  =   0;
            $tmp            =   0;
            if ($ordersData) {
                foreach ($ordersData as $v) {
                    if ($goods_id == $v['goods_id']) {
                        $tmp               +=  $v['num'];
                        $maxNum['num']      =   $tmp;
                    }
                }
            }
        }
        if ($data['max_num'] > 0 && $maxNum['num']) {
            if ($maxNum['num'] >= $data['max_num']) goto error;   //限购
            $data['max_num']    -=  $maxNum['num']; //减去之前参与的
            if ($hasNum < $data['max_num']) {   //只能购买
                $data['max_num']    -=  $hasNum;
            }
        } elseif ($data['max_num'] > 0) {
            if ($hasNum < $data['max_num']) {   //只能购买
                $data['max_num']    -=  $hasNum;
            }
        }
        if ($orders) {
            $data['participateId']   =   self::insertSpikeData($data, $orders);
            if ($data['participateId'] == false) goto error;
        }
        return $data;
        error:
            return false;
    }
    
    /**
     * 写入数据
     * @param array $data
     * @param array $orders
     */
    public static function insertSpikeData($data, $orders) {
        if (self::activitySaleInc($data['id'], $orders['count_goods_num']) == false) {
            goto error;
        }
        $aData['uid']               =   $orders['uid'];
        $aData['s_no']              =   $orders['s_no'];
        $aData['type_id']           =   $data['type_id'];
        $aData['activity_id']       =   $data['id'];
        $aData['max_num']           =   $data['max_num'];
        $aData['full_money']        =   $data['full_money'];
        $aData['shop_id']           =   $orders['shop_id'];
        $aData['buy_num']           =   $orders['count_goods_num']; //当前购买数量
        switch ($data['type_id']) {
            case 5: //0
                $aData['remark']            =   '享受了0元购活动';
                $aData['full_value']        =   $orders['pay_price'] - $orders['express_price_edit'];
                $aData['calc_before_money'] =   $orders['pay_price'];
                $aData['calc_after_money']  =   $orders['express_price_edit'];
                break;
            case 6: //秒
                $aData['remark']            =   '享受了秒杀活动';
                $aData['full_value']        =   ($orders['pay_price'] - $orders['express_price_edit']) - ($data['full_money'] * $orders['goods_num']);
                $aData['calc_before_money'] =   $orders['pay_price'];
                $aData['calc_after_money']  =   ($data['full_money'] * $orders['goods_num']) + $orders['express_price_edit'];
                break;
        }

        //加入参与记录
        if (self::isRecor($orders['s_no'], $data['type_id'], $orders['uid']) == true) {//未添加至参与记录则添加至参与记录
            $lastId =   self::recording($aData);
            if (!$lastId) goto error;
        }
        return $lastId;
        error:
            return false;
    }
    
    /**
     * 获取赠送商品快递邮费
     * @param string  $goodsIds 赠送商品的id
     * @param integer $eTplId   运费模板ID
     * @param integer $oId      父订单ID
     * @param integer $express_type 快递类型
     */
    private static function getExpressPrice($goodsIds, $eTplId, $oId, $express_type, $sellerId) {
        $city   =   M('orders')->cache(true)->where(['id' => $oId])->getField('city');   //获取所在城市
        //获取商品
        $sql    =   'SELECT distinct goods_id,weight FROM ' . C('DB_PREFIX') . 'goods_attr_list WHERE `goods_id` IN('.trim($goodsIds, ',').')';
        $goods  =   M()->query($sql);
        if ($eTplId > 0) {
            $tplMap     =   ['id' => $eTplId];
        } else {
            $tplMap     =   ['uid' => $sellerId, 'is_free' => 0];
        }
        //取商家不包邮的快递模板
        $do=D('Common/ExpressTplRelation');
        $tpl 	=$do->cache(true)->relation(true)
        ->where($tplMap)
        ->field('id,is_free,unit,is_express,express_default_first_unit,express_default_first_price,express_default_next_unit,express_default_next_price,is_ems,ems_default_first_unit,ems_default_first_price,ems_default_next_unit,ems_default_next_price')
        ->find();
        $goods_total    =   [];
        $gs             =   [];
        foreach ($goods as &$val) {
            $gs['num']          +=   1;
            $gs['total_weight'] +=  $val['weight'];
            $goods_total[$tpl['id']]['num'] 			+=$val['num'];//只能赠送1件
            $goods_total[$tpl['id']]['total_weight'] 	+=$val['weight'];
        }
        unset($val);
        //商品
        //按件计的运费
        $res[1]		=  array(
            'num'	=>  0,	//不同运模板笔数
            'first'	=>  0,	//首重/件金额
            'next'	=>  0,	//续重/件金额 
        );
        
        //按重量计的运费
        $res[2]		=  array(
            'num'	=>  0,
            'first'	=>  0,
            'next'	=>  0,
        );
        
        //同类型运费模板首重/件取最大值，续重或件费用累加
        foreach($goods_total as $key => $val){
            $res[$tpl['unit']]['num'] = $val['num'];
            $price  =   self::calcGoodsExpressPrice($tpl, $gs, $city, $express_type);
            if($res[$tpl['unit']]['first'] > $price['first']) {
                $res[$tpl['unit']]['next']    +=$price['next2'];
            } else {
                $res[$tpl['unit']]['next']    +=$price['next'];
                $res[$tpl['unit']]['first']    =$price['first'];
            }
        }
        $express_price  =   round(($res[1]['first']+$res[1]['next']),2) + round(($res[2]['first']+$res[2]['next']),2);

        return $express_price;
    }
    
    /**
     * 计算商品运费
     * @param array 	$tpl 运费模板
     * @param array 	$goods 待计运费的商品
     * @param int 	$city 	城市ID
     * @param int 	$express_type 发货方式,1=快递 ,2=Ems
     */
    private static function calcGoodsExpressPrice($tpl,$goods,$city,$express_type){
        if($express_type==1 && $tpl['is_express']==1){	//快递默认运费
            $logsic=array(
                'unit'          =>$tpl['unit'],
                'first_unit'    =>$tpl['express_default_first_unit'],
                'first_price'   =>$tpl['express_default_first_price'],
                'next_unit'     =>$tpl['express_default_next_unit'],
                'next_price'    =>$tpl['express_default_next_price'],
            );
            $express_type=1;
        }else{	//EMS默认运费，如果选择的发货方式为快递是，运费模板中没有启用快递将默认按EMS计算
            $logsic=array(
                'unit'          =>$tpl['unit'],
                'first_unit'    =>$tpl['ems_default_first_unit'],
                'first_price'   =>$tpl['ems_default_first_price'],
                'next_unit'     =>$tpl['ems_default_next_unit'],
                'next_price'    =>$tpl['ems_default_next_price'],
            );
            $express_type=2;
        }
    
        //根据地区查找运费配置
        if($tpl['express_area']){
            foreach($tpl['express_area'] as $val){
                if($val['type']==$express_type){
                    $val['city_ids']=explode(',',$val['city_ids']);
                    if(in_array($city, $val['city_ids'])){
                        $logsic['first_unit']   =$val['first_unit'];
                        $logsic['first_price']  =$val['first_price'];
                        $logsic['next_unit']    =$val['next_unit'];
                        $logsic['next_price']   =$val['next_price'];
                        //dump($city);
                        break;
                    }
                }
            }
        }
    
        $price['first']=$logsic['first_price'];	//首重/件费用
    
        //续重/件费用
        if($logsic['unit']==2){ //计重方式
            if($goods['total_weight']>$logsic['first_unit']){
                $price['next'] = ceil(($goods['total_weight']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];
    
                //将首重也纳为续重
                $price['next2'] = ceil($goods['total_weight']/$logsic['next_unit']) * $logsic['next_price'];
            }
        }else{  //计件方式
            if($goods['num']>$logsic['first_unit']){
                $price['next'] = ceil(($goods['num']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];
    
                //将首件也纳为续件
                $price['next2'] = ceil($goods['num']/$logsic['next_unit']) * $logsic['next_price'];
            }
        }
        return $price;
    }
    
    /**
     * 退款后减钱，主要针对累积升级
     * @param string $s_no
     * @param number $uid
     * @param float $refundMoney
     * @param number $typeId
     */
    public static function refundLessMoney($s_no, $uid, $refundMoney, $typeId = 7) {
        //查看当前订单是否有参与活动

//        $map = [
//            's_no'      => $s_no,
//            'status'    => ['in', '0,1'],
//            'uid'       => $uid,
//        ];
//
//        $ids = M('activity_participate')->where($map)->getField('activity_id', true);
//
//        if ($ids) {
//            $ids = join(',', $ids);
//            M('activity')->where(['id' => ['in', $ids]])->setDec('payment_num', 1);
//        }

        $id =   M('activity_participate')->where(['s_no' => $s_no, 'type_id' => $typeId, 'status' => ['in', '0,1'], 'uid' => $uid])->getField('id');
        if ($id > 0) {
            if (M('activity_participate')->where(['id' => $id])->setDec('full_value', $refundMoney)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 关闭活动，主要针对累积升级
     * @param string $s_no
     * @param number $uid
     * @param number $typeId
     * @return boolean
     */
    public static function refundSetStatus($s_no, $uid, $typeId = 7) {
        //查看当前订单是否有参与活动
        $id =   M('activity_participate')->where(['s_no' => $s_no, 'type_id' => $typeId, 'status' => ['in', '0,1'], 'uid' => $uid])->getField('id');
        if ($id > 0) {
            if (M('activity_participate')->where(['id' => $id])->save(['status' => 2])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 已参与0元购及秒杀活动的商品不能修改为包邮
     * @param unknown $goodsId
     * @param unknown $shopId
     */
    public static function isExpressFree($goodsId, $shopId) {
        $map    =   [
            'shop_id'   =>  $shopId,
            'type_id'   =>  ['in', '5,6'],
            'status'    =>  ['in', '0,1'],
            '_string'   =>  'FIND_IN_SET('.$goodsId.', full_value)',
        ];
        if(M('activity')->where($map)->getField('id')) {
            return false;
        }
        return true;
    }
    
    public function __get($name) {
        return $this->$name;
    }
    
    public function __set($name, $value) {
        $this->$name    =   $value;
    }
    
    private static function getData($map) {
        
    }
}