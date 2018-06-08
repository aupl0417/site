<?php
/**
 * 发送消息
 * Create by liangfeng
 * 2017-05-15
 */
namespace Common\Behavior;

use Think\Behavior;
use Common\Notice\System;
use Think\Model\MongoModel as Mongo;
class InitMsgBehavior extends Behavior {
	private $user;
	private $subject;
	private $category_id;
	private $tpl_id;
	
	

    public function run(&$params) {
		
		switch($params['tpl_tag']){
			case 'orders_pay':case 'orders_confirm':
				$res =  $this->orders_send_seller_msg($params);
				break;
			case 'orders_deliver':
				$res =  $this->orders_send_buyer_msg($params);
				break;
			case 'refund_apply':case 'refund_cancel':case 'service_apply':case 'service_cancel':case 'refund_edit':
				$res =  $this->refund_send_seller_msg($params);
				break;
			case 'refund_agree':case 'refund_refuse':case 'service_agree':case 'service_refuse':
				$res =  $this->refund_send_buyer_msg($params);
				break;
			case 'register_success':case 'test':case 'shop_open_success':case 'shop_open_faile':case 'shop_close':case 'goods_down':
				$res =  $this->normol_msg($params);
				break;
			default:
				$res = ['code'=>0,'msg'=>'标记不存在'];
				break;
		}
		//writelog($params);
		//writelog($res);
		//dump($res);
		//$this->remark($params,$res);
	}
	
	/**
     * 订单-发送卖家
     * Create by liangfeng
     * 2017-05-17
     */
	private function orders_send_seller_msg($params){
		//读取模板分类id
		$res = $this->get_msg_tpl($params['tpl_tag']);
		
		if($res['code'] == 1){			
			//读取买家nick，卖家id
			$res = M('orders_shop')->field('nick,seller_id')->join('ylh_user ON ylh_orders_shop.uid = ylh_user.id')->where(['ylh_orders_shop.s_no'=>$params['s_no']])->find();
			$this->subject['buyer'] = $res['nick'];
			$this->subject['s_no'] = $params['s_no'];
			
			//读取接收用户的信息
			$res = $this->get_to_user($res['seller_id']);
			if($res['code'] == 1){
				$res = $this->send_msg($params['s_no']);
			}
		} 
		return $res;
	}
	/**
     * 订单-发送买家
     * Create by liangfeng
     * 2017-05-17
     */
	private function orders_send_buyer_msg($params){
		//读取模板分类id
		$res = $this->get_msg_tpl($params['tpl_tag']);
		
		if($res['code'] == 1){			
			//读取买家id
			$res = M('orders_shop')->field('shop_name,ylh_orders_shop.uid')->join('ylh_shop ON ylh_orders_shop.shop_id = ylh_shop.id')->where(['ylh_orders_shop.s_no'=>$params['s_no']])->find();
			$this->subject['shop_name'] = $res['shop_name'];
			$this->subject['s_no'] = $params['s_no'];
			
			//读取接收用户的信息
			$res = $this->get_to_user($res['uid']);
			if($res['code'] == 1){
				$res = $this->send_msg($params['s_no']);
			}
		} 
		return $res;
	}
	/**
     * 退款-发送卖家
     * Create by liangfeng
     * 2017-05-17
     */
	private function refund_send_seller_msg($params){
		//读取模板分类id
		$res = $this->get_msg_tpl($params['tpl_tag']);
		
		if($res['code'] == 1){			
			//读取买家id
			$res = M('refund')->field('nick,seller_id')->join('ylh_user ON ylh_refund.uid = ylh_user.id')->where(['ylh_refund.r_no'=>$params['r_no']])->find();
			$this->subject['buyer'] = $res['nick'];
			$this->subject['s_no'] = $params['s_no'];
			$this->subject['r_no'] = $params['r_no'];
			
			//读取接收用户的信息
			$res = $this->get_to_user($res['seller_id']);
			if($res['code'] == 1){
				$res = $this->send_msg($params['r_no']);
			}
		} 
		return $res;
	}
	/**
     * 退款-发送买家
     * Create by liangfeng
     * 2017-05-17
     */
	private function refund_send_buyer_msg($params){
		//读取模板分类id
		$res = $this->get_msg_tpl($params['tpl_tag']);
		
		if($res['code'] == 1){			
			//读取买家id
			$res = M('refund')->field('ylh_refund.uid,shop_name')->join('ylh_shop ON ylh_refund.shop_id = ylh_shop.id')->where(['ylh_refund.r_no'=>$params['r_no']])->find();
			$this->subject['shop_name'] = $res['shop_name'];
			$this->subject['r_no'] = $params['r_no'];
			
			//读取接收用户的信息
			$res = $this->get_to_user($res['uid']);
			if($res['code'] == 1){
				$res = $this->send_msg($params['r_no']);
			}
		} 
		return $res;
	}
	
	/**
     * 普通发送
     * Create by liangfeng
     * 2017-05-17
     */
	private function normol_msg($params){
		//读取模板分类id
		$res = $this->get_msg_tpl($params['tpl_tag']);
		
		if($res['code'] == 1){			
			//读取接收用户的信息
			$res = $this->get_to_user($params['uid']);
			if($res['code'] == 1){
				$res = $this->send_msg();
			}
		} 
		return $res;
	}
	
	

	
	
	
	
	
	/**
     * 调用消息类发送消息
     * Create by liangfeng
     * 2017-05-17
     */
	private function send_msg($r_id=0){
		//dump($this->subject);
		return (new System($this->user['id'], $this->tpl_id, $this->subject,$this->category_id,$r_id))->send();
	}
	
	
	/**
     * 获取模板id及分类id
     * Create by liangfeng
     * 2017-05-17
     */
	private function get_msg_tpl($tpl_tag){
		
		$res = M('msg_tpl')->field('id,category_id')->where(['tag'=>$tpl_tag])->find();
		if($res){
			$this->category_id = $res['category_id'];
			$this->tpl_id = $res['id'];
			return ['code'=>1];
		} return ['code'=>0,'msg'=>'没有找到此标记的模板'];
		
	}
	
	
	/**
     * 获取接收用户信息
     * Create by liangfeng
     * 2017-05-17
     */
	private function get_to_user($uid){
		$res = M('user')->field('id,nick,is_receive_msg')->where(['id'=>$uid])->find();
		if($res){
			$this->user['id'] = $res['id'];
			$this->user['nick'] = $res['nick'];
			$this->user['is_receive_msg'] = $res['is_receive_msg'];
			
			$this->subject['nick'] = $this->user['nick'];
			return $this->check_user_receive();
		} return ['code'=>0,'msg'=>'没有此用户'];
		
	}
	
	/**
     * 检查用户是否接收此类消息
     * Create by liangfeng
     * 2017-05-17
     */
	private function check_user_receive(){
		if($this->user['is_receive_msg'] == '1'){
			return ['code'=>1];
		}else if($this->user['is_receive_msg'] == '0'){
			return ['code'=>0,'msg'=>'用户不接收消息'];
		}else{
			$res = json_decode($this->user['is_receive_msg'],true);			
			if($res[$this->category_id] == 1){
				return ['code'=>1];
			}else{
				return ['code'=>0,'msg'=>'用户不接收此类消息'];
			}
		}
	}
	
	
	private function remark($params,$res){		
	
		$data['data'] = '';
		foreach($params as $k => $v){
			$data['data'] .= $k.'='.$v.' | ';
		}
		foreach($res as $k => $v){
			$data['data'] .= $k.'='.$v.' | ';
		}
		M('test_log')->add($data);
		
		
	
	}
	
}