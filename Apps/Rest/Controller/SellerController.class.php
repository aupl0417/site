<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家中心
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class SellerController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    
    /**
    * 数据统计
    */
    public function total(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $res=A('Total')->seller_ucenter($this->uid);

        $this->apiReturn(1,['data' => $res]);

    }

	
	
	
	public function supplier_total(){
		//频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$seller_id = C('cfg.supplier')['seller_id'];


		
		//当前商品数量
		$goods = M('goods')->cache(true,5)->field('status,is_best,count(id) as num')->where(['seller_id'=>$seller_id,'supplier_id'=>$this->uid])->group('status,is_best')->select();
		//dump($goods);
		foreach($goods as $v){
			$goods_count[$v['status']] += $v['num'];
			if($v['is_best'] == 1){
				$goods_count['best_num'] += $v['num'];
			}
		}
		$re['goods_count'] = $goods_count;

		//当前订单数
		$orders = M('orders_shop')->cache(true,5)->field('status,count(id) as num')->where(['seller_id'=>$seller_id,'supplier_id'=>$this->uid])->group('status')->where(['status'=>['in','1,2']])->select();
		foreach($orders as $v){
			$orders_count[$v['status']] += $v['num'];
		}
		
		$re['orders_count'] = $orders_count;
		
		
		//店铺数据计算
		$day=$day?$day:date('Y-m-d',time()-86400);
        //后续可记录到数据库中
        $res=M()->cache(true,86400)->query('select count(*) as num,sum(pay_price) as money,sum(goods_num) as goods_num from '.C('DB_PREFIX').'orders_shop where seller_id='.$seller_id.' and supplier_id = '.$this->uid.' and date_format(pay_time,"%Y-%m-%d")="'.$day.'"');
        //$result['sql'] = M()->getlastsql();
        $result['num']          =!is_null($res[0]['num'])?$res[0]['num']:0;   //订单数
        $result['money']        =!is_null($res[0]['money'])?$res[0]['money']:0;   //销售金额
        $result['money']        =number_format($result['money'],2);
        $result['goods_num']    =!is_null($res[0]['goods_num'])?$res[0]['goods_num']:0;   //商品数量 

        $result['buyer']        = M('orders_shop')->where(['seller_id' => $seller_id,'supplier_id'=>$this->uid,'_string' => 'date_format(pay_time,"%Y-%m-%d")="'.$day.'"'])->field('count(distinct(uid)) as num')->find();
        $result['buyer']        =$result['buyer']['num'];   //买家数量
        $result['price']        =number_format($result['money']/$result['buyer'],2);    //客单价
        //$result['refund']       =M('refund')->cache(true,86400)->where(['seller_id' => $uid,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$day.'"'])->count();
		
		$re['sale_total'] = $result;
		

        $this->apiReturn(1,['data' => $re]);
	}
}