<?php
namespace Rest2\Controller;

/**
 * 商家
 * @author Lzy 2017-03-17
 */

class SellerController extends ApiController

{

	
	/**
	 * subject: 订单各状态数量统计
	 * api: /Seller/orders_total
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function orders_total(){
		$this->check('openid',false);
		$res = $this->_orders_total($this->post);
		$this->apiReturn($res);		
	}
	public function _orders_total($param){
		$data['need_pay_count'] = 0;
		$data['need_delivery_count'] = 0;
		$data['need_receipt_count'] = 0;
		$data['need_comment_count'] = 0;
		
		$res = M('orders_shop')->field('status,count(id) as num')->where(['seller_id' => $this->user['id'],'status' => ['in','1,2,3,4']])->group('status')->select();
		
		foreach($res as $v){
			if($v['status'] == 1){
				$data['need_pay_count'] += $v['num'];
			}else if($v['status'] == 2){
				$data['need_delivery_count'] += $v['num'];
			}else if($v['status'] == 3){
				$data['need_receipt_count'] += $v['num'];
			}else if($v['status'] == 4){
				$data['need_comment_count'] += $v['num'];
			}
		}
		return ['code'=>1,'data'=>$data];
	}
	
	
	/**
	 * subject: 退款售后统计
	 * api: /Seller/refund_total
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function refund_total(){
		$this->check('openid',false);
		$res = $this->_refund_total($this->post);
		$this->apiReturn($res);
	}
	public function _refund_total($param){
		$data['new_refund_count'] = 0;
		$data['new_after_sale_count'] = 0;
		
		$res = M('refund')->field('orders_status,count(id) as num')->where(['seller_id' => $this->user['id'],'status'=>['in','1,2,3,4,5,6,10']])->group('orders_status')->select();
		
		foreach($res as $v){
			if($v['orders_status'] <= 3){
				$data['new_refund_count'] += $v['num'];
			}else if($v['orders_status'] > 3){
				$data['new_after_sale_count'] += $v['num'];
			}
		}
		return ['code'=>1,'data'=>$data];
	}
	/**
	 * subject: 评价统计
	 * api: /Seller/comment_total
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 * param: day,int,0,统计多少天前到现在默认3
	 */
	public function comment_total(){
		$this->check('openid',false);
		$res = $this->_comment_total($this->post);
		$this->apiReturn($res);
	}

	public function _comment_total($param){
		$day_num = $param['day']>0?$param['day']:3;
		$sday = date('Y-m-d',time()-86400*$day_num);
		$eday = date('Y-m-d',time());
		
		$data['comment_count'] = 0;
		$data['not_goods_count'] = 0;
		$data['goods_count'] = 0;
		
		$map['seller_id'] = $this->user['id'];
		$map['_string'] 	= 'date_format(atime,"%Y-%m-%d")>="'.$sday.'" and date_format(atime,"%Y-%m-%d")<"'.$eday.'"';
		
		$res = M('orders_goods_comment')->field('rate,count(id) as num')->where($map)->group('rate')->select();
		
		foreach($res as $v){
			if($v['rate'] == '1'){
				$data['goods_count'] += $v['num'];
			}else{
				$data['not_goods_count'] += $v['num'];
			}
			$data['comment_count'] += $v['num'];
		}		
		return ['code'=>1,'data'=>$data];
		
	}
	
	/**
	 * subject: 店铺金额统计
	 * api: /Seller/shop_month_price
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function shop_price_count(){
		$this->check('openid',false);
		$res = $this->_shop_price_count($this->post);
		$this->apiReturn($res);
	}
	public function _shop_price_count($param){
		$stime = date('Y-m-01',time());
		$etime = date('Y-m-d',time());
		$map['seller_id'] 	= $this->user['id'];
		$map['status'] 		= ['in','4,5'];
		$map['_string'] 	= 'date_format(receipt_time,"%Y-%m-%d")>="'.$stime.'" and date_format(receipt_time,"%Y-%m-%d")<"'.$etime.'"';
		
		$res['shop_month_price'] = M('orders_shop')->where($map)->sum('pay_price');
		$res['shop_month_price'] = $res['shop_month_price']>0?$res['shop_month_price']:0;
		
		$map2['seller_id'] 	= $this->user['id'];
		$map2['status'] 		= ['in','2,3'];		
		$res['shop_order_price'] = M('orders_shop')->where($map2)->sum('pay_price');
		$res['shop_order_price'] = $res['shop_order_price']>0?$res['shop_order_price']:0;
		
		
		$seller_wait_payment = A('Erp')->_get_seller_wait_payment(['userID'=>$this->user['erp_uid']]);
		$res['seller_wait_payment'] = $seller_wait_payment['data'] >0?$seller_wait_payment['data']:0;
		
		return ['code'=>1,'data'=>$res];
	}
	
	
	/**
	 * subject: 店铺详情保存
	 * api: /Seller/shop_info_save
	 * author: Lzy
	 * param: openid,string,1,商家openid
	 * param: province,int,1,省份id
	 * param: city,int,1,城市id
	 * param: district,int,1,区域id
	 * param: town,int,0,城镇id
	 * param: street,int,1,详细地址
	 * param: remark,int,1,公告
	 * param: remark_status,int,1,是否隐藏
	 */
	public function shop_info_save(){
		$this->check('openid,province,city,district,town,street,remark_status',false,'remark');
		$res = $this->_shop_info_save($this->post);
		$this->apiReturn($res);
	}

