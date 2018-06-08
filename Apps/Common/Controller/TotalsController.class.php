<?php
/**
 * -------------------------------------------
 * 每天数据统计
 * -------------------------------------------
 * Author:李祖衡
 * -------------------------------------------
 */
namespace Common\Controller;
use Think\Controller;
class TotalsController extends Controller {
    private $day;    //要统计的时间
    private $day2;    
    private $insert;
	private $ip;


    public function _initialize() {
		set_time_limit(0);
        $this->day  =   date('Y-m-d',time()-86400); //默认为统计前一天
        $this->day2  =   date('Y-m-d',time()-86400*2); //默认为统计前两天
        $this->day7  =   date('Y-m-d',time()-86400*7); //默认为统计前七天	
		$this->ip	=	get_client_ip();
        $this->insert = M("totals");
    }

    /**
    * 设置属性
    */
    public function __set($name,$v){
        return $this->$name=$v;
    }

    /**
    * 获取属性
    */
    public function __get($name){
        return isset($this->$name)?$this->$name:null;
    }
    
    /**
    * 销毁属性
    */
    public function __unset($name) {
        unset($this->$name);
    }  
	
    /**
     * 更新统计并且返回更新的数据
     */
    public function total_date(){
		//dump($this->day);
		$public_data['ip'] = $this->ip;
		$public_data['day'] = $this->day;		
		
		//统计基本数据
		$totals_basic = $this->totals_basic();
		$result_basic         = array_merge_recursive($public_data,$totals_basic);
		//判断是否已经产生过记录
		
		$id = M('totals_basic')->where(['day' => $this->day])->getField("id");
		if($id){
            $res = M('totals_basic')->where(['id' => $id])->save($result_basic);
        }else{
            $res = M('totals_basic')->add($result_basic);
        }
		
		
		
		//统计交易数据
		$totals_trans       = $this->totals_trans();
		$result_trans         = array_merge_recursive($public_data,$totals_trans);
		//判断是否已经产生过记录
		
		$id = M('totals_trans')->where(['day' => $this->day])->getField("id");
		if($id){
            $res = M('totals_trans')->where(['id' => $id])->save($result_trans);
        }else{
            $res = M('totals_trans')->add($result_trans);
        }
		
		
		//统计促销数据
		$totals_promotion     = $this->totals_promotion();
		$result_promotion   = array_merge_recursive($public_data,$totals_promotion);
		//判断是否已经产生过记录
		
		$id = M('totals_promotion')->where(['day' => $this->day])->getField("id");
		if($id){
            $res = M('totals_promotion')->where(['id' => $id])->save($result_promotion);
        }else{
            $res = M('totals_promotion')->add($result_promotion);
        }
		
		
		$result_hot = $this->totals_hot();
		//dump($result_hot);
		
		for($i=0;$i<=9;$i++){
			$hot_data = array_merge_recursive($public_data,$result_hot[$i]);
			$hot_data['no'] = $i;
			$hot_data['ip'] = $public_data['ip'];
			$hot_data['day'] = $public_data['day'];
			
			$id = M('totals_hot')->where(['day' => $this->day,'no'=>$i])->getField("id");
			if($id){
				$res = M('totals_hot')->where(['id' => $id])->save($hot_data);
			}else{
				$res = M('totals_hot')->add($hot_data);
			}
		}
		
		//统计话费、流量充值数据
		$totals_recharge = $this->totals_recharge();
		$totals_recharge = array_merge_recursive($public_data,$totals_recharge);	
		//判断是否已经产生过记录
		$id = M('mobile_orders_totals')->where(['day' => $this->day])->getField("id");
		if($id){
            $res = M('mobile_orders_totals')->where(['id' => $id])->save($totals_recharge);
        }else{
            $res = M('mobile_orders_totals')->add($totals_recharge);
        }
		
        return $result;
    }
	
