<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 工具类 - 刷单检测
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-04-11
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class ToolsRateController extends ApiController {
    protected $action_logs = array('shop_point','check_orders_shuadan','shop_fraction');

    /**
     * 通过评价ID检测是否为刷单记录,作废，
     * Create by lazycat
     * 2017-04-11
     */
    public function check_rate_item(){
        $this->check('id',false);
        $res = $this->_check_rate_item($this->post);

        $this->apiReturn($res);
    }
    public function _check_rate_item($param){
        $id             = $param['id'];
        $comment        = $param['comment'];
        $pay_time       = $param['pay_time'];
        $goods_price    = $param['goods_price'];


        if(empty($comment)){
            $comment = M('orders_goods_comment')->where(['id' => $id])->field('id,atime,s_id,orders_goods_id,goods_id,uid,seller_id,shop_id,rate,fraction_desc,is_shuadan')->find();
            if(empty($comment)) return ['code' => 0,'msg' => '找不到评价记录！'];
        }
        if($comment['rate'] != 1) return ['code' => 0,'msg' => '不处理中差评记录！'];
        if($comment['is_shuadan'] > 0) return ['code' => 0,'msg' => '该评价已做过处理！'];

        //支付时间
        if(empty($pay_time)){
            $pay_time = M('orders_shop')->where(['id' => $comment['s_id']])->getField('pay_time');
            if(empty($pay_time)) return ['code' => 0,'msg' => '找不到订单记录！'];
        }

        //当前商品金额
        if(empty($goods_price)){
            $goods_price = M('goods')->where(['id' => $comment['goods_id']])->getField('price');
            if(empty($goods_price)) return ['code' => 0,'msg' => '找不到商品记录！'];
        }

        //同一买家，14天内订购同一款商品超过一定量定为刷单记录
        //IP刷单判断

        //交易的14天内如果价格小于商品当前价格的10%，且在5笔以上，当前评价屏蔽
        $map['goods_id']    = $comment['goods_id'];
        $map['rate']        = 1;
        $map['_string']     = '(goods_id in (select goods_id from '.C('DB_PREFIX').'orders_goods where total_price_edit/num<'.($goods_price * 0.2).')) and (s_id in (select id from '.C('DB_PREFIX').'orders_shop where status=5 and pay_time>"'.date('Y-m-d H:i:s',strtotime($pay_time) - 86400 * 14).'" and pay_time<"'.$pay_time.'"))';
        $count = M('orders_goods_comment')->where($map)->count();
        //echo M('orders_goods_comment')->getLastSql();
        if($count > 1){
            if(M('orders_goods_comment')->where(['id' => $id])->setField('is_shuadan',2)){
                return ['code' => 1,'msg' => '刷单评价！'];
            }else return ['code' => 0,'msg' => '更新刷单记录失败！'];
        }else{
            return ['code' => 10,'msg' => '正常评价！'];
        }

    }

    /**
     * 通过订单号检测是否为刷单订单
     * 针对修改商品金额小于原价20%的订单
     * Create by lazycat
     * 2017-04-26
     */
    public function check_orders_shuadan(){
        $this->check('s_no',false);

        $res = $this->_check_orders_shuadan($this->post);
        $this->apiReturn($res);
    }

    public function _check_orders_shuadan($param){
        $count = M('orders_shop')->where(['s_no' => $param['s_no'],'status' => ['in','5,6'],'is_shuadan' => 0])->count();   //只有已评价的订单才参与检测
        if($count == 0) return ['code' => 100,'msg' => '不符合检测规则！'];

        $orders_goods   = M('orders_goods')->where(['s_no' => $param['s_no']])->field('id,goods_id,num,total_price_edit,attr_list_id')->select();
        $goods_ids      = arr_id(['plist' => $orders_goods,'field' => 'goods_id']);
        $goods          = M('goods')->where(['id' => ['in',$goods_ids]])->getField('id,price,sale_num,pr_extra',true);


        $n      = 0;
        $ids    = [];
        foreach ($orders_goods as $val){
            if(($val['total_price_edit'] / $val['num']) < $goods[$val['goods_id']]['price'] * 0.2) {
                $ids[] = $val['id'];
                $goods[$val['goods_id']]['is_shuadan']++;
                $goods[$val['goods_id']]['num'] += $val['num'];
                $n++;
            }
        }


        if($n > 0){
            $do = M();
            $do->startTrans();

            if($this->sw[] = false === M('orders_shop')->where(['s_no' => $param['s_no']])->setField('is_shuadan',2)){
                $msg = '更新刷单记录状态失败！';
                goto error;
            }
			
			//刷单的原因
			$c_id = M('orders_goods_comment')->where(['orders_goods_id' => ['in',$ids],'rate' => 1])->getField("id");
			$data['reason'] = "实付价格小于实际价的20%";
			$data['atime']  = date('Y-m-d H:i:s');
			$data['ip']     = get_client_ip();
			$data['c_id']   = $c_id;
			if($this->sw[] = false === M('orders_apply_reason')->add($data)){
                $msg = '更新刷单原因失败！';
                goto error;
            }

            if($this->sw[] = false === M('orders_goods_comment')->where(['orders_goods_id' => ['in',$ids],'rate' => 1])->save(['is_shuadan' => 2,'point' => 0,'dotime' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',(time() + 86400 * 3))])){
                $msg = '更新评价记录得分失败！';
                goto error;
            }

            //检测到刷单记录，只标记，暂不做任何处理，3天内卖家未申诉再执行处罚处理
            /*
            //删减相对应的销量
            $goods_ids = [];
            foreach($goods as $val){
                if($val['is_shuadan'] > 0) {
                    $goods_ids[] = $val['id'];
                    //刷销量的商品降低权重
                    $sale_num_str = $val['sale_num'] > $val['num'] ? 'sale_num= sale_num-' . $val['num'] : 'sale_num = 0';
                    $pr_extra_str = $val['pr_extra'] < -30 ? 'is_display=0' : 'pr_extra=pr_extra+' . (strlen($val['num']) * -3);
                    $sql = 'update ' . C('DB_PREFIX') . 'goods set ' . $sale_num_str . ',' . $pr_extra_str . ' where id=' . $val['id'];
                    if (!$this->sw[] = $do->execute($sql)) {
                        $msg = '[goods_id=' . $val['id'] . ']删减销量失败！';
                    }
                }
            }

            //删减属性销量，有可能存在属性变更情况，所以不列入事务
            foreach($orders_goods as $val){
                if(in_array($val['goods_id'],$goods_ids)) {
                    M('goods_attr_list')->where(['id' => $val['attr_list_id'],'sale_num' => ['gt',$val['num']]])->setDec('sale_num',$val['num']);
                }
            }
            */

            $do->commit();

            //评价处理
            $this->_orders_point(['s_no' => $param['s_no']]);
            return ['code' => 1,'msg' => '更新刷单记录状态成功！'];

            error:
            $do->rollback();
            return ['code' => 0,'msg' => $msg ? $msg : '更新刷单记录状态失败！'];
        }

        return ['code' => 100,'msg' => '正常订单！'];
    }


    /**
     * 订单评价计分处理
     * 对没有刷单的记录才做计分处理
     * Create by lazycat
     * 2017-05-01
     */
    public function _orders_point($param){
        $ors = M('orders_shop')->cache(true)->where(['s_no' => $param['s_no'],'status' => ['in','5,6']])->field('pay_time,uid,seller_id')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '不符合条件的订单！'];

        $list = M('orders_goods_comment')->where(['s_no' => $param['s_no'],'rate' => ['neq',0],'is_shuadan' => 0])->field('id,s_no,shop_id,goods_id,rate,is_shuadan,point')->order('id asc')->select();

        if($list) {
            $res = [];
            foreach ($list as $val) {
                $res[] = $this->_rate_point(['id' => $val['id']], $val, $ors);
            }

            return ['code' => 1,'data' => $res];
        }
        return ['code' => 3,'msg' => '暂无符合计分条件的评价记录！'];
    }


    /**
     * 评价加分判定，正常的评价才进行计分计算
     * Create by lazycat
     * 2017-05-01
     * 计分规则(含匿名评价)：
     *　1)每个自然月中，相同买家和卖家之间的评价计分不得超过6分(以淘宝订单创建的时间计算)。超出计分规则范围的评价将不计分。
     *　(解释：每个自然月同买卖家之间评价计分在[-6,+6]之间，每个自然月相同买卖家之间总分不超过6分，也就是说总分在-6和+6之间，例如买家先给卖家6个差评，再给1个好评和1个差评，则7个差评都会生效计分。)
     *　2)若14天内(以订单创建的时间(付款)计算)相同买卖家之间就同一个商品进行评价，多个好评只计一分，多个差评只记-1分。
     */
    public function rate_point(){
        $this->check('id',false);

        $res = $this->_rate_point($this->post);
        $this->apiReturn($res);
    }

    public function _rate_point($param,$comment=null,$ors=null){
        if(is_null($comment)) {
            $comment = M('orders_goods_comment')->where(['id' => $param['id'],'rate' => ['neq',0],'is_shuadan' => 0])->field('id,s_no,shop_id,goods_id,rate,is_shuadan,point')->find();
        }
        if(empty($comment))             return ['code' => 0,'msg' => '评价记录不符合计分条件！'];
        if($comment['rate'] == 0)       return ['code' => 0,'msg' => '中评不参与计分计算！'];
        if($comment['is_shuadan'] != 0) return ['code' => 0,'msg' => '正常的评价记录才能参与计分计算！'];

        if(is_null($ors)) {
            $ors = M('orders_shop')->cache(true)->where(['s_no' => $comment['s_no']])->field('status,pay_time,uid,seller_id')->find();
        }
        if(!in_array($ors['status'],[5,6])) return ['code' => 0,'msg' => '订单状态错误！'];

        $point  = $comment['rate'];

        //自然月同一买卖双方
        $month_point    = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d',strtotime($ors['pay_time'])).' 00:00:00" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($month_point) && ($month_point >=6 || $month_point <= -6)) $point = 0;

        //14天内同一商品评价
        $point_14       = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'goods_id' => $comment['goods_id'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d H:i:s',strtotime($ors['pay_time']) - 86400 * 14).'" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($point_14) && ($point_14 >= 1 || $point_14 <= -1)) $point = 0;


        $do = M();
        $do->startTrans();

        if($comment['point'] != $point) {
            if (!$this->sw[] = M('orders_goods_comment')->where(['id' => $comment['id']])->setField('point', $point)) {
                $msg = '更新评价记录分数失败！';
                goto error;
            }
        }

        //$shop_point     = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'shop_id' => $comment['shop_id'],'is_shuadan' => 0])->sum('point');
        $shop_point     = M('orders_goods_comment')->where(['shop_id' => $comment['shop_id'],'is_shuadan' => 0])->sum('point');
        if($this->sw[] = false === M('shop')->where(['id' => $comment['shop_id']])->save(['shop_point' => $shop_point,'shop_level' => $this->_shop_level($shop_point)])){
            $msg = '更新店铺分数失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => '更新分数失败'];
    }

	
    /**
	
     * @param string $param['id'] 评价ID
	 
     * 疑似刷单申诉处理结果评价加分判定
     * Create by lizuheng
     * 2017-05-06
     * 计分规则(含匿名评价)：
     *　1)每个自然月中，相同买家和卖家之间的评价计分不得超过6分(以淘宝订单创建的时间计算)。超出计分规则范围的评价将不计分。
     *　(解释：每个自然月同买卖家之间评价计分在[-6,+6]之间，每个自然月相同买卖家之间总分不超过6分，也就是说总分在-6和+6之间，例如买家先给卖家6个差评，再给1个好评和1个差评，则7个差评都会生效计分。)
     *　2)若14天内(以订单创建的时间(付款)计算)相同买卖家之间就同一个商品进行评价，多个好评只计一分，多个差评只记-1分。
     */
    public function appeal_rate_point(){
        $this->check('id',false);

        $res = $this->_appeal_rate_point($this->post);
        $this->apiReturn($res);
    }

    public function _appeal_rate_point($param,$comment=null,$ors=null){
        if(is_null($comment)) {
            $comment = M('orders_goods_comment')->where(['id' => $param['id'],'rate' => ['neq',0],'is_shuadan' => 0])->field('id,s_no,shop_id,goods_id,rate,is_shuadan,point')->find();
        }
        if(empty($comment))             return ['code' => 0,'msg' => '评价记录不符合计分条件！'];
        if($comment['rate'] == 0)       return ['code' => 0,'msg' => '中评不参与计分计算！'];
        if($comment['is_shuadan'] != 0) return ['code' => 0,'msg' => '正常的评价记录才能参与计分计算！'];

        if(is_null($ors)) {
            $ors = M('orders_shop')->cache(true)->where(['s_no' => $comment['s_no']])->field('status,pay_time,uid,seller_id')->find();
        }
        if(!in_array($ors['status'],[5,6])) return ['code' => 0,'msg' => '订单状态错误！'];

        $point  = $comment['rate'];

        //自然月同一买卖双方
        $month_point    = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d',strtotime($ors['pay_time'])).' 00:00:00" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($month_point) && ($month_point >=6 || $month_point <= -6)) $point = 0;

        //14天内同一商品评价
        $point_14       = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'goods_id' => $comment['goods_id'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d H:i:s',strtotime($ors['pay_time']) - 86400 * 14).'" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($point_14) && ($point_14 >= 1 || $point_14 <= -1)) $point = 0;


        $do = M();
        $do->startTrans();

        if($comment['point'] != $point) {
            if (!$this->sw[] = M('orders_goods_comment')->where(['id' => $comment['id']])->setField('point', $point)) {
                $msg = '更新评价记录分数失败！';
                goto error;
            }
        }

        //$shop_point     = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'shop_id' => $comment['shop_id'],'is_shuadan' => 0])->sum('point');
        $shop_point     = M('orders_goods_comment')->where(['shop_id' => $comment['shop_id'],'is_shuadan' => 0])->sum('point');
        if($this->sw[] = false === M('shop')->where(['id' => $comment['shop_id']])->save(['shop_point' => $shop_point,'shop_level' => $this->_shop_level($shop_point)])){
            $msg = '更新店铺分数失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => '更新分数失败'];
    }
	
    /**
     * 计算店铺等级
     * Create by lazycat
     * 2017-04-28
     */

    /**
     * D（铜币）	5-20
     * D+（2铜币）	21-40
     * D++（3铜币）	41-60
     * C--（铜宝）	61-80
     * C-（1铜宝）	81-100
     * C（2铜宝）	101-200
     * C+（3铜宝）	201-400
     * C++（4铜宝）	401-700
     * B--（银宝）	701-1000
     * B-（1银宝）	1001-1500
     * B（2银宝）	1501-3000
     * B+（3银宝）	3001-5000
     * B++（4银宝）	5001-10000
     * A--（金宝）	10001-20000
     * A-（1金宝）	20001-50000
     * A（2金宝）	50001-80000
     * A+（3金宝）	80001-150000
     * A++（4金宝）	150001-200000
     * S(玉如意)	200000以上
     */
    public function _shop_level($point){
        $level = 0;
        if($point >= 5 && $point < 41) $level = 1;
        elseif($point >= 41 && $point < 61) $level = 2;
        elseif($point >= 61 && $point < 81) $level = 3;
        elseif($point >= 81 && $point < 101) $level = 4;
        elseif($point >= 101 && $point < 201) $level = 5;
        elseif($point >= 201 && $point < 401) $level = 6;
        elseif($point >= 401 && $point < 701) $level = 7;
        elseif($point >= 701 && $point < 1001) $level = 8;
        elseif($point >= 1001 && $point < 1501) $level = 9;
        elseif($point >= 1501 && $point < 3001) $level = 10;
        elseif($point >= 3001 && $point < 5001) $level = 11;
        elseif($point >= 5001 && $point < 10001) $level = 12;
        elseif($point >= 10001 && $point < 20001) $level = 13;
        elseif($point >= 20001 && $point < 50001) $level = 14;
        elseif($point >= 50001 && $point < 80001) $level = 15;
        elseif($point >= 80001 && $point < 150001) $level = 16;
        elseif($point >= 150001 && $point < 200001) $level = 17;
        elseif($point >= 200001) $level = 18;

        return $level;
    }

    /**
     * 店铺计分、等级
     * Create by lazycat
     * 2017-05-01
     */
    public function shop_point(){
        $this->check('id',false);

        $res = $this->_shop_point($this->post);
        $this->apiReturn($res);
    }

    public function _shop_point($param){
        $shop_point     = M('orders_goods_comment')->where(['shop_id' => $param['id'],'is_shuadan' => 0])->sum('point');
        M('shop')->where(['id' => $param['id']])->save(['shop_point' => $shop_point,'shop_level' => $this->_shop_level($shop_point)]);
        return ['code' => 1];
    }

    /**
     * 计算店铺动态评分
     * Create by lazycat
     * 2017-05-01
     */
    public function shop_fraction(){
        $this->check('id',false);

        $res = $this->_shop_fraction($this->post);
        $this->apiReturn($res);
    }

    public function _shop_fraction($param){
        //店铺总体综合评价
        $shop_rate = M()->query('select count(*) as num,sum(fraction_speed) as fraction_speed,sum(fraction_service) as fraction_service,sum(fraction_desc) as fraction_desc,sum(fraction) as fraction from '.C('DB_PREFIX').'orders_shop_comment where shop_id='.$param['id']);
        //print_r($shop_rate[0]);

        //系统默认赠送8个5分,2个4分（即10笔=48分，相当于默认评分为4.8分），避免评价少时计算出来的分数太差，
        $give   = 10;
        $tmp    = [];
        $tmp['fraction_speed']      = ($shop_rate[0]['fraction_speed'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction_service']    = ($shop_rate[0]['fraction_service'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction_desc']       = ($shop_rate[0]['fraction_desc'] + 48) / ($give + $shop_rate[0]['num']);
        $tmp['fraction']            = ($shop_rate[0]['fraction'] + 48) / ($give + $shop_rate[0]['num']);

        M('shop')->where(['id' => $param['id']])->save($tmp);
        return ['code' => 1];
    }

    /**
     * 刷单惩罚处理
     * Create by lazycat
     * 2017-05-03
     *
     * @param int $auto 系统自动操作
     * @param int $id   评价ID
     */

    public function rate_punish(){
        $this->check($this->_field('auto','id'),false);

        $res = $this->_rate_punish($this->post);
        $this->apiReturn($res);
    }

    public function _rate_punish($param){   //状态码为100时为自动从队列中移除
        $rs = M('orders_goods_comment')->where(['id' => $param['id'],'is_shuadan' => 2])->field('id,s_id,orders_goods_id,attr_list_id,goods_id,next_time')->find();
        if(empty($rs)) return ['code' => 100,'msg' => '找不到记录！'];
        if($param['auto'] == 1){    //系统自动执行时需要验证下一步执行时间
            if(date('Y-m-d H:i:s') > $rs['next_time'])  return ['code' => 100,'msg' => '未到执行时间！'];
        }

        $orders_goods = M('orders_goods')->where(['id' => $rs['orders_goods_id']])->field('id,num')->find();

        $do = M();
        $do->startTrans();

        if(!$this->sw[] = M('orders_goods_comment')->where(['id' => $param['id']])->save(['is_shuadan' => 1,'dotime' => date('Y-m-d H:i:s')])){
            $msg = '更新评价刷单状态失败！';
            goto error;
        }

        //删减销量
        if(!$this->sw[] = M('goods')->where(['id' => $rs['goods_id'],'num' => ['egt',$orders_goods['num']]])->setDec('num',$orders_goods['num'])){
            $msg = '删减商品销量失败！';
            goto error;
        }

        //删减库存记录销量，由于库存记录可能随时会被变更，所以不列入事务
        M('goods_attr_list')->where(['id' => $rs['attr_list_id'],'num' => ['egt',$orders_goods['num']]])->setDec('num',$orders_goods['num']);

        //是否已全部标记为刷单订单
        $count = M('orders_goods_comment')->where(['s_id' => $rs['s_id'],'is_shuadan' => 2])->count();
        if($count == 0){
            if(!$this->sw[] = M('orders_shop')->where(['id' => $rs['s_id']])->setField('is_shuadan',1)){
                $msg = '标记订单刷单状态失败！';
                goto error;
            }
        }

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg : '更新失败！'];
    }

    /**
     * 取消刷单状态
     * Create by lazycat
     * 2017-05-03
     * @param int $id 评价ID
     */
    public function cancel_shuadan(){
        $this->check('id');

        $res = $this->_cancel_shuadan($this->post);
        $this->apiReturn($res);
    }

    public function _cancel_shuadan($param){

    }



}