	public function _shop_info_save($param){
		$shop_address = array(
			'province' 	=> (int) $param['province'],
			'city' 		=> (int) $param['city'],
			'district' 	=> (int) $param['district'],
			'town' 		=> (int) $param['town'],
			'street'	=> $param['street'],
		);
		$res1 = M('shop')->where(['uid' => $this->user['id']])->data($shop_address)->save();
		if($res1===false) return ['code' => 0,'msg'=>'店铺地址修改失败'];
		
		$shop_news = array(
			'remark' 		=> $param['remark'],
			'status' => (int) $param['remark_status'],
		);
		
		$shop_news_id = M('shop_news')->where(['uid' => $this->user['id']])->getField('id');
		if($shop_news_id){
			$res2 = M('shop_news')->where(['uid' => $this->user['id']])->data($shop_news)->save();
		}else{
			$shop_news['uid'] = $this->user['id'];
			$shop_news['shop_id'] = $this->user['shop_id'];
			$res2 = M('shop_news')->data($shop_news)->add();
		}
		if($res2===false) return ['code' => 0,'msg'=>'店铺公告修改失败'];

		return ['code' => 1];
		
		/*
		$shopData = array(
			'about'		=> $param['about'],
			'province' 	=> (int) $param['province'],
			'city' 		=> (int) $param['city'],
			'district' 	=> (int) $param['district'],
			'town' 		=> (int) $param['town'],
			'street'	=> $param['street'],
		);
		if($param['qq']){
			if(checkform($param['qq'],'is_qq') == false) $this->apiReturn(['code' => 4,'msg' => 'qq格式错误！']);
			$shopData['qq'] = $param['qq'];
		}
		if($param['mobile']){
			if(checkform($param['mobile'],'is_mobile') == false) $this->apiReturn(['code' => 4,'msg' => '手机号码格式错误！']);
			$shopData['mobile'] = $param['mobile'];
		}
		if($param['tel']){
			if(checkform($param['tel'],'is_phone') == false) $this->apiReturn(['code' => 4,'msg' => '电话格式错误！']);
			$shopData['tel'] = $param['tel'];
		}
		if($param['email']){
			if(checkform($param['email'],'is_email') == false) $this->apiReturn(['code' => 4,'msg' => '邮箱格式错误！']);
			$shopData['email'] = $param['email'];
		}
		$shopSave = M('shop')->where(['uid' => $this->user['id']])->data($shopData)->save();
		return ['code' => (int) is_int($shopSave)];
		*/
	}
	
	
	/**
	 * subject: 读取店铺公告
	 * api: /Seller/shop_news
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function shop_news(){
		$this->check('openid',false);
		$res = $this->_shop_news($this->post);
		$this->apiReturn($res);
	}
	public function _shop_news($param){
		$res = M('shop_news')->field('remark,status')->where(['uid'=>$this->user['id']])->find();
		if($res){
			return ['code'=>1,'data'=>$res];
		}
		return ['code'=>3];
	}

	
	
	
	/**
	 * subject: 店铺本月营收 - 请使用 /Seller/shop_price_count 获取数据
	 * api: /Seller/shop_month_price
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function shop_month_price(){
		$this->check('openid',false);
		$res = $this->_shop_month_price($this->post);
		$this->apiReturn($res);
	}
	public function _shop_month_price($param){
		$stime = date('Y-m-1',time()-86400);
		$etime = date('Y-m-d',time());
		$map['seller_id'] 	= $this->user['id'];
		$map['status'] 		= ['in','2,3,4,5'];
		$map['_string'] 	= 'date_format(pay_time,"%Y-%m-%d")>="'.$stime.'" and date_format(pay_time,"%Y-%m-%d")<"'.$etime.'"';
		
		$res = M('orders_shop')->where($map)->sum('pay_price');
		
		if($res){
			return ['code'=>1,'data'=>$res];
		}
		return ['code'=>3,'data'=>0];
	}

	/**
	 * subject: 店铺订单中货款 - 请使用 /Seller/shop_price_count 获取数据
	 * api: /Seller/shop_order_price
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function shop_order_price(){
		$this->check('openid',false);
		$res = $this->_shop_order_price($this->post);
		$this->apiReturn($res);
	}
	public function _shop_order_price($param){
		$map['seller_id'] 	= $this->user['id'];
		$map['status'] 		= ['in','2,3'];		
		$res = M('orders_shop')->where($map)->sum('pay_price');
		if($res){
			return ['code'=>1,'data'=>$res];
		}
		return ['code'=>3,'data'=>0];
	}
	
	
	
	/**
	 * subject: 待付款订单 - 请使用 /Seller/orders_total 获取数据
	 * api: /Seller/new_refund
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function need_pay_count(){
		$this->check('openid',false);
		$count = M('orders_shop')->where(['seller_id' => $this->user['id'],'status' => 1])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}

	/**
	 * subject: 待发货订单 - 请使用 /Seller/orders_total 获取数据
	 * api: /Seller/send
	 * author: Lzy
	 * param: openid,string,1,商家openid
	 */
	public function need_delivery_count(){
		$this->check('openid',false);
		$count = M('orders_shop')->where(['seller_id' => $this->user['id'],'status' => 2])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}

	
	/**
	 * subject: 待收货订单 - 请使用 /Seller/orders_total 获取数据
	 * api: /Seller/new_refund
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function need_receipt_count(){
		$this->check('openid',false);
		$count = M('orders_shop')->where(['seller_id' => $this->user['id'],'status' => 3])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}
	
	/**
	 * subject: 待评价订单 - 请使用 /Seller/orders_total 获取数据
	 * api: /Seller/new_refund
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	public function need_comment_count(){
		$this->check('openid',false);
		$count = M('orders_shop')->where(['seller_id' => $this->user['id'],'status' => 4])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}
	
	/**
	 * subject: 新增退款数量 - 请使用 /Seller/refund_total 获取数据
	 * api: /Seller/new_refund
	 * author: Lzy
	 * param: openid,string,1,商家openid
	 */
	public function new_refund_count(){
		$this->check('openid',false);
		$count = M('refund')->where(['seller_id' => $this->user['id'],'status' => 1, 'type' => ['in',[1,2,3]]])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}


