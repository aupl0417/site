<?php
namespace Admin\Model;
use Think\Model;

class TotalsModel extends Model
{
	public $dateWhere;

	public function _initialize(){
		$t = time() - 24 * 3600;

		$now		= date('Y-m-d',$t);
		$last7day 	= date('Y-m-d',$t - 6 * 24 * 3600);
		$this->dateWhere = ['date'=>['between',[$last7day,$now]]];
	}


	/**
	 * 网站7天流量统计
	 */
	public function flow_7day(){
		$data = $this->where($this->dateWhere)->field('id,date,register,login,pv')->order('date asc')->select();
		
		$dayList 	= array();
		foreach ($data as $k => $v) {
			$dayList[] = $v['date'];

			$register[] = $v['register'];
			$login[] 	= $v['login'];
			$pv[] 		= $v['pv'];
		}

		$data = array();
		
		$legend 	= ['会员注册','登录数量','访问量'];
		$color 		= ['#c23531','#019440', '#6fa8dc'];

		$data['xAxis']  = json_encode($dayList);
		$data['title']	= json_encode("7天流量统计");
		$data['legend'] = json_encode($legend);
		$data['color'] 	= json_encode($color);
		$data['series'] = json_encode(array(
			array( 'name' => $legend[0], 'type' => 'bar', 'data' => $register ),
			array( 'name' => $legend[1], 'type' => 'bar', 'data' => $login ),
			array( 'name' => $legend[2], 'type' => 'bar', 'data' => $pv ),
		));
		
		return $data;
	}

	/**
	 * 网站近7天的交易
	 */
	public function jiaoyi_7day(){
		$data = $this->where($this->dateWhere)->field('register,login,pv',true)->order('date asc')->select();
		$dayList 	= array();
		foreach ($data as $k => $v) {
			$dayList[] = $v['date'];

			$buy_money[] 			= $v['buy_money'];
			$sale_money[] 			= $v['sale_money'];
			$refund_money[] 		= $v['refund_money'];
			$itemrefund_money[] 	= $v['itemrefund_money'];
			$support_money[] 		= $v['support_money'];
			$ad_money[] 			= $v['ad_money'];
			$xiaobao_join[] 		= $v['xiaobao_join'];
			$xiaobao_quit[] 		= $v['xiaobao_quit'];
		}

		$legend 	= ['新增付款','确认收货','订单退款','宝贝退款','售后退款','广告投放','加入消保','退出消保'];
		$color 		= ['#1bf602', '#c70933','#ffe599','#0000ff', '#b6d7a8','#660000','#747083', '#cdc8e3'];

		$data['xAxis']  = json_encode($dayList);
		$data['title']	= json_encode("7天交易统计");
		$data['legend'] = json_encode($legend);
		$data['color'] 	= json_encode($color);
		$data['series'] = json_encode(array(
			array( 'name' => $legend[0], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $buy_money ),
			array( 'name' => $legend[1], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $sale_money ),
			array( 'name' => $legend[2], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $refund_money ),
			array( 'name' => $legend[3], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $itemrefund_money ),
			array( 'name' => $legend[4], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $support_money ),
			array( 'name' => $legend[5], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $ad_money ),
			array( 'name' => $legend[6], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $xiaobao_join ),
			array( 'name' => $legend[7], 'type' => 'line', /* 'areaStyle'=>array('normal'=>array()), */ 'data' => $xiaobao_quit ),
		));
		return $data;
	}


	/**
     * 添加数据 从其他表里面统计出数据放到totals,一天一条数据 未完成
     * @param string $date yyyy-mm-dd 哪一天
     * @return boolean
     */
	public function add($date = '2016-06-03'){
		if(! strtotime($date)){
			return false;// 日期格式不对
		}

		$date   = date('Y-m-d H:i:s',strtotime($date));
		$d1 	= date('Y-m-d',strtotime($date));
		$date2 	= date('Y-m-d H:i:s',strtotime($date) + 24 * 3600 - 1 );
		$d2 	= date('Y-m-d',strtotime($date) + 24 * 3600 - 1 );
		// echo $date,'<hr/>',$date2,'<hr/>';exit;

		$bt = ['BETWEEN',[$date,$date2]];
		$register = M('user')->where(['atime'=>$bt])->field('id')->count();// 注册数量
		$pv = M('user')->where(['last_login_time'=>$bt])->count(); // 访问数量

		$orders = M('orders')->where(['atime'=>$bt])->field('id')->count();// 订单数量

		$buy_money = M('orders')->where(['pay_time'=>$bt,'is_pay'=>1])->sum('pay_price');// 新增付款
		$sale_money = M('orders_shop')->where(['receipt_time'=>$bt,'is_pay'=>1,'status'=>4])->sum('pay_price');// 确认收货
		$refund_money = M('orders_shop')->where(['receipt_time'=>$bt,'is_pay'=>1,'status'=>20])->sum('refund_price');// 订单退款

		$ad_money = M('ad')->where(['status'=>1,'sday'=>['EGT',$d1],'eday'=>['lt',$ELT]])->sum('money_pay');// 广告投放


		$data = array(
			'register' 	=> $register,
			'pv'		=> $pv,
			'orders'	=> $orders,
		);
		$tModel = M('totals');
		if($tModel->add($data)){
			return true;
		}else{
			return false;
		}
	}

}