<?php
namespace Seller\Controller;
use Think\Model\MongoModel as Mongo;
class StatisticController extends AuthController {
	
	static protected $dbConfig;
	static protected $dbTable;
	static protected $model;
	public $shop_id;
	public $nowDay;
	
	static protected function dbConfig(){
		self::$dbConfig or self::$dbConfig = C('DB_MONGO_CONFIG');
		return self::$dbConfig;
	}
	static protected function dbTable(){
		self::$dbTable or self::$dbTable = C('DB_MONGO_CONFIG.DB_PREFIX') . 'tongji_shop';
		return self::$dbTable;
	}
    public function _initialize() {
		parent::_initialize();
		//mongo
		$this->model = new Mongo(self::dbTable(), null, self::dbConfig());
		//sql
		$this->sql = M('')->db(1,"mysqli://root:123456@192.168.3.203:3306/dtmall_shop_analysis");
		//当前日期
		$this->nowDay = date('Y-m-d',time());
		//$this->nowDay = "2016-09-29";
		//当前用户的店铺id
		$this->shop_id = $this->shop_info['id'];
    }
	
    //实时统计
    public function index() {
        //查询的日期
		$selectDay = isset($_GET['selectDay']) ? I('get.selectDay') : $this->nowDay ;
		//后一天
		$nextDay = date('Y-m-d',(strtotime($selectDay) + 86400));
		//前一天
		$prevDay = date('Y-m-d',(strtotime($selectDay) - 86400));
		
		//当天访客，浏览数
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$uv['todayNum'] = $res['keys'];
		$pv['todayNum'] = $res['count'];
		
		//当天wap占比
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->group(['terminal'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$wapNum = 0;
		foreach($res['retval'] as $v){
			if($v['terminal'] == 'wap'){
				$wapNum = $v['num'];
			}
		}
		$uv['wapPercen'] = sprintf("%.2f",(($wapNum / $res['count'])*100)).'%';
		$pv['wapPercen'] = $uv['wapPercen'];
		//前一天访客，浏览数
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$prevDay])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$uv['yesterdayNum'] = $res['keys'];
		$pv['yesterdayNum'] = $res['count'];
		//同比增长率
		$uv['changePercen']=sprintf("%.2f",(($uv['todayNum'] - $uv['yesterdayNum'])/$uv['yesterdayNum']*100)).'%';
		$pv['changePercen']=sprintf("%.2f",(($pv['todayNum'] - $pv['yesterdayNum'])/$pv['yesterdayNum']*100)).'%';

		//当天支付金额
		$payPrice['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->sum('pay_price');
		$payPrice['todayNum'] = $payPrice['todayNum'] > 0 ? $payPrice['todayNum'] : 0 ;
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->sum('pay_price');
		$payPrice['wapPercen'] = sprintf("%.2f",(($wapNum / $payPrice['todayNum'])*100)).'%';
		//前一天支付金额
		$payPrice['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->sum('pay_price');
		//同比增长率
		$payPrice['changePercen']=sprintf("%.2f",(($payPrice['todayNum'] - $payPrice['yesterdayNum'])/$payPrice['yesterdayNum']*100)).'%';
		
		
		//当天支付订单数
		$payOrder['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->count();
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->count();
		$payOrder['wapPercen'] = sprintf("%.2f",(($wapNum / $payOrder['todayNum'])*100)).'%';
		//前一天支付订单数
		$payOrder['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->count();
		//同比增长率
		$payOrder['changePercen']=sprintf("%.2f",(($payOrder['todayNum'] - $payOrder['yesterdayNum'])/$payOrder['yesterdayNum']*100)).'%';
		
		
		//当天支付买家数
		$orderBuyer['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->group('uid')->count();
		$orderBuyer['todayNum'] = $orderBuyer['todayNum'] > 0 ? $orderBuyer['todayNum'] : 0 ;
		
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->group('uid')->count();
		$orderBuyer['wapPercen'] = sprintf("%.2f",(($wapNum / $orderBuyer['todayNum'])*100)).'%';
		//前一天支付买家数
		$orderBuyer['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->group('uid')->count();
		//同比增长率
		$orderBuyer['changePercen']=sprintf("%.2f",(($orderBuyer['todayNum'] - $orderBuyer['yesterdayNum'])/$orderBuyer['yesterdayNum']*100)).'%';
		
		//支付记录
		$payRecord = M('orders_shop')->field('date_format(pay_time, "%H") as hour,sum(pay_price) as pay_money,count(id) as order_num,uid')->where('shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" and pay_time < "'.$nextDay.'"')->order('pay_time asc')->group('date_format(pay_time, "%Y-%m-%d %H "),uid')->select();
		
		//按小时计算数据
		for($i=0;$i<=23;$i++){
			$tmp_buyerNum[$i] = 0;
			foreach($payRecord as $k => $v){
				if($i == (int)$v['hour']){
					$tmp_payMoney[$i] += $v['pay_money'];
				}				
			}			
		}
		$hourData['buyerNum'] = ['no'=>'3','data'=>$tmp_buyerNum];
		
		
		
		
		
		//核心指标
		$this->authApi('/TjShop/flow_basic',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1])->with('flow_basic');
	
		$this->authApi('/TjShop/trans_basic',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1])->with('trans_basic');
		
		//折线图
		$this->authApi('/TjShop/flow_trend',['shop_id'=>$this->shop_id,'dayType'=>3,'terminal'=>1])->with();
		foreach($this->_data['data']['data'] as $v){
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['year'] =  $v['year'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['month'] =  $v['month'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['day'] =  $v['day'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['uv'] =  $v['uv'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['pv'] =  $v['pv'];
		}
		$this->authApi('/TjShop/trand_trend',['shop_id'=>$this->shop_id,'dayType'=>3,'terminal'=>1])->with();
		foreach($this->_data['data']['data'] as $v){
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['buy_money_total'] =  $v['buy_money_total'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['uv_buy_percen'] =  $v['uv_buy_percen'];
			$core_list[$v['year'].'-'.$v['month'].'-'.$v['day']]['avg_buy_money'] =  $v['avg_buy_money'];
		}
		
		
		//搜索关键字
		$this->authApi('/TjShop/flow_search',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1,'limit'=>5])->with('flow_search');
		//来源列表
		$this->authApi('/TjShop/flow_source',['shop_id'=>$this->shop_id,'dayType'=>1])->with('flow_source');
		
		//商品分析
		$this->authApi('/TjShop/flow_source',['shop_id'=>$this->shop_id,'dayType'=>1])->with('flow_source');
		
		//商品总况
		$this->authApi('/TjShop/goods_basic',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1])->with('goods_basic');
		//异常商品统计
		$this->authApi('/TjShop/goods_abnormal_count',['shop_id'=>$this->shop_id,'dayType'=>1])->with('goods_abnormal_count');
		
		//商品访客排行
		$this->authApi('/TjShop/goods_list',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1,'order'=>'uv','now_page'=>1,'page_num'=>5],'keyword')->with('goods_uv_list');
	
		//商品支付金额排行
		$this->authApi('/TjShop/goods_list',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1,'order'=>'buy_money','now_page'=>1,'page_num'=>5],'keyword')->with('goods_buy_list');
		
		$this->assign('uv',$uv);
		$this->assign('pv',$pv);
		$this->assign('payPrice',$payPrice);
		$this->assign('payOrder',$payOrder);
		$this->assign('orderBuyer',$orderBuyer);
		$this->assign('hourData',$hourData);
		$this->assign('core_data',$core_data);
		$this->assign('core_list',$core_list);
		
        $this->display();
    }
	
	
	
	//流量地图
	public function flowMap(){
		
		//页面访客列表-时间类型
		$con['residence_type'] = is_numeric($_POST['residence_type']) ? I('post.residence_type') : 1;
		//页面访客列表-终端
		$con['residence_terminal'] = is_numeric($_POST['residence_terminal']) ? I('post.residence_terminal') : 1;
		
		//基本数据
		$this->authApi('/TjShop/flow_page_percen',['shop_id'=>$this->shop_id,'dayType'=>$con['residence_type'],'terminal'=>$con['residence_terminal']])->with('page_data');
		//页面访客列表
		$con['residence_page'] = is_numeric($_POST['residence_page']) ? I('post.residence_page') : 1;
		$this->authApi('/TjShop/flow_url_residence',['shop_id'=>$this->shop_id,'dayType'=>$con['residence_type'],'terminal'=>$con['residence_terminal'],'now_page'=>$con['residence_page']])->with('url_residence_list');
		
		
		//页面进入列表-时间类型
		$con['come_type'] = is_numeric($_POST['come_type']) ? I('post.come_type') : 1;
		//页面进入列表-终端
		$con['come_terminal'] = is_numeric($_POST['come_terminal']) ? I('post.come_terminal') : 1;
		//页面进入列表
		$con['come_page'] = is_numeric($_POST['come_page']) ? I('post.come_page') : 1;
		$this->authApi('/TjShop/flow_url_come',['shop_id'=>$this->shop_id,'dayType'=>$con['come_type'],'terminal'=>$con['come_terminal'],'now_page'=>$con['come_page']])->with('url_come_list');
		
		//页面离开列表-时间类型
		$con['go_type'] = is_numeric($_POST['go_type']) ? I('post.go_type') : 1;
		//页面离开列表-终端
		$con['go_terminal'] = is_numeric($_POST['go_terminal']) ? I('post.go_terminal') : 1;
		//页面离开列表
		$con['go_page'] = is_numeric($_POST['go_page']) ? I('post.go_page') : 1;
		$this->authApi('/TjShop/flow_url_go',['shop_id'=>$this->shop_id,'dayType'=>$con['go_type'],'terminal'=>$con['go_terminal'],'now_page'=>$con['go_page']])->with('url_go_list');
		
		//条件
		$this->assign('con',$con);
		$this->display();
	}
	//流量概况
	public function flowSurvey(){
	
		//页面访客列表-时间类型
		$con['total_type'] = is_numeric($_POST['total_type']) ? I('post.total_type') : 1;
		//页面访客列表-终端
		$con['total_terminal'] = is_numeric($_POST['total_terminal']) ? I('post.total_terminal') : 1;
		//总览数据
		$this->authApi('/TjShop/flow_basic',['shop_id'=>$this->shop_id,'dayType'=>$con['total_type'],'terminal'=>$con['total_terminal']])->with('total_data');
		
		
		//趋势时间类型
		$con['trend_type'] = is_numeric($_POST['trend_type']) ? I('post.trend_type') : 2;
		//趋势终端
		$con['trend_terminal'] = is_numeric($_POST['trend_terminal']) ? I('post.trend_terminal') : 1;
		//趋势数据
		$this->authApi('/TjShop/flow_trend',['shop_id'=>$this->shop_id,'dayType'=>$con['trend_type'],'terminal'=>$con['trend_terminal']])->with('trend_data');
		
		//来源时间类型
		$con['source_type'] = is_numeric($_POST['source_type']) ? I('post.source_type') : 1;
		//来源列表
		$this->authApi('/TjShop/flow_source',['shop_id'=>$this->shop_id,'dayType'=>$con['source_type']])->with('source_list');
		
		
		//访客行为时间类型
		$con['behavior_type'] = is_numeric($_POST['behavior_type']) ? I('post.behavior_type') : 1;
		//访客行为终端
		$con['behavior_terminal'] = is_numeric($_POST['behavior_terminal']) ? I('post.behavior_terminal') : 1;
		//搜索关键字列表
		$this->authApi('/TjShop/flow_search',['shop_id'=>$this->shop_id,'dayType'=>$con['behavior_type'],'terminal'=>$con['behavior_terminal'],'limit'=>5])->with('search_list');
		//商品访问列表
		$this->authApi('/TjShop/flow_goods_top',['shop_id'=>$this->shop_id,'dayType'=>$con['behavior_type'],'terminal'=>$con['behavior_terminal']])->with('goods_list');
	
		//访客特征时间类型
		$con['features_type'] = is_numeric($_POST['features_type']) ? I('post.features_type') : 1;
		//访客特征终端
		$con['features_terminal'] = is_numeric($_POST['features_terminal']) ? I('post.features_terminal') : 1;
		//每小时访客记录
		$this->authApi('/TjShop/flow_hour',['shop_id'=>$this->shop_id,'dayType'=>$con['features_type'],'terminal'=>$con['features_terminal']])->with('hour_list');
		//城市访问记录
		$this->authApi('/TjShop/flow_city',['shop_id'=>$this->shop_id,'dayType'=>$con['features_type'],'terminal'=>$con['features_terminal'],'limit'=>3])->with('city_list');
		//dump($this->_data);
		
		//新访客占比
		$this->authApi('/TjShop/flow_new_customer',['shop_id'=>$this->shop_id,'dayType'=>1,'terminal'=>1])->with('new_customer_data');
		//设备占比
		$this->authApi('/TjShop/flow_terminal',['shop_id'=>$this->shop_id,'dayType'=>1])->with('terminal_data');
		
		
		//条件
		//dump($con);
		$this->assign('con',$con);
		$this->display();
	}
	//访客分析
	public function flowVisit(){
		//时段分布-时间类型
		$con['hour_type'] = is_numeric($_POST['hour_type']) ? I('post.hour_type') : 1;
		//时段分布-终端
		$con['hour_terminal'] = is_numeric($_POST['hour_terminal']) ? I('post.hour_terminal') : 1;
		$this->authApi('/TjShop/flow_hour',['shop_id'=>$this->shop_id,'dayType'=>$con['hour_type'],'terminal'=>$con['hour_terminal']])->with('hour_list');
		
		//地域分布-时间类型
		$con['city_type'] = is_numeric($_POST['city_type']) ? I('post.city_type') : 1;
		//地域分布-终端
		$con['city_terminal'] = is_numeric($_POST['city_terminal']) ? I('post.city_terminal') : 1;
		//访客数分布
		$this->authApi('/TjShop/flow_city',['shop_id'=>$this->shop_id,'dayType'=>$con['city_type'],'terminal'=>$con['city_terminal'],'limit'=>34])->with('city_list');
		
		
		//会员等级及消费层次 特征-时间类型
		$con['feature_type'] = is_numeric($_POST['feature_type']) ? I('post.feature_type') : 1;
		//会员等级及消费层次 特征-终端
		$con['feature_terminal'] = is_numeric($_POST['feature_terminal']) ? I('post.feature_terminal') : 1;
		//会员等级分布
		$this->authApi('/TjShop/flow_user_level',['shop_id'=>$this->shop_id,'dayType'=>$con['feature_type'],'terminal'=>$con['feature_terminal']])->with('level_list');
		//消费层次分布
		$this->authApi('/TjShop/flow_money_level',['shop_id'=>$this->shop_id,'dayType'=>$con['feature_type'],'terminal'=>$con['feature_terminal']])->with('money_list');
		
		
		//行为分布-时间类型
		$con['behavior_type'] = is_numeric($_POST['behavior_type']) ? I('post.behavior_type') : 1;
		//行为分布-终端
		$con['behavior_terminal'] = is_numeric($_POST['behavior_terminal']) ? I('post.behavior_terminal') : 1;
		//搜索关键字分布
		$this->authApi('/TjShop/flow_search',['shop_id'=>$this->shop_id,'dayType'=>$con['behavior_type'],'terminal'=>$con['behavior_terminal'],'limit'=>3])->with('search_list');
		//浏览量分布
		$this->authApi('/TjShop/flow_user_pv',['shop_id'=>$this->shop_id,'dayType'=>$con['behavior_type'],'terminal'=>$con['behavior_terminal'],'limit'=>3])->with('pv_list');

		$this->assign('con',$con);
		$this->display();
	}
	//生成报表
	public function generateReport(){
		$this->authApi('/TjShop/get_quota',['shop_id'=>$this->shop_id])->with();
		$this->assign('quota',$this->_data['data']);
		$this->display();
	}
	
	//添加我的报表
	public function addMyReport(){
		if(I('post.fields') == ''){
			$this->ajaxReturn(['code'=>0,'msg'=>'需要勾选指标']);
		}
		$this->authApi('/TjShop/add_my_report',['shop_id'=>$this->shop_id,'quota_fields'=>I('post.fields'),'time_type'=>I('post.time_type')])->with();
		if($this->_data['code'] == 1){
			$this->ajaxReturn(['status'=>'success','msg'=>'添加成功！']);
		}else{
			$this->ajaxReturn(['status'=>'warning','msg'=>'添加失败！']);
		}
	}
	//预览数据
	public function previewReport(){
	
		//dump(I('post.fields'));
		//
		$this->authApi('/TjShop/get_report',['shop_id'=>$this->shop_id,'quota_fields'=>I('post.fields'),'time_type'=>I('post.time_type')])->with();
		
		//dump($this->data);
		
		$this->display();
	}
	//商品异常
	public function goodsAbnormal(){
	
		$con['pv_page'] = is_numeric($_POST['pv_page']) ? I('post.pv_page') : 1;
		//流量下跌
		$this->authApi('/TjShop/goods_abnormal',['shop_id'=>$this->shop_id,'type'=>1,'now_page'=>$con['pv_page']])->with('pv_list');
		
		$con['buy_page'] = is_numeric($_POST['buy_page']) ? I('post.buy_page') : 1;
		//付款下跌
		$this->authApi('/TjShop/goods_abnormal',['shop_id'=>$this->shop_id,'type'=>2,'now_page'=>$con['buy_page']])->with('buy_list');
		
		$con['zero_page'] = is_numeric($_POST['zero_page']) ? I('post.zero_page') : 1;
		//零支付
		$this->authApi('/TjShop/goods_abnormal',['shop_id'=>$this->shop_id,'type'=>3,'now_page'=>$con['zero_page']])->with('zero_list');
		
		$this->assign('con',$con);
		$this->display();
	}
	//商品效果
	public function goodsEffect(){
		//商品列表-时间类型
		$con['effect_type'] = is_numeric($_POST['effect_type']) ? I('post.effect_type') : 1;
		//商品列表-终端
		$con['effect_terminal'] = is_numeric($_POST['effect_terminal']) ? I('post.effect_terminal') : 1;
		//商品列表-搜索关键字
		$con['effect_keyword'] = isset($_POST['effect_keyword']) ? I('post.effect_keyword') : '';
		
		//选择字段
		$con['field_uv'] = is_numeric($_POST['field_uv']) ? I('post.field_uv') : 1;
		$con['field_pv'] = is_numeric($_POST['field_pv']) ? I('post.field_pv') : 1;
		$con['field_buy_num'] = is_numeric($_POST['field_buy_num']) ? I('post.field_buy_num') : 1;
		$con['field_buy_money'] = is_numeric($_POST['field_buy_money']) ? I('post.field_buy_money') : 1;
		$con['field_into_cart'] = is_numeric($_POST['field_into_cart']) ? I('post.field_into_cart') : 1;
		$con['field_fav_num'] = is_numeric($_POST['field_fav_num']) ? I('post.field_fav_num') : 1;
		$con['field_avg_residence_time'] = is_numeric($_POST['field_avg_residence_time']) ? I('post.field_avg_residence_time') : 1;
		
		//列表
		$con['effect_page'] = is_numeric($_POST['effect_page']) ? I('post.effect_page') : 1;
		$this->authApi('/TjShop/goods_list',['shop_id'=>$this->shop_id,'dayType'=>$con['effect_type'],'terminal'=>$con['effect_terminal'],'keyword'=>$con['effect_keyword'],'order'=>'uv','now_page'=>$con['effect_page'],'page_num'=>5],'keyword')->with('goods_list');
		
		
		$this->assign('con',$con);
		$this->display();
	}
	//商品概况
	public function goodsSurvey(){
		//商品总况-时间类型
		$con['total_type'] = is_numeric($_POST['total_type']) ? I('post.total_type') : 1;
		//商品总况-终端
		$con['total_terminal'] = is_numeric($_POST['total_terminal']) ? I('post.total_terminal') : 1;
		//商品总况
		$this->authApi('/TjShop/goods_basic',['shop_id'=>$this->shop_id,'dayType'=>$con['total_type'],'terminal'=>$con['total_terminal']])->with('goods_data');
		
		//商品趋势-时间类型
		$con['trend_type'] = is_numeric($_POST['trend_type']) ? I('post.trend_type') : 2;
		//商品趋势-终端
		$con['trend_terminal'] = is_numeric($_POST['trend_terminal']) ? I('post.trend_terminal') : 1;
		//商品总况
		$this->authApi('/TjShop/goods_trend',['shop_id'=>$this->shop_id,'dayType'=>$con['trend_type'],'terminal'=>$con['trend_terminal']])->with('trend_list');
		
		//商品排行-时间类型
		$con['rank_type'] = is_numeric($_POST['rank_type']) ? I('post.rank_type') : 1;
		//商品排行-终端
		$con['rank_terminal'] = is_numeric($_POST['rank_terminal']) ? I('post.rank_terminal') : 1;
		//商品排行-搜索关键字
		$con['rank_keyword'] = isset($_POST['rank_keyword']) ? I('post.rank_keyword') : '';
		//商品排行-访客列表页码
		$con['rank_p1'] = is_numeric($_POST['rank_p1']) ? I('post.rank_p1') : 1;
		//商品排行-支付金额列表页码
		$con['rank_p2'] = is_numeric($_POST['rank_p2']) ? I('post.rank_p2') : 1;
		
		//商品访客排行
		$this->authApi('/TjShop/goods_list',['shop_id'=>$this->shop_id,'dayType'=>$con['rank_type'],'terminal'=>$con['rank_terminal'],'keyword'=>$con['rank_keyword'],'order'=>'uv','now_page'=>$con['rank_p1'],'page_num'=>3],'keyword')->with('goods_uv_list');
		//dump($this->_data);
		//商品支付金额排行
		$this->authApi('/TjShop/goods_list',['shop_id'=>$this->shop_id,'dayType'=>$con['rank_type'],'terminal'=>$con['rank_terminal'],'keyword'=>$con['rank_keyword'],'order'=>'buy_money','now_page'=>$con['rank_p2'],'page_num'=>3],'keyword')->with('goods_buy_list');
		
		//dump($this->_data);
		//dump($con);
		$this->assign('con',$con);
		$this->display();
	}
	//我的报表
	public function myReport(){
		$this->authApi('/TjShop/my_report',['shop_id'=>$this->shop_id])->with();
		//dump($this->data);
		$this->display();
	}
	//导出报表
	public function exportReport(){
		
		$this->authApi('/TjShop/get_report',['shop_id'=>$this->shop_id,'quota_fields'=>I('post.fields'),'time_type'=>I('post.time_type')])->with();
		//dump($this->_data);
		
		//如果有则
		if($this->_data['data']){
			$out_option_orders = 'A';
			foreach($this->_data['data']['fields'] as $k => $v){
				$out_excel_option[$out_option_orders]['descript'] = $v['title'];
				$out_excel_option[$out_option_orders]['field'] = $v['field'];
				$out_option_orders++;
			}
			//dump($out_excel_option);
			D('Admin/Excel')->outExcel($this->_data['data']['data'],$out_excel_option,'导出报表');
		}else{
			exit('没有数据');
		}
		
	}
	//促销效果
	public function promotionEffect(){
		//促销总况-时间类型
		$con['effect_type'] = is_numeric($_POST['effect_type']) ? I('post.effect_type') : 1;
		//促销总况-终端
		$con['effect_terminal'] = is_numeric($_POST['effect_terminal']) ? I('post.effect_terminal') : 1;
		//促销类型
		$con['effect_pro_type'] = is_numeric($_POST['effect_pro_type']) ? I('post.effect_pro_type') : 0;
		
		//列表
		$con['effect_page'] = is_numeric($_POST['effect_page']) ? I('post.effect_page') : 1;
		$this->authApi('/TjShop/promotion_list',['shop_id'=>$this->shop_id,'dayType'=>$con['effect_type'],'terminal'=>$con['effect_terminal'],'pro_type'=>$con['effect_pro_type'],'now_page'=>$con['effect_page'],'page_num'=>5])->with('promotion_list');
		
		//促销类别
		$activity_type = M('activity_type')->field('id,activity_name')->select();
		foreach($activity_type as $v){
			$promotion_type[$v['id']] = $v;
		}
		
		//dump($this->_data);
		//dump($con);
		$this->assign('con',$con);
		$this->assign('promotion_type',$promotion_type);
		$this->display();
	}
	//促销概况
	public function promotionSurvey(){
		//促销总况-时间类型
		$con['total_type'] = is_numeric($_POST['total_type']) ? I('post.total_type') : 1;
		//促销总况-终端
		$con['total_terminal'] = is_numeric($_POST['total_terminal']) ? I('post.total_terminal') : 1;
		//促销总况
		$this->authApi('/TjShop/promotion_basic',['shop_id'=>$this->shop_id,'dayType'=>$con['total_type'],'terminal'=>$con['total_terminal']])->with('promotion_data');
		
		
		//促销趋势-时间类型
		$con['trend_type'] = is_numeric($_POST['trend_type']) ? I('post.trend_type') : 2;
		//促销趋势-终端
		$con['trend_terminal'] = is_numeric($_POST['trend_terminal']) ? I('post.trend_terminal') : 1;
		//选择字段
		$con['field_buy_num'] = is_numeric($_POST['field_buy_num']) ? I('post.field_buy_num') : 1;
		$con['field_buy_money'] = is_numeric($_POST['field_buy_money']) ? I('post.field_buy_money') : 1;
		$con['field_ad_money'] = is_numeric($_POST['field_ad_money']) ? I('post.field_ad_money') : 1;
		$con['field_coupon_money'] = is_numeric($_POST['field_coupon_money']) ? I('post.field_coupon_money') : 1;
		//促销趋势
		$this->authApi('/TjShop/promotion_trend',['shop_id'=>$this->shop_id,'dayType'=>$con['trend_type'],'terminal'=>$con['trend_terminal']])->with('trend_list');
		
		//dump($this->_data);
		//dump($con);
		$this->assign('con',$con);
		$this->display();
	}
	//实时榜单
	public function realtimeList(){
		//查询的日期
		$selectDay = isset($_POST['selectDay']) ? I('post.selectDay') : $this->nowDay ;
		//前一天
		$prevDay = date('Y-m-d',(strtotime($selectDay) - 86400));
		
		
		
		
		/*
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'page_type'=>'view'])->group(['goods_id'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		dump($this->model->getlastsql());
		//$res = $this->model->command('ylscdb.ylh_tongji_shop.group({key:{"goods_id":1},cond:{"shop_id":"206","date":"2016-10-05","page_type":"view"},reduce:"function(obj, prev){prev.num++;}",initial:{"num":0}})');
		dump($res);
		
		*/
		
		//$res = $this->model->command(array('group'=>['ns'=>'ylh_tongji_shop','key'=>['goods_id'=>1], 'initial'=>['num'=>0],'$reduce'=>"function(obj, prev){prev.num++;}",'condition'=>['shop_id'=>$this->shop_id]]));
		/*
		{$match:{library_id:3607}},
		 {$limit:5},
		 {$group:{_id:"$version", count: {$sum:1}}},
		 {$sort:{count:-1}}
		 */
		//$a['$limit'] = 5;
		//$a['$group'] = ['_id'=>'"$goods_id"','count'=>['$sum'=>1],'total'=>['$sum'=>'"$sum"']];
		//$a['$sort'] = ['count'=>1];
		//dump(json_encode($a));
		
		
		
		
	
		
		
		//访客数Top
		if(isset($_POST['goods_name'])){
			$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,page_type:1,goods_id:1,attr_id:1,goods_name:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'",page_type:"view",goods_name:"'.I('post.goods_name').'"}},{$group:{_id:"$goods_id",num:{"$sum":1},goods_name:{"$last":"$goods_name"},attr_id:{"$last":"$attr_id"}}},{$sort:{num:-1}},{$limit:50}])');
		}else{
			$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,page_type:1,goods_id:1,attr_id:1,goods_name:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'",page_type:"view"}},{$group:{_id:"$goods_id",num:{"$sum":1},goods_name:{"$last":"$goods_name"},attr_id:{"$last":"$attr_id"}}},{$sort:{num:-1}},{$limit:50}])');
		}
		
		$visitList = $res['result'];
		
		/*
		//清理没有goods_id的数据
		foreach($visitList as $k => $v){
			if(empty($v['_id']['goods_id'])){
				unset($visitList[$k]);
			}
		}
		$visitList = array_merge($visitList);
		*/
		//获取当前页面的商品
		/*
		$page1 = isset($_GET['p1']) ? I('get.p1') : 1;
		$count1 = count($visitList) > 50 ? 50 : count($visitList) ;
		$totalPage1 = ceil($count1/3);
		$start1 = ($page1-1)*3;
		$visitList = array_slice($visitList,$start1,3);
		*/
		$page1['nowPage'] = isset($_GET['p1']) ? I('get.p1') : 1;
		$page1['count'] = count($visitList) > 50 ? 50 : count($visitList) ;
		$page1['pageNum'] = 3;
		$page1['pageShow'] = 5;
		$page1['start'] = ($page1['nowPage']-1)*3;
		$page1 = $this->_page($page1);
		
		$visitList = array_slice($visitList,$page1['start'],3);
		
		
		//查找商品的各种信息
		$visitTop = array();
		foreach($visitList as $k=>$v){
			$tmp_array = array();
			//排名
			$tmp_array['no'] = $page1['start']+$k+1;
			$tmp_array['goods_id'] = $v['_id'];
			//浏览数
			$tmp_array['pv'] = $v['num'];
			//访客数
			$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'page_type'=>'view','goods_id'=>$v['_id']])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
			$tmp_array['uv'] = $res['keys'];
			$tmp_array['attr_id'] = $v['attr_id'];
			//支付买家数
			$res = M('orders_goods')->field('uid,count(*)')->where('goods_id = '.$v['_id'].' and s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" )')->group('uid')->select();
			$tmp_array['buyer'] = count($res) > 0 ? count($res) : 0 ;
			//支付金额
			$res = M('orders_goods')->where('goods_id = '.$v['_id'].' and s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" )')->sum('total_price_edit');
			$tmp_array['money'] =  $res > 0 ? $res : 0 ;
			$tmp_array['buyerPercen'] = sprintf('%.2f',$tmp_array['buyer']/$tmp_array['uv']*100);
			//商品属性
			$this->api('/Goods/view', ['id' => $v['attr_id']])->with('res');
			$tmp_array['goodsInfo'] = $this->res;
			$visitTop[] = $tmp_array;
		}
		
		
		//支付金额Top
		if(isset($_POST['goods_name'])){
			$buyList = M('orders_goods')->field('goods_id,attr_list_id')->where('s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" and goods_name like "%'.I('post.goods_name').'%" )')->group('goods_id')->order('sum(total_price_edit) desc,goods_id desc')->limit(50)->select();
		}else{
			$buyList = M('orders_goods')->field('goods_id,attr_list_id')->where('s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" )')->group('goods_id')->order('sum(total_price_edit) desc,goods_id desc')->limit(50)->select();
		}
		
		
		//获取当前页面的商品
		/*
		$page2 = isset($_GET['p2']) ? I('get.p2') : 1;
		$count2 = count($buyList) > 50 ? 50 : count($buyList) ;
		$totalPage2 = ceil($count2/3);
		$start2 = ($page2-1)*3;
		$buyList = array_slice($buyList,$start2,3);
		$buyTop = array();
		*/
		$page2['nowPage'] = isset($_GET['p2']) ? I('get.p2') : 1;
		$page2['count'] = count($buyList) > 50 ? 50 : count($buyList);
		$page2['pageNum'] = 3;
		$page2['start'] = ($page2['nowPage']-1)*3;
		$page2 = $this->_page($page2);
		$buyList = array_slice($buyList,$page2['start'],3);
		$buyTop = array();
		foreach($buyList as $k => $v){
			$tmp_array = array();
			//排名
			$tmp_array['no'] = $page2['start']+$k+1;
			$tmp_array['goods_id'] = $v['goods_id'];
			//浏览数
			$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'page_type'=>'view','goods_id'=>$v['goods_id']])->count();
			
			$tmp_array['pv'] = $res;
			//访客数
			$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'page_type'=>'view','goods_id'=>$v['goods_id']])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
			$tmp_array['uv'] = $res['keys'];
			//商品属性id
			$tmp_array['attr_id'] = $v['attr_list_id'];
			//支付买家数
			$res = M('orders_goods')->field('uid,count(*)')->where('goods_id = '.$v['goods_id'].' and s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" )')->group('uid')->select();
			$tmp_array['buyer'] = count($res) > 0 ? count($res) : 0 ;
			//支付金额
			$res = M('orders_goods')->where('goods_id = '.$v['goods_id'].' and s_id IN(select id from ylh_orders_shop where shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" )')->sum('total_price_edit');
			
			$tmp_array['money'] =  $res > 0 ? $res : 0 ;
			$tmp_array['buyerPercen'] = sprintf('%.2f',$tmp_array['buyer']/$tmp_array['uv']*100);
			//商品属性
			$this->api('/Goods/view', ['id' => $tmp_array['attr_id']])->with('res');
			$tmp_array['goodsInfo'] = $this->res;
			$buyTop[] = $tmp_array;
		}
		
	
		
		$this->assign('buyTop',$buyTop);
		$this->assign('visitTop',$visitTop);
		$this->assign('page1',$page1);
		$this->assign('page2',$page2);
		$this->display();
	}
	
	
	//实时来源
	public function realtimeSource(){
		//查询的日期
		$selectDay = isset($_GET['selectDay']) ? I('get.selectDay') : $this->nowDay ;

		//前一天
		$prevDay = date('Y-m-d',(strtotime($selectDay) - 86400));

		//PC当天来源
		$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,source:1,terminal:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'",terminal:"pc"}},{$group:{_id:"$source",num:{"$sum":1}}},{$sort:{num:-1}},{$limit:5}])');
		$pcTop = $res['result'];
		
		//PC当天付款IP
		$pcPayIps = M('orders_shop')->field('ip,count(*) as num')->where('shop_id = '.$this->shop_id.' and terminal = 0 and pay_time>"'.$selectDay.'"')->group('ip')->select();
		foreach($pcPayIps as $k => $v){
			$res = $this->model->field('ip,source')->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'ip'=>$v['ip']])->group(['source'=>1],['num'=>0],"function(obj, prev){prev.num++;}");
			$pcVisitIps = $res['retval'];
			if(!empty($pcVisitIps)){
				foreach($pcVisitIps as $ke=>$va){
					foreach($pcTop as $key=>$val){
						if($va['source'] == $val['_id']){
							$pcTop[$key]['payNum'] += $v['num'];
						}
					}
				}
			}
		}
		foreach($pcTop as $k => $v){
			if($v['_id'] == 'search'){$pcTop[$k]['source'] ='站内搜索';}
			if($v['_id'] == 'ad'){$pcTop[$k]['source'] ='首页广告';}
			if($v['_id'] == 'goods_fav'){$pcTop[$k]['source'] ='商品收藏';}
			if($v['_id'] == 'shop_fav'){$pcTop[$k]['source'] ='店铺收藏';}
			if($v['_id'] == 'other'){$pcTop[$k]['source'] ='其他来源';}
			if($v['_id'] == 'shop'){$pcTop[$k]['source'] ='店内跳转';}
		}
		
	
		
		//WAP当天来源
		$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,source:1,terminal:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'",terminal:"wap"}},{$group:{_id:"$source",num:{"$sum":1}}},{$sort:{num:-1}},{$limit:5}])');
		$wapTop = $res['result'];
		//WAP当天付款IP
		$wapPayIps = M('orders_shop')->field('ip,count(*) as num')->where('shop_id = '.$this->shop_id.' and terminal = 1 and pay_time>"'.$selectDay.'"')->group('ip')->select();
		foreach($wapPayIps as $k => $v){
			$res = $this->model->field('ip,source')->where(['shop_id'=>$this->shop_id,'date'=>$selectDay,'ip'=>$v['ip']])->group(['source'=>1],['num'=>0],"function(obj, prev){prev.num++;}");
			$wapVisitIps = $res['retval'];
			if(!empty($wapVisitIps)){
				foreach($wapVisitIps as $ke=>$va){
					foreach($wapTop as $key=>$val){
						if($va['source'] == $val['_id']){
							$wapTop[$key]['payNum'] += $v['num'];
						}
					}
				}
			}
		}
		foreach($wapTop as $k => $v){
			if($v['_id'] == 'search'){$wapTop[$k]['source'] ='站内搜索';}
			if($v['_id'] == 'ad'){$wapTop[$k]['source'] ='首页广告';}
			if($v['_id'] == 'goods_fav'){$wapTop[$k]['source'] ='商品收藏';}
			if($v['_id'] == 'shop_fav'){$wapTop[$k]['source'] ='店铺收藏';}
			if($v['_id'] == 'other'){$wapTop[$k]['source'] ='其他来源';}
			if($v['_id'] == 'shop'){$wapTop[$k]['source'] ='店内跳转';}
		}
		
		
		//总访问量
		$pv = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->count();
		//访客地域分布
		$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,city:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'"}},{$group:{_id:"$city",num:{"$sum":1}}},{$sort:{num:-1}},{$limit:5}])');
		$visitMap = $res['result'];
		foreach($visitMap as $k => $v){
			if($v['_id']){
				$visitMap[$k]['city'] = $v['_id'];
			}else{
				$visitMap[$k]['city'] = '未知';
			}
			$visitMap[$k]['percen'] = sprintf("%.2f",$v['num']/$pv*100);
		}
		
		
		
		//支付买家数分布
		$buyerMap = array();
		$buyerCount = 0;
		$res = M('orders_shop')->field('ip')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->select();
		if(!empty($res)){
			foreach($res as $k => $v){
				$buyerCity = $this->_getIpLookup($v['ip']);
				if(isset($buyerMap[$buyerCity])){
					$buyerMap[$buyerCity]['num']++;
				}else{
					$array = array('city'=>$buyerCity,'num'=>1);
					$buyerMap[] = $array;
				}
				$buyerCount ++;
			}
		}
		foreach($buyerMap as $k => $v){
			if(!$v['city']){
				unset($buyerMap[$k]);
			}else{
				$buyerMap[$k]['percen'] = sprintf("%.2f",$v['num']/$buyerCount*100);
			}
		}
		$buyerMap = $this->_bubble($buyerMap,'num');
		
		$this->assign('pcTop',$pcTop);
		$this->assign('wapTop',$wapTop);
		$this->assign('visitMap',$visitMap);
		$this->assign('buyerMap',$buyerMap);
		$this->display();
	}
	
	//实时概况
	public function realtimeSurvey(){
		//查询的日期
		$selectDay = isset($_GET['selectDay']) ? I('get.selectDay') : $this->nowDay ;
		//后一天
		$nextDay = date('Y-m-d',(strtotime($selectDay) + 86400));
		//前一天
		$prevDay = date('Y-m-d',(strtotime($selectDay) - 86400));
		
		//当天访客，浏览数
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$uv['todayNum'] = $res['keys'];
		$pv['todayNum'] = $res['count'];
		
		//当天wap占比
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->group(['terminal'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$wapNum = 0;
		foreach($res['retval'] as $v){
			if($v['terminal'] == 'wap'){
				$wapNum = $v['num'];
			}
		}
		$uv['wapPercen'] = sprintf("%.2f",(($wapNum / $res['count'])*100)).'%';
		$pv['wapPercen'] = $uv['wapPercen'];
		//前一天访客，浏览数
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$prevDay])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		$uv['yesterdayNum'] = $res['keys'];
		$pv['yesterdayNum'] = $res['count'];
		//同比增长率
		$uv['changePercen']=sprintf("%.2f",(($uv['todayNum'] - $uv['yesterdayNum'])/$uv['yesterdayNum']*100)).'%';
		$pv['changePercen']=sprintf("%.2f",(($pv['todayNum'] - $pv['yesterdayNum'])/$pv['yesterdayNum']*100)).'%';

		//当天支付金额
		$payPrice['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->sum('pay_price');
		$payPrice['todayNum'] = $payPrice['todayNum'] > 0 ? $payPrice['todayNum'] : 0 ;
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->sum('pay_price');
		$payPrice['wapPercen'] = sprintf("%.2f",(($wapNum / $payPrice['todayNum'])*100)).'%';
		//前一天支付金额
		$payPrice['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->sum('pay_price');
		//同比增长率
		$payPrice['changePercen']=sprintf("%.2f",(($payPrice['todayNum'] - $payPrice['yesterdayNum'])/$payPrice['yesterdayNum']*100)).'%';
		
		
		//当天支付订单数
		$payOrder['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->count();
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->count();
		$payOrder['wapPercen'] = sprintf("%.2f",(($wapNum / $payOrder['todayNum'])*100)).'%';
		//前一天支付订单数
		$payOrder['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->count();
		//同比增长率
		$payOrder['changePercen']=sprintf("%.2f",(($payOrder['todayNum'] - $payOrder['yesterdayNum'])/$payOrder['yesterdayNum']*100)).'%';
		
		
		//当天支付买家数
		$orderBuyer['todayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'"')->group('uid')->count();
		$orderBuyer['todayNum'] = $orderBuyer['todayNum'] > 0 ? $orderBuyer['todayNum'] : 0 ;
		
		//wap占比
		$wapNum = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$selectDay.'" and terminal = 1')->group('uid')->count();
		$orderBuyer['wapPercen'] = sprintf("%.2f",(($wapNum / $orderBuyer['todayNum'])*100)).'%';
		//前一天支付买家数
		$orderBuyer['yesterdayNum'] = M('orders_shop')->where('shop_id = '.$this->shop_id.' and pay_time>"'.$prevDay.'" and pay_time<"'.$selectDay.'"')->group('uid')->count();
		//同比增长率
		$orderBuyer['changePercen']=sprintf("%.2f",(($orderBuyer['todayNum'] - $orderBuyer['yesterdayNum'])/$orderBuyer['yesterdayNum']*100)).'%';
		

		
		//访客记录
		$res =$this->model->execute('db.ylh_tongji_shop.aggregate([{$project:{shop_id:1,date:1,hour:1,key:1}},{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'"}},{$group:{_id:{hour:"$hour",key:"$key"}}}])');
		$uvRecord = $res['result'];
		
		//支付记录
		$payRecord = M('orders_shop')->field('date_format(pay_time, "%H") as hour,sum(pay_price) as pay_money,count(id) as order_num,uid')->where('shop_id = "'.$this->shop_id.'" and pay_time > "'.$selectDay.'" and pay_time < "'.$nextDay.'"')->order('pay_time asc')->group('date_format(pay_time, "%Y-%m-%d %H "),uid')->select();
	
		
		//按小时计算数据
		for($i=0;$i<=23;$i++){
			$tmp_visit[$i] = 0;
			$tmp_payMoney[$i] = 0;
			$tmp_buyerNum[$i] = 0;
			$tmp_orderNum[$i] = 0;
			
			foreach($uvRecord as $k => $v){
				if($i == (int)$v['_id']['hour']){
					$tmp_visit[$i] += 1;
				}				
			}	
			foreach($payRecord as $k => $v){
				if($i == (int)$v['hour']){
					$tmp_payMoney[$i] += $v['pay_money'];
					$tmp_buyerNum[$i] += 1;
					$tmp_orderNum[$i] += $v['order_num'];
				}				
			}			
		}
		$hourData['visit'] = ['no'=>'1','data'=>$tmp_visit];
		$hourData['payMoney'] = ['no'=>'2','data'=>$tmp_payMoney];
		$hourData['buyerNum'] = ['no'=>'3','data'=>$tmp_buyerNum];
		$hourData['orderNum'] = ['no'=>'4','data'=>$tmp_orderNum];
		
		//dump($hourData);
		$this->assign('uv',$uv);
		$this->assign('pv',$pv);
		$this->assign('payPrice',$payPrice);
		$this->assign('payOrder',$payOrder);
		$this->assign('orderBuyer',$orderBuyer);
		$this->assign('hourData',$hourData);
		$this->assign('selectDay',$selectDay);
		$this->display();
	}
	//实时访客
	public function realtimeVisit(){
		//查询的日期
		$selectDay = isset($_POST['selectDay']) ? I('post.selectDay') : $this->nowDay ;
		/*
		$page = isset($_GET['p']) ? I('get.p') : 1;
		$count = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->count();
		$totalPage = ceil($count/5);
		$start = ($page-1)*5;
		*/
		$res = $this->model->where(['shop_id'=>$this->shop_id,'date'=>$selectDay])->group(['key'=>1], ['num'=>0], "function(obj, prev){prev.num++;}");
		
		$page['nowPage'] = isset($_GET['p']) ? I('get.p') : 1;
		$page['count'] = $res['keys'];
		$page['pageNum'] = 5;
		$page['pageShow'] = 5;
		$page['start'] = ($page['nowPage']-1)*$page['pageNum'];

		//获取uv列表
		$res = $this->model->execute('db.ylh_tongji_shop.aggregate([{$match:{shop_id:"'.$this->shop_id.'",date:"'.$selectDay.'"}},{$sort:{datetime:-1}},{$group:{_id:"$key",vister:{"$last":"$key"},lastTime:{"$first":"$datetime"},source:{"$last":"$source"},sourceUrl:{"$last":"$url"},visitUrl:{"$first":"$url"}}},{$sort:{lastTime:-1}},{$skip:'.$page['start'].'},{$limit:5}])');
		$visitList = $res['result'];

		foreach($visitList as $k => $v){
			$visitList[$k]['no'] = $page['start']+$k+1;
			if($v['source'] == 'search'){$visitList[$k]['source'] ='站内搜索';}
			if($v['source'] == 'ad'){$visitList[$k]['source'] ='首页广告';}
			if($v['source'] == 'goods_fav'){$visitList[$k]['source'] ='商品收藏';}
			if($v['source'] == 'shop_fav'){$visitList[$k]['source'] ='店铺收藏';}
			if($v['source'] == 'shop'){$visitList[$k]['source'] ='店内跳转';}
			if($v['source'] == 'other'){$visitList[$k]['source'] ='其他来源';}
		}
		
		$page = $this->_page($page);
		//dump($page);
		$this->assign("page",$page);
		$this->assign("visitList",$visitList);
		$this->display();
	}
	//推荐报表
	public function recommendReport(){
		$this->authApi('/TjShop/my_report',['shop_id'=>0])->with();
		$this->display();
	}
	//交易构成
	public function transactionConstitute(){
		//终端构成-时间类型
		$con['terminal_type'] = is_numeric($_POST['terminal_type']) ? I('post.terminal_type') : 1;
		//终端构成-终端
		$con['terminal_terminal'] = is_numeric($_POST['terminal_terminal']) ? I('post.terminal_terminal') : 1;
		//终端构成
		$this->authApi('/TjShop/trand_terminal',['shop_id'=>$this->shop_id,'dayType'=>$con['terminal_type'],'terminal'=>$con['terminal_terminal']])->with('terminal_list');
		
		//类目构成-时间类型
		$con['category_type'] = is_numeric($_POST['category_type']) ? I('post.category_type') : 1;
		//类目构成-终端
		$con['category_terminal'] = is_numeric($_POST['category_terminal']) ? I('post.category_terminal') : 1;
		//类目构成
		$this->authApi('/TjShop/trand_category',['shop_id'=>$this->shop_id,'dayType'=>$con['category_type'],'terminal'=>$con['category_terminal']])->with('category_list');
		
		//类目构成-时间类型
		$con['brand_type'] = is_numeric($_POST['brand_type']) ? I('post.brand_type') : 1;
		//类目构成-终端
		$con['brand_terminal'] = is_numeric($_POST['brand_terminal']) ? I('post.brand_terminal') : 1;
		//类目构成
		$this->authApi('/TjShop/trand_brand',['shop_id'=>$this->shop_id,'dayType'=>$con['brand_type'],'terminal'=>$con['brand_terminal']])->with('brand_list');
		
		//价格带构成-时间类型
		$con['money_level_type'] = is_numeric($_POST['money_level_type']) ? I('post.money_level_type') : 1;
		//价格带构成-终端
		$con['money_level_terminal'] = is_numeric($_POST['money_level_terminal']) ? I('post.money_level_terminal') : 1;
		//价格带构成
		$this->authApi('/TjShop/flow_money_level',['shop_id'=>$this->shop_id,'dayType'=>$con['money_level_type'],'terminal'=>$con['money_level_terminal']])->with('money_list');

		$this->assign('con',$con);
		$this->display();
	}
	//交易概况
	public function transactionSurvey(){
		//交易总览-时间类型
		$con['total_type'] = is_numeric($_POST['total_type']) ? I('post.total_type') : 1;
		//交易总览-终端
		$con['total_terminal'] = is_numeric($_POST['total_terminal']) ? I('post.total_terminal') : 1;
		//交易总览数据
		$this->authApi('/TjShop/trans_basic',['shop_id'=>$this->shop_id,'dayType'=>$con['total_type'],'terminal'=>$con['total_terminal']])->with('trans_data');
		
		//商品趋势-时间类型
		$con['trend_type'] = is_numeric($_POST['trend_type']) ? I('post.trend_type') : 2;
		//商品趋势-终端
		$con['trend_terminal'] = is_numeric($_POST['trend_terminal']) ? I('post.trend_terminal') : 1;
		//选择字段
		$con['field_buy_user_total'] = is_numeric($_POST['field_buy_user_total']) ? I('post.field_buy_user_total') : 1;
		$con['field_buy_money_total'] = is_numeric($_POST['field_buy_money_total']) ? I('post.field_buy_money_total') : 1;
		$con['field_avg_buy_money'] = is_numeric($_POST['field_avg_buy_money']) ? I('post.field_avg_buy_money') : 1;
		$con['field_uv_order_percen'] = is_numeric($_POST['field_uv_order_percen']) ? I('post.field_uv_order_percen') : 1;
		$con['field_uv_buy_percen'] = is_numeric($_POST['field_uv_buy_percen']) ? I('post.field_uv_buy_percen') : 1;
		$con['field_order_buy_percen'] = is_numeric($_POST['field_order_buy_percen']) ? I('post.field_order_buy_percen') : 1;
		//交易趋势列表
		$this->authApi('/TjShop/trand_trend',['shop_id'=>$this->shop_id,'dayType'=>$con['trend_type'],'terminal'=>$con['trend_terminal']])->with('trans_list');
		
		$this->assign('con',$con);
		$this->display();
	}
	
	//排序
	public function _bubble($array,$field,$state=1){
		$len = count($array);
		for($i=1;$i<$len;$i++){
			for($j=$len-1;$j>=$i;$j--){
				if($state){
					//从大到小
					if($array[$j][$field]>$array[$j-1][$field]){
						$x=$array[$j];
						$array[$j]=$array[$j-1];
						$array[$j-1]=$x;
					}
				}else{
					if($array[$j][$field]<$array[$j-1][$field]){
						$x=$array[$j];
						$array[$j]=$array[$j-1];
						$array[$j-1]=$x;
					}
				}
				
			}
		}
		return $array;
	}
	
	//获取IP所在城市
	public function _getIpLookup($ip = ''){  
		if(empty($ip)){  
			return false;
		}
		$res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);  
		if(empty($res)){ return false; }  
		$jsonMatches = array();  
		preg_match('#\{.+?\}#', $res, $jsonMatches);  
		if(!isset($jsonMatches[0])){ return false; }  
		$json = json_decode($jsonMatches[0], true);  
		if(isset($json['ret']) && $json['ret'] == 1){  
			$json['ip'] = $ip;  
			unset($json['ret']);  
		}else{  
			return false;  
		}  
		return $json['province'];
	}  
	//分页
	public function _page($page){
		$page['totalPage'] = ceil($page['count']/$page['pageNum']);
		//中间页码
		if($page['totalPage']<$page['pageShow']){
			for($i=1;$i<=$page['totalPage'];$i++){
				$tmp_page['p'] = $i;
				if($i == $page['nowPage']){
						$tmp_page['active'] = 1;
					}else{
						$tmp_page['active'] = 0;
					}
				$page['page'][] = $tmp_page;
				
			}
		}else{
			//页面展示的中间值
			$page['middle'] = ceil($page['pageShow']/2);
			if($page['nowPage']<$page['middle']){
				for($i=1;$i<=$page['pageShow'];$i++){
					$tmp_page['p'] = $i;
					if($i == $page['nowPage']){
						$tmp_page['active'] = 1;
					}else{
						$tmp_page['active'] = 0;
					}
					
					$page['page'][] = $tmp_page;
				}
			}else if($page['nowPage'] + $page['pageShow'] > $page['totalPage']){
				for($i=$page['totalPage']-$page['pageShow']+1;$i<=$page['totalPage'];$i++){
					$tmp_page['p'] = $i;
					if($i == $page['nowPage']){
						$tmp_page['active'] = 1;
					}else{
						$tmp_page['active'] = 0;
					}
					$page['page'][] = $tmp_page;
				}
			}else{
				for($i=$page['nowPage']-$page['middle']+1;$i<=$page['nowPage']+$page['middle']-1;$i++){
					$tmp_page['p'] = $i;
					if($i == $page['nowPage']){
						$tmp_page['active'] = 1;
					}else{
						$tmp_page['active'] = 0;
					}
					$page['page'][] = $tmp_page;
				}
			}
		}
		//上一页
		if($page['nowPage'] != 1){
			$page['prevPage'] = $page['nowPage']-1;
		}
		//下一页
		if($page['nowPage'] != $page['totalPage']){
			$page['nextPage'] = $page['nowPage']+1;
		}
		
		return $page;
	}
}