	/**
	 * subject: 新增售后数量 - 请使用 /Seller/refund_total 获取数据
	 * api: /Seller/after_sale_count
	 * author: Lzy
	 * param: openid,string,1,商家openid
	 */
	public function new_after_sale_count(){
		$this->check('openid',false);
		$count = M('refund')->where(['seller_id' => $this->user['id'],'status' => 1, 'type' => ['in',[4,5]]])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}
	
	/**
	 * subject: 3天内新增评价 - 请使用 /Seller/comment_total 获取数据
	 * api: /Seller/new_refund
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	 
	public function new_comment_count(){
		$this->check('openid',false);
		$day3 = date('Y-m-d',time()-86400*3);
		$count = M('orders_goods_comment')->where(['seller_id' => $this->user['id'],'atime' => ['gt',$day3]])->count();
		$this->apiReturn(['code' => 1,'data' => ['count' => $count]]);
	}
	/**
	 * subject: 3天内新增中、差评价 - 请使用 /Seller/comment_total 获取数据
	 * api: /Seller/new_refund
	 * author: liangfeng
	 * param: openid,string,1,商家openid
	 */
	 
	public function new_comment_bad_count(){
		$this->check('openid',false);
		$day3 = date('Y-m-d',time()-86400*3);
		$data['count'] = M('orders_goods_comment')->where(['seller_id' => $this->user['id'],'atime' => ['gt',$day3],'rate'=>['in','0,-1']])->count();
		$this->apiReturn(['code' => 1,'data' => $data]);
	}

}