	/**
    * 基础统计 
    */
    public function totals_basic(){
        //会员
        $result['member']=M('user')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
		
		//店铺
        $result['open_store_user'] = M('shop')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        $result['open_store_success'] = M('zhaoshang_join')->where(['status'=>1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        $result['normal_store']  = M('shop')->where(['status'=>1])->count();
        $result['close_store'] = M('shop')->where(['status'=>2,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
		
        //商品
        $result['goods_num']      = M('goods')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        $result['illegal_goods_num']   = M('goods_illegl')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        $result['online_goods_num']   = M('totals_shop')->where(['day' => $this->day])->sum('online_goods_num');
		
        //评价
        $result['comment_num']  = M('orders_goods_comment')->where(['status' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
       
		//总额
		$last_data = M('totals_basic')->where(['day' => $this->day2])->find();
		//如果存在上一天数据，进行累加
		if($last_data){
			$result['total_member'] = $last_data['total_member'] + $result['member'];   
			
			$result['comment_total_num'] = $last_data['comment_total_num'] + $result['comment_num'];   
		}else{
			$result['total_member'] = M('user')->count();
			
			$result['comment_total_num']  = M('orders_goods_comment')->where(['status' => 1])->count();
		}
		return $result;
	}
	
	
	/**
    * 交易统计 
    */
    public function totals_trans(){
		//获取所有店铺的数据进行合计
        $res = M('totals_shop')->field('sum(alipay_num) as alipay_num,sum(day_alipay_total) as day_alipay_total,sum(money_num) as money_num,sum(day_money_total) as day_money_total,sum(tangbao_num) as tangbao_num,sum(day_tangbao_total) as day_tangbao_total,sum(order_num) as order_num,sum(pc_order_num) as pc_order_num,sum(day_order_total) as day_order_total,sum(order_success) as order_success,sum(pc_order_success) as pc_order_success,sum(day_order_success_total) as day_order_success_total,sum(accept_num) as  accept_num,sum(day_accept_total) as day_accept_total,sum(refund_num) as refund_num,sum(day_refund_money) as day_refund_money,sum(refund_success_num) as refund_success_num,sum(refund_success_money) as refund_success_money')->where(['day' => $this->day])->find();
		
		
		//支付宝
		$result['alipay_num'] = $res['alipay_num'];
		$result['day_alipay_total'] = $res['day_alipay_total'];
		//余额
		$result['money_num'] = $res['money_num'];
		$result['day_money_total'] = $res['day_money_total'];
		//唐宝
		$result['tangbao_num'] = $res['tangbao_num'];
		$result['day_tangbao_total'] = $res['day_tangbao_total'];
		//下单
		$result['order_num'] = $res['order_num'];
		$result['pc_order_num'] = $res['pc_order_num'];
		$result['wap_order_num'] = $res['order_num'] - $res['pc_order_num'];
		$result['day_order_total'] = $res['day_order_total'];
		$result['day_average_order'] = round($res['day_order_total']/$res['order_num'],2);
		//成交
		$result['order_success'] = $res['order_success'];
		$result['pc_order_success'] = $res['pc_order_success'];
		$result['wap_order_success'] = $res['order_success'] - $res['pc_order_success'];
		$result['day_order_success_total'] = $res['day_order_success_total'];
		$result['day_average_success_order'] = round($res['day_order_success_total']/$res['order_success'],2);
		//付款率
		$result['pc_pay_rate'] = round($res['pc_order_success']/$res['pc_order_num'],2);
		$result['wap_pay_rate'] = round($res['wap_order_success']/$res['wap_order_num'],2);
		$result['total_pay_rate'] = round($res['order_success']/$res['order_num'],2);
		
		//确认收货
		$result['accept_num'] = $res['accept_num'];
		$result['day_accept_total'] = $res['day_accept_total'];
		$result['day_average_accept'] = round($res['day_accept_total']/$res['accept_num'],2);
		//申请退货
		$result['refund_num'] = $res['refund_num'];
		$result['day_refund_money'] = $res['day_refund_money'];
		$result['day_average_refund'] = round($res['day_refund_money']/$res['refund_num'],2);
		//退货成功
		$result['refund_success_num'] = $res['refund_success_num'];
		$result['refund_success_money'] = $res['refund_success_money'];
		$result['refund_success_average'] = round($res['refund_success_money']/$res['refund_success_num'],2);
		
		
		
		
		//总额
		$last_data = M('totals_trans')->where(['day' => $this->day2])->find();
		//如果存在上一天数据，进行累加
		if($last_data){
			$result['order_num_total'] = $last_data['order_num_total'] + $res['order_num'];
		
			$result['money_total'] = $last_data['money_total'] + $res['day_money_total'];   
			$result['total_alipay'] = $last_data['total_alipay'] + $res['day_alipay_total'];
			$result['total_tangbao'] = $last_data['total_tangbao'] + $res['day_tangbao_total']; 
			$result['success_order_num_total'] = $last_data['success_order_num_total'] + $res['order_success'];
			$result['success_order_total'] = $last_data['success_order_total'] + $res['day_order_success_total'];
			
			$result['accept_total'] = $last_data['accept_total'] + $res['day_accept_total'];
			
			$result['refund_total'] = $last_data['refund_total'] + $res['day_refund_money'];
			$result['refund_success_total'] = $last_data['refund_success_total'] + $res['refund_success_money'];
		}else{
			$result['order_num_total'] = M('orders_shop')->count();
			
			$result['money_total'] = M('orders_shop')->where(['pay_type' => 1,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum("pay_price");
			$result['total_alipay'] = M('orders_shop')->where(['pay_type' => 3,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum("pay_price");
			$result['total_tangbao'] = M('orders_shop')->where(['pay_type' => 2,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum("pay_price");
			$result['success_order_num_total'] = M('orders_shop')->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->count();
			$result['success_order_total'] = M('orders_shop')->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum('pay_price');
			
			$result['accept_total'] = M('orders_shop')->where(['_string' => 'date_format(receipt_time,"%Y-%m-%d")!="0000-00-00"'])->sum('pay_price');
			
			$result['refund_total'] = M('refund')->sum("money");
			$result['refund_success_total'] = M('refund')->where(['_string' => 'date_format(accept_time,"%Y-%m-%d")!="0000-00-00"'])->sum("money");
		}
		return $result;
    }
	
	 /**
    * 促销统计 
    */
    public function totals_promotion(){

        //品牌
        $result['brand_num']  = M('brand_ext')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
		//每天新增品牌推广总金额
		//$result['day_brand_money']  = M('brand_ext')->where(['pay_type' => 3,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->sum("pay_price");
		//累计品牌推广总金额
		//$result['brand_total_money']  = M('brand_ext')->where(['pay_type' => 3])->sum("pay_price");
		
		$res = M('totals_shop')->field('sum(activity_num) as activity_num,sum(activity_pay_num) as activity_pay_num,sum(day_activity_money) as day_activity_money,sum(coupon_num) as coupon_num,sum(day_coupon_total) as day_coupon_total,sum(use_coupon_num) as use_coupon_num,sum(use_coupon_money) as use_coupon_money')->where(['day' => $this->day])->find();
		
		//活动
		$result['activity_num'] = $res['activity_num'];
		$result['activity_pay_num'] = $res['activity_pay_num'];
		$result['day_activity_money'] = $res['day_activity_money'];
		$result['day_average_activity'] = round($res['day_activity_money']/$res['activity_pay_num'],2);
		//优惠券
		$result['coupon_num'] = $res['coupon_num'];
		$result['day_coupon_total'] = $res['day_coupon_total'];
		$result['use_coupon_num'] = $res['use_coupon_num'];
		$result['use_coupon_money'] = $res['use_coupon_money'];
		
		//广告
		$ad = M('ad')->field('count(*) as ad_num,sum(money) as ad_money,avg(money) as ad_price_unit')->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->find();
		$result['ad_num'] = $ad['ad_num'];
		$result['ad_money'] = $ad['ad_money']>0?$ad['ad_money']:0;
		$result['ad_price_unit'] = $ad['ad_price_unit']>0?$ad['ad_price_unit']:0;
		
		//素材
        $result['sucai_num']  = M('ad_sucai')->where(['status' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
       
		
		//总额
		$last_data = M('totals_promotion')->where(['day' => $this->day2])->find();
		//如果存在上一天数据，进行累加
		if($last_data){
			$result['activity_total_num'] = $last_data['activity_total_num'] + $res['activity_pay_num'];
			$result['activity_total_money'] = $last_data['activity_total_money'] + $res['day_activity_money'];
			$result['coupon_total_num'] = $last_data['coupon_total_num'] + $res['coupon_num'];
			$result['coupon_total_money'] = $last_data['coupon_total_money'] + $res['day_coupon_total'];
			$result['use_coupon_total_num'] = $last_data['use_coupon_total_num'] + $res['use_coupon_num'];
			$result['use_coupon_total_money'] = $last_data['use_coupon_total_money'] + $res['use_coupon_money'];
			$result['ad_total_num'] = $last_data['ad_total_num'] + $result['ad_num'];
			$result['ad_total_money'] = $last_data['ad_total_money'] + $result['ad_money'];
			$result['sucai_total_num'] = $last_data['sucai_total_num'] + $result['sucai_num'];
			
		}else{
			$result['activity_total_num'] = M('activity_participate')->where(['status' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")!="0000-00-00"'])->count();
			$result['activity_total_money'] = M('activity_participate')->where(['status' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")!="0000-00-00"'])->sum('calc_before_money-calc_after_money');
			$result['activity_total_money'] = $result['activity_total_money']>0 ? $result['activity_total_money'] : 0;
			
			$result['coupon_total_num'] = M('coupon')->where(['_string' => 'date_format(get_time,"%Y-%m-%d")!="0000-00-00"'])->count();
			$result['coupon_total_money'] = M('coupon')->where(['_string' => 'date_format(get_time,"%Y-%m-%d")!="0000-00-00"'])->sum('price');
			$result['coupon_total_money'] = $result['coupon_total_money']>0 ? $result['coupon_total_money'] : 0;
			
			$result['use_coupon_total_num'] = M('coupon')->where(['_string' => 'date_format(use_time,"%Y-%m-%d")!="0000-00-00"'])->count();
			$result['use_coupon_total_money'] = M('coupon')->where(['_string' => 'date_format(use_time,"%Y-%m-%d")!="0000-00-00"'])->sum('price');
			$result['use_coupon_total_money'] = $result['use_coupon_total_money']>0 ? $result['use_coupon_total_money'] : 0;
			
			$result['ad_total_num'] = M('ad')->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->count();
			$result['ad_total_money'] = M('ad')->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum('money');
			$result['ad_total_money'] = $result['ad_total_money']>0 ? $result['ad_total_money'] : 0;
			
			$result['sucai_total_num']  = M('ad_sucai')->where(['status' => 1])->count();
		}
		
		
		//dump($result);
		
        return $result;
    }
   
	/**
     * 热销统计
     */
	public function totals_hot(){
		//热销商品
		$list = M('orders_goods')->field('goods_id,goods_name,count(*) as success_order_num,price as goods_price,sum(num) as buy_num,sum(total_price) as buy_money')->where(['goods_name'=>['notlike','%运费%'],'_string' => 'date_format(atime,"%Y-%m-%d")>"'.$this->day7.'" and s_id in(select id from ylh_orders_shop where date_format(pay_time,"%Y-%m-%d")>"'.$this->day7.'")'])->group('goods_id')->order('success_order_num desc,buy_num desc, buy_money desc')->limit(10)->select();
		
		if(!empty($list)){
			foreach($list as $v){
				$goods_ids .= $v['goods_id'].',';
			}
			$goods_ids = substr($goods_ids,0,-1);
			$list2 = M('orders_goods')->field('goods_id,sum(num) as order_num')->where(['_string' => 'date_format(atime,"%Y-%m-%d")>"'.$this->day7.'"','goods_id'=>['in',$goods_ids]])->group('goods_id')->select();
			foreach($list as $k => $v){
				foreach($list2 as $va){
					if($v['goods_id'] == $va['goods_id']){
						$list[$k]['order_num'] = $va['order_num'];
						$list[$k]['order_buy_percen'] = round($v['buy_num']/$va['order_num'],2);
					}
				}
			}
		}
		
		return $list;
	}
	
	/**
     * 代购数据统计
     */
	public function daigou(){
		$do=M('orders_shop');
        //pc代购下单数量
		$res['pc_daigou_order'] = $do->where('terminal="0" and daigou_cost>0 and date_format(atime,"%Y-%m-%d")="'.$this->day.'"')->count();
		//wap代购下单数量
		$res['wap_daigou_order'] = $do->where('terminal="1" and daigou_cost>0 and date_format(atime,"%Y-%m-%d")="'.$this->day.'"')->count();
		//代购下单总数量
		$res['total_daigou_order'] = $do->where('daigou_cost>0 and date_format(atime,"%Y-%m-%d")="'.$this->day.'"')->count();
		
		//pc代购支付总数量
		$res['pc_pay_daigou'] = $do->where('terminal="0" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		//wap代购支付总数量
		$res['wap_pay_daigou'] = $do->where('terminal="1" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		//代购支付总数量
		$res['total_pay_daigou'] = $do->where('daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		
		//pc代购付款率
		$res['pc_pay_rate'] = $res['pc_pay_daigou']/$res['pc_daigou_order']*100;
		//wap代购付款率
		$res['wap_pay_rate'] = $res['wap_pay_daigou']/$res['wap_daigou_order']*100;
		//代购付款率
		$res['total_pay_rate'] = $res['total_pay_daigou']/$res['total_daigou_order']*100;
		
		//pc代购支付总额
		$res['pc_daigou_total'] = $do->where('terminal="0" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);
		$res['pc_daigou_total'] = $res['pc_daigou_total']==null?0:$res['pc_daigou_total'];
		//wap代购支付总额
		$res['wap_daigou_total'] = $do->where('terminal="1" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);
		$res['wap_daigou_total'] = $res['wap_daigou_total']==null?0:$res['wap_daigou_total'];
		//代购支付总额
		$res['daigou_total_money'] = $do->where('daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);	
		$res['daigou_total_money'] = $res['daigou_total_money']==null?0:$res['daigou_total_money'];
		
		//pc代购支付平均价
		$res['pc_daigou_avg_price'] = $do->where('terminal="0" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);	
		$res['pc_daigou_avg_price'] = $res['pc_daigou_avg_price']==null?0:$res['pc_daigou_avg_price'];
		//wap代购支付平均价
		$res['wap_daigou_avg_price'] = $do->where('terminal="1" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);	
		$res['wap_daigou_avg_price'] = $res['wap_daigou_avg_price']==null?0:$res['wap_daigou_avg_price'];
		//代购支付平均价
		$res['total_daigou_avg_price'] = $do->where('daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->avg(goods_price_edit);	
		$res['total_daigou_avg_price'] = $res['total_daigou_avg_price']==null?0:$res['total_daigou_avg_price'];
        
		//支付宝支付数量
		$res['daigou_alipay_pay'] = $do->where('pay_type="3" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		//糖宝支付数量
		$res['daigou_tangbao_pay'] = $do->where('pay_type="2" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		//现金支付数量
		$res['daigou_money_pay'] = $do->where('pay_type="1" and daigou_cost>0 and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->count();
		
		//wap代购总额
		$res['wap_total_moeny'] = $do->where('terminal="1" and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);
		$res['wap_total_moeny'] = $res['wap_total_moeny']==null?0:$res['wap_total_moeny'];
		//pc代购总额
		$res['pc_total_moeny'] = $do->where('terminal="0" and date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"')->sum(goods_price_edit);
		$res['pc_total_moeny'] = $res['pc_total_moeny']==null?0:$res['pc_total_moeny'];
		
		return $res;
	}



	/**
     * 统计店铺某一天销售额（销售额、平均单价、售出商品数量、退款金额）
     * Author：Lazycat
     * 2016-12-01
     * @param int $shop_id 店铺ID
     */

	public function shop_totals($shop_id){
	    $shop = M('shop')->field('id,uid')->where(['id' => $shop_id])->field('uid')->find();
        if(empty($shop)) return;
		
		
		$data2['ip'] = $this->ip;
		$data2['day'] = $this->day;
		$data2['shop_id'] = $shop_id;
		$data2['uid'] = $shop['uid'];
		
		//下单的笔数和金额(按付款设备统计)
		$trans_terminal = M('orders_shop')->field('terminal,count(*) as num,sum(pay_price) as money')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->group('terminal')->select();
		foreach($trans_terminal as $v){
			$data2['order_num'] += $v['num'];
			$data2['day_order_total'] += $v['money'];
			if($v['terminal'] == 0){
				$data2['pc_order_num'] += $v['num'];
			}
		}
		
		//付款的笔数和金额(按付款类型统计)
		$trans_pay_type = M('orders_shop')->field('pay_type,count(*) as num,sum(pay_price) as money')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'" AND date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->group('pay_type')->select();
		foreach($trans_pay_type as $v){
			//余额
			if($v['pay_type'] == 1){
				$data2['money_num'] += $v['num'];
				$data2['day_money_total'] += $v['money'];
			//唐宝
			}else if($v['pay_type'] == 2){
				$data2['tangbao_num'] += $v['num'];
				$data2['day_tangbao_total'] += $v['money'];
			//支付宝
			}else if($v['pay_type'] == 3){
				$data2['alipay_num'] += $v['num'];
				$data2['day_alipay_total'] += $v['money'];
			}else if($v['pay_type'] == 4){
				
			}
		}
		
		
		//付款的笔数和金额(按设备类型统计)
		$data2['order_success'] = 0;
		$data2['day_order_success_total'] = 0;
		$data2['pc_order_success'] = 0;
		$trans_success_terminal = M('orders_shop')->field('terminal,count(*) as num,sum(pay_price) as money')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'" AND date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->group('terminal')->select();
		foreach($trans_success_terminal as $v){
			$data2['order_success'] += $v['num'];
			$data2['day_order_success_total'] += $v['money'];
			if($v['terminal'] == 0){
				$data2['pc_order_success'] += $v['num'];
			}
		}
		
		
		//确认收货的笔数和金额(按设备类型统计)
		$trans_accept = M('orders_shop')->field('count(*) as num,sum(pay_price) as money')->where(['_string' => 'date_format(receipt_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id,'status'=>['gt','3']])->find();
		$data2['accept_num'] = $trans_accept['num']>0 ? $trans_accept['num'] : 0;
		$data2['day_accept_total'] = $trans_accept['money']>0 ? $trans_accept['money'] : 0;
		

		//申请退款的笔数和金额
		$trans_refund = M('refund')->field('count(*) as num,sum(money) as money')->where(['shop_id' => $shop_id,'_string' => 'DATE_FORMAT(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		$data2['refund_num'] = $trans_refund['num']>0 ? $trans_refund['num'] : 0;
		$data2['day_refund_money'] = $trans_refund['money']>0 ? $trans_refund['money'] : 0;
		
		//确认退款的笔数和金额
		$trans_success_refund = M('refund')->field('count(*) as num,sum(money) as money')->where(['shop_id' => $shop_id,'_string' => 'DATE_FORMAT(accept_time,"%Y-%m-%d")="'.$this->day.'"'])->find();
		$data2['refund_success_num'] = $trans_success_refund['num']>0 ? $trans_success_refund['num'] : 0;
		$data2['refund_success_money'] = $trans_success_refund['money']>0 ? $trans_success_refund['money'] : 0;
		
		//在线出售中的商品数量
        $data2['online_goods_num']   = M('goods')->where(['shop_id'=>$shop_id,"_string" => "status=1"])->count();
		
		//活动
		$promotion_activity = M('activity_participate')->field('status,count(*) as num,sum(calc_before_money-calc_after_money) as money')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->group('status')->select();
		foreach($promotion_activity as $v){
			$data2['activity_num'] += $v['num'];
			if($v['status']==1){
				$data2['activity_pay_num'] += $v['num'];
				$data2['day_activity_money'] += $v['money'];
			}
			
		}
		
		$coupon_get = M('coupon')->field('count(*) as num,sum(price) as money')->where(['_string' => 'date_format(get_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->find();
		$data2['coupon_num'] = $coupon_get['num']>0 ? $coupon_get['num'] : 0;
		$data2['day_coupon_total'] = $coupon_get['money']>0 ? $coupon_get['money'] : 0;
		$coupon_use = M('coupon')->field('count(*) as num,sum(price) as money')->where(['_string' => 'date_format(use_time,"%Y-%m-%d")="'.$this->day.'"','shop_id'=>$shop_id])->find();
		$data2['use_coupon_num'] = $coupon_use['num']>0 ? $coupon_use['num'] : 0;
		$data2['use_coupon_money'] = $coupon_use['money']>0 ? $coupon_use['money'] : 0;
		
//dump($data2);
		
		//原有数据
		$data2['money_pay'] = $data2['day_order_success_total'];
		$data2['money_refund'] = $data2['refund_success_money'];
		$data2['orders_pay_num'] = $data2['order_success'];
		$data2['goods_sale_num'] = M('orders_goods')->where(['shop_id' => $shop_id,'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where DATE_FORMAT(pay_time,"%Y-%m-%d")="'.$this->day.'")'])->sum('num');
		$data2['goods_sale_num'] = $data2['goods_sale_num'] > 0 ? $data2['goods_sale_num'] : 0;
		$data2['avg_orders_price'] = round($data2['day_order_success_total']/$data2['order_success'],2);
	
		
		
		$id = M('totals_shop')->where(['shop_id' => $shop_id,'day' => $this->day])->getField('id');
		if($id){
            $data2['etime']  = date('Y-m-d H:i:s');
            M('totals_shop')->where(['id' => $id])->save($data2);
        }else{
            $res = M('totals_shop')->add($data2);
        }

        //统计店铺营业额
        $total_money_pay = M('totals_shop')->where(['shop_id' => $shop_id])->sum('money_pay');
        M('shop')->where(['id' => $shop_id])->save(['total_money_pay' => $total_money_pay]);

        return $result;
    }

    //批量统计店铺销售额，适合店铺用户数量不是很多的情况下，店铺多的话请用队列处理
    public function shop_totals_bat(){
        $list = M('shop')->where(['status' => 1])->getField('id',true);
        foreach ($list as $val) {
            $this->shop_totals($val);
			usleep(10000);
        }
		$this->total_date();
    }
	
	/**
    * 话费流量充值统计 
    */
    public function totals_recharge(){
		$do = M("mobile_orders");

		//话费充值
		$res['fare']  = $do->field('count(id) as fare_totals_num,sum(score) as fare_totals_score,sum(pay_price) as fare_totals_money')->where(['recharge_type' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//流量充值
		$res['flow']  = $do->field('count(id) as flow_totals_num,sum(score) as flow_totals_score,sum(pay_price) as flow_totals_money')->where(['recharge_type' => 2,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//话费充值成功
        $res['fare_success']  = $do->field('count(id) as fare_success_num,sum(score) fare_success_score,sum(pay_price) as fare_success_money,avg(pay_price) as fare_success_avg')->where(['recharge_type' => 1,'_string' => '(status IN ("3","4","5")) or (status=2 and return_status IN ("1","29")) and date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();

		//流量充值成功
        $res['flow_success']  = $do->field('count(id) as flow_success_num,sum(score) flow_success_score,sum(pay_price) as flow_success_money,avg(pay_price) as flow_success_avg')->where(['recharge_type' => 2,'_string' => '(status IN ("3","4","5")) or (status=2 and return_status IN ("1","29")) and date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//充值成功
        $res['success']  = $do->field('count(id) as recharge_success_num,sum(score) recharge_success_score,sum(pay_price) as recharge_success_money,avg(pay_price) as recharge_success_avg')->where(['_string' => '((status in ("3,4,5")) or (status=2 and return_status in ("1,29"))) and date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//充值送积分方式充值
        $res['type']  = $do->field('count(id) as recharge_type_num,sum(score) recharge_type_score,sum(pay_price) as recharge_type_money')->where(['type' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//余额充值
        $res['balance']  = $do->field('count(id) as balance_pay_num,sum(score) balance_pay_score,sum(pay_price) as balance_pay_money')->where(['pay_type' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//微信充值
        $res['weixin']  = $do->field('count(id) as weixin_pay_num,sum(score) weixin_pay_score,sum(pay_price) as weixin_pay_money')->where(['type' => 3,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//支付宝充值
        $res['alipay']  = $do->field('count(id) as alipay_pay_num,sum(score) alipay_pay_score,sum(pay_price) as alipay_pay_money')->where(['type' => 5,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//银联充值
        $res['bank']  = $do->field('count(id) as bank_pay_num,sum(score) bank_pay_score,sum(pay_price) as bank_pay_money')->where(['type' => array("in","7,8"),'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//充值总数，总额，总积分
        $res['totals']  = $do->field('count(id) as num_totals,sum(score) score_totals,sum(pay_price) as money_totals')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//pc充值
        $res['pc']  = $do->field('count(id) as pc_recharge_num,sum(score) pc_recharge_score,sum(pay_price) as pc_recharge_money')->where(['terminal' => 0,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//wap充值
        $res['wap']  = $do->field('count(id) as wap_recharge_num,sum(score) wap_recharge_score,sum(pay_price) as wap_recharge_money')->where(['terminal' => 1,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//ios充值
        $res['ios']  = $do->field('count(id) as ios_recharge_num,sum(score) ios_recharge_score,sum(pay_price) as ios_recharge_money')->where(['terminal' => 2,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		//android充值
        $res['android']  = $do->field('count(id) as android_recharge_num,sum(score) android_recharge_score,sum(pay_price) as android_recharge_money')->where(['terminal' => 3,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->find();
		
		$result = array_merge_recursive($res['fare'],$res['flow'],$res['fare_success'],$res['flow_success'],$res['success'],$res['type'],$res['balance'],$res['weixin'],$res['alipay'],$res['bank'],$res['totals'],$res['pc'],$res['wap'],$res['ios'],$res['android']);
		foreach($result as $key=>$val){
			if(!$val){
				$result[$key] = 0;
			}
		}
		// dump($result);		
        return $result;
    }
}