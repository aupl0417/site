<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 数据统计
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
use Common\Controller\OrdersController;
use Common\Controller\SellerOrdersController;
class TotalController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 统计商家在线商品数量
    * @param int    $_POST['uid']   用户ID
    */
    public function seller_goods_online($uid){
        $goods_num=M('goods')->where(['seller_id'=>$uid,'status'=>1,'num'=>['gt',0]])->count();
        M('shop')->where(['uid' => $uid])->save(['goods_num'=>$goods_num]);

        return $goods_num;   
    }

    /**
    * 用户广告统计
    * @param int    $_POST['uid']   用户ID
    */
    public function user_ad($uid){
        //广告统计
        $do=M('ad');
        $result['status'][0]    =$do->where(['uid' => $uid,'status' => 0])->count();    //待付款
        $result['status'][1]    =$do->where(['uid' => $uid,'status' => 1])->count();    //已付款
        $result['status'][2]    =$do->where(['uid' => $uid,'status' => 2])->count();    //强制下架

        $result['status']['all']=array_sum($result['status']);

        $result['status'][3]    =$do->where(['uid' => $uid,'status' => 1,'days' => ['like','%'.date('Y-m-d').'%']])->count();   //投放中
        $result['status'][4]    =$do->where(['uid' => $uid,'status' => 1,'sday' => ['gt',date('Y-m-d')]])->count(); //待投放
        $result['status'][5]    =$do->where(['uid' => $uid,'status' => 1,'eday' => ['lt',date('Y-m-d')]])->count(); //已过期

        //素材统计
        $do=M('ad_sucai');
        $result['sucai'][0]     =$do->where(['uid' => $uid,'status' => 0])->count();    //待审核
        $result['sucai'][1]     =$do->where(['uid' => $uid,'status' => 1])->count();    //审核通过
        $result['sucai'][2]     =$do->where(['uid' => $uid,'status' => 2])->count();    //审核未通过

        $result['sucai']['all'] =array_sum($result['sucai']);

        //消费
        $result['money']=M('ad')->where(['uid' => $uid,'status' => 1])->sum('money_pay');

        return $result;
    }

    /**
    * 卖家中心首页-基础数据统计
    * @param int    $_POST['uid']   用户ID
    */
    public function seller_ucenter($uid){
        $result['orders']   =   $this->seller_orders($uid);
        $result['goods']    =   $this->seller_goods($uid);
        $result['illegl']   =   $this->seller_illegl($uid);
        $result['sale_total']   =   $this->sale_total($uid);
        $result['shop_fav']     =   $this->shop_fav($uid);
        $result['goods_fav']    =   $this->goods_fav($uid);
        $result['wait']         =   $this->seller_wait_money($uid);
        return $result;
    }


    /**
    * 卖家订单统计
    * @param int    $_POST['uid']   用户ID
    */
    public function seller_orders($uid){
        $orders=new SellerOrdersController(['seller_id' => $uid]);
        $res=$orders->s_count();

        return $res['data'];
    }

    /**
    * 卖家商品统计
    * @param int    $_POST['uid']   用户ID
    */
    public function seller_goods($uid){
        $do=M('goods');

        $status=[1,2,3,4,5,6];
        foreach($status as $val){
			if($val == 1){
				//统计商家在线商品数量时，不统计库存为0的商品
				$result[$val]   =$do->cache(true,5)->where(['seller_id' => $uid,'status' => $val,'num'=>['gt',0]])->count();
			}else if($val == 99){
				//统计在线并且库存为0的商品
				
			}else{
				$result[$val]   =$do->cache(true,5)->where(['seller_id' => $uid,'status' => $val])->count();
			}
           
            $result[$val]   =!is_null($result[$val])?$result[$val]:0;
        }

        $result['online_zero']    	=$do->cache(true,5)->where(['seller_id' => $uid,'status' => 1,'num'=>['eq',0]])->count();
        $result['best']     		=$do->cache(true,5)->where(['seller_id' => $uid,'status' => 1,'is_best' => 1])->count();

        return $result;
    }

    /**
    * 卖家违规商品统计
    * @param int    $_POST['uid']   用户ID
    */
    public function seller_illegl($uid){
        $do=M('goods_illegl');
        // <=2分 一般违规,>2分 严重违规
        $result[1]      = $do->cache(true,5)->where(['uid' => $uid,'status' =>['gt',0],'illegl_point' => ['elt',2],'_string'=>'date_format(atime,"%Y")="'.date('Y').'"'])->count();
        $result[2]      = $do->cache(true,5)->where(['uid' => $uid,'status' =>['gt',0],'illegl_point' => ['gt',2],'_string'=>'date_format(atime,"%Y")="'.date('Y').'"'])->count();

        return $result;
    }

    /**
     * 分账模式为“扣除货款”，已确认收货未到账金额统计
     */
    public function seller_wait_money($uid){
        $do = M('orders_shop');
        $result = $do->where(['inventory_type' => 0,'seller_id' => $uid,'receipt_time' => ['gt',date('Y-m-d H:i:s', time() - (86400 * 10))]])->sum('pay_price - refund_price - refund_express');

        return $result > 0 ? $result : 0;
    }

    /**
    * 卖家-统计某一天数据
    * @param int   $uid     用户ID
    * @param date  $day     日期，如 2016-01-01
    */
    public function sale_total($uid,$day=''){
        $day=$day?$day:date('Y-m-d',time()-86400);
        //后续可记录到数据库中
        $res=M()->cache(true,86400)->query('select count(*) as num,sum(pay_price) as money,sum(goods_num) as goods_num from '.C('DB_PREFIX').'orders_shop where seller_id='.$uid.' and date_format(pay_time,"%Y-%m-%d")="'.$day.'"');
        //$result['sql'] = M()->getlastsql();
        $result['num']          =!is_null($res[0]['num'])?$res[0]['num']:0;   //订单数
        $result['money']        =!is_null($res[0]['money'])?$res[0]['money']:0;   //销售金额
        $result['money']        =number_format($result['money'],2);
        $result['goods_num']    =!is_null($res[0]['goods_num'])?$res[0]['goods_num']:0;   //商品数量 

        //$result['buyer']        =M('orders_shop')->cache(true,86400)->where(['seller_id' => $uid,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.$day.'"'])->group('uid')->count();
        $result['buyer']        = M('orders_shop')->where(['seller_id' => $uid,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.$day.'"'])->field('count(distinct(uid)) as num')->find();
        $result['buyer']        =$result['buyer']['num'];   //买家数量
        $result['price']        =number_format($result['money']/$result['buyer'],2);    //客单价
        $result['refund']       =M('refund')->cache(true,86400)->where(['seller_id' => $uid,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$day.'"'])->count();
		//$result['sql3'] = M()->getlastsql();
        return $result;
    }


    /**
    * 店铺收藏统计
    * @param int    $_POST['uid']   用户ID
    */
    public function shop_fav($uid){
        $count=$do=M('shop_fav')->where(['uid' => $uid])->count();
        return $count?$count:0;
    }

    /**
    * 商品收藏统计
    * @param int    $_POST['uid']   用户ID
    */
    public function goods_fav($uid){
        $count=$do=M('goods_fav')->where(['uid' => $uid])->count();
        return $count?$count:0;
    }

    /**
    * 买家订单统计
    * @param int    $_POST['uid']   用户ID
    */
    public function buyer_orders($uid){
        $orders=new OrdersController(['uid' => $uid]);
        $res=$orders->b_count();

        return $res['data'];
    }    

    /**
    * 买家首页统计
    * @param int    $_POST['uid']   用户ID
    */
    public function buyer_ucenter($uid){
        $result['orders']       =   $this->buyer_orders($uid);
        $result['shop_fav']     =   $this->shop_fav($uid);
        $result['goods_fav']    =   $this->goods_fav($uid);
        return $result;     
    }

    /**
     * 提供给Work APP 的数据统计
     * 今日付款，今日确认收货，今日退款
     * 2016-11-23
     */
    public function total(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=[];
        if(I('post.day')) $this->need_param[] = 'day';

        $this->_need_param();
        $this->_check_sign();

        $day = I('post.day') ? I('post.day') : date('Y-m-d');
        $is_cache = $day == date('Y-m-d') ? false : false;   //如果统计的是当天数据，则不开启缓存

        //自营店今日付款
        $result['self']['pay']          = M('orders_shop')->cache($is_cache)->where(['_string' => 'DATE_FORMAT(pay_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1)'])->field('sum(pay_price) as pay_moeny,count(*) as pay_num')->find();

        //自营店今日确认收货
        $result['self']['receipt']      = M('orders_shop')->cache($is_cache)->where(['_string' => 'DATE_FORMAT(receipt_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1)'])->field('sum(money) as receipt_money,count(*) as receipt_num')->find();

        //自营店今日退款
        $result['self']['refund']       = M('refund')->cache($is_cache)->where(['status' => 100,'_string' => 'DATE_FORMAT(accept_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1)'])->field('sum(money+refund_express) as refund_money,count(*) as refund_num')->find();

        //自营店待处理退款笔数
        $result['self']['wait_refund']  = M('refund')->cache($is_cache)->where(['status' => ['not in','20,100'],'_string' => 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1)'])->field('sum(money+refund_express) as wait_refund_money,count(*) as wait_refund_num')->find();

        //=================================================

        //非自营店今日付款
        $result['notself']['pay']          = M('orders_shop')->cache($is_cache)->where(['_string' => 'DATE_FORMAT(pay_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1)'])->field('sum(pay_price) as pay_moeny,count(*) as pay_num')->find();

        //非自营店今日确认收货
        $result['notself']['receipt']      = M('orders_shop')->cache($is_cache)->where(['_string' => 'DATE_FORMAT(receipt_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1)'])->field('sum(money) as receipt_money,count(*) as receipt_num')->find();

        //非自营店今日退款
        $result['notself']['refund']       = M('refund')->cache($is_cache)->where(['status' => 100,'_string' => 'DATE_FORMAT(accept_time,"%Y-%m-%d")="' . $day .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1)'])->field('sum(money+refund_express) as refund_money,count(*) as refund_num')->find();

        //非自营店待处理退款笔数
        $result['notself']['wait_refund']  = M('refund')->cache($is_cache)->where(['status' => ['not in','20,100'],'_string' => 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1)'])->field('sum(money+refund_express) as wait_refund_money,count(*) as wait_refund_num')->find();

        //最近5天退款笔数

        for($i=0;$i<30;$i++){
            $tday = date('Y-m-d',strtotime($day) - ($i * 86400));

            //$sql = 'select count(*) as num from '.C('DB_PREFIX').'orders_shop where status not in (0,1,10) and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1) and id in (select s_id from '.C('DB_PREFIX').'refund where DATE_FORMAT(atime,"%Y-%m-%d")="' . $tday .'" group by s_id)';
            $sql = 'select count(c.num) as num from (select count(*) as num from '.C('DB_PREFIX').'refund where DATE_FORMAT(atime,"%Y-%m-%d")="' . $tday .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id=1) group by s_id) c';
            $res = M()->query($sql);
            $result['self']['week_refund_num'][$tday] = $res[0]['num'];

            //$sql = 'select count(*) as num from '.C('DB_PREFIX').'orders_shop where status not in (0,1,10) and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1) and id in (select s_id from '.C('DB_PREFIX').'refund where DATE_FORMAT(atime,"%Y-%m-%d")="' . $tday .'" group by s_id)';
            $sql = 'select count(c.num) as num from (select count(*) as num from '.C('DB_PREFIX').'refund where DATE_FORMAT(atime,"%Y-%m-%d")="' . $tday .'" and shop_id in (select id from '.C('DB_PREFIX').'shop where type_id!=1) group by s_id) c';
            $res = M()->query($sql);
            $result['notself']['week_refund_num'][$tday] = $res[0]['num'];
        }

        //数据输出格式化，避免输出null
        foreach($result as $key => $val){
            foreach ($val as $vk => $v){
                foreach($v as $k => $vl) {
                    $result[$key][$vk][$k] = is_null($vl) ? 0 : $vl;
                    if($vk != 'week') $result['total'][$k] += $vl;
                }
            }
        }



        $this->apiReturn(1,['data' => $result]);
    }

    /**
     * 提供给Work APP 卖家未分账货款统计
     * 2016-11-24
     */
    public function wait_money(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=['erp_uid'];

        $this->_need_param();
        $this->_check_sign();

        $uid = M('user')->where(['erp_uid' => I('post.erp_uid')])->getField('id');
        if(!$uid) $this->apiReturn(0);

        $res = $this->seller_wait_money($uid);
        $this->apiReturn(1,['data' => ['money' => $res]]);
    }


}