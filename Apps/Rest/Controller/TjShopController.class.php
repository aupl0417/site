<?php
namespace Rest\Controller;
use Think\Model\MongoModel as Mongo;
# 统计
class TjShopController extends CommonController
{

	# 统计类型

	

	static protected $dbConfig;
	static protected $dbTable;

	public $sql;
	public $page_type;

	
	static protected function dbConfig(){
		self::$dbConfig or self::$dbConfig = C('DB_MONGO_CONFIG');
		return self::$dbConfig;
	}

	static protected function dbTable(){
		self::$dbTable or self::$dbTable = C('DB_MONGO_CONFIG.DB_PREFIX') . 'tongji_shop';
		return self::$dbTable;
	}

	public function index(){
    	redirect(C('sub_domain.www'));
    }

	public function setting(){
		$this->sql = M('')->db(1,"mysqli://root:123456@192.168.3.203:3306/dtmall_shop_analysis");
		
		
		//页面类型
		$this->page_type = array(
			'home'=>'店铺首页',
			'view'=>'商品详情页',
			'search'=>'搜索结果页',
			'list'=>'商品分类页',
			'other'=>'其他页面'
		);
		
		//指标字段
		$this->quota = array(
			'flow'=>array(
				'title'=>'流量',
				'cate'=>'flow',
				'data'=>array(
					'uv'=>array('field'=>'uv','title'=>'访客数'),
					'pv'=>array('field'=>'pv','title'=>'浏览量'),
					'jump'=>array('field'=>'jump','title'=>'跳失率'),
					'avg_pv'=>array('field'=>'avg_pv','title'=>'人均浏览量'),
					'avg_residence_time'=>array('field'=>'avg_residence_time','title'=>'平均停留时间（秒)'),
					'uv_home'=>array('field'=>'uv_home','title'=>'店铺首页访客数'),
					'uv_view'=>array('field'=>'uv_view','title'=>'商品详情页访客数'),
					'uv_search'=>array('field'=>'uv_search','title'=>'搜索结果页访客数'),
					'uv_list'=>array('field'=>'uv_list','title'=>'商品分类页访客数'),
					'uv_other'=>array('field'=>'uv_other','title'=>'其他页访客数'),
				),
			),
			'goods'=>array(
				'title'=>'商品',
				'cate'=>'goods',
				'data'=>array(
					'uv'=>array('field'=>'uv','title'=>'商品访客数'),
					'pv'=>array('field'=>'pv','title'=>'商品浏览量'),
					'visit_num'=>array('field'=>'visit_num','title'=>'被访问的商品数'),
					'avg_residence_time'=>array('field'=>'avg_residence_time','title'=>'平均停留时间'),
					'jump'=>array('field'=>'jump','title'=>'详情跳出率'),
					'into_cart'=>array('field'=>'into_cart','title'=>'加入购物车件数'),
					'pay_num'=>array('field'=>'pay_num','title'=>'支付件数'),
					'abnormal'=>array('field'=>'abnormal','title'=>'异常商品数'),
					'fav'=>array('field'=>'fav','title'=>'收藏数'),
				),
			),
			'trans'=>array(
				'title'=>'交易',
				'cate'=>'trans',
				'data'=>array(
					'order_user_total'=>array('field'=>'order_user_total','title'=>'下单用户数'),
					'order_money_total'=>array('field'=>'order_money_total','title'=>'下单金额'),
					'buy_user_total'=>array('field'=>'buy_user_total','title'=>'支付用户数'),
					'buy_money_total'=>array('field'=>'buy_money_total','title'=>'支付金额'),
					'buy_goods_total'=>array('field'=>'buy_goods_total','title'=>'支付商品数'),
					'avg_buy_money'=>array('field'=>'avg_buy_money','title'=>'客单价'),
				),
			),
			'promotion'=>array(
				'title'=>'促销',
				'cate'=>'promotion',
				'data'=>array(
					'buy_num'=>array('field'=>'buy_num','title'=>'成交订单数'),
					'buy_money'=>array('field'=>'buy_money','title'=>'促销成交金额'),
					'ad_num'=>array('field'=>'ad_num','title'=>'广告投放笔数'),
					'ad_money'=>array('field'=>'ad_money','title'=>'广告投放金额'),
					'coupon_money'=>array('field'=>'coupon_money','title'=>'使用优惠卷金额'),
				),
			),
		);
	}

    /**
	 * 记录访问
	 */
	public function visit(){
		$this->need_param = ['server','session','shop_info','session_id','terminal','page_type'];
		$this->_need_param();
		$this->_check_sign();

		$server = json_decode($_POST['server'], true);
		$session = json_decode($_POST['session'], true);
		$shop_info = json_decode($_POST['shop_info'], true);
		
		$data['test'] = "20161105";
		if(isset($_POST['goods_id']) && !empty($_POST['goods_id'])){
			$data['goods_id']	= I('post.goods_id');	
		}
		if(isset($_POST['attr_id']) && !empty($_POST['attr_id'])){
			$data['attr_id']	= I('post.attr_id');	
		}
		if(isset($_POST['goods_name']) && !empty($_POST['goods_name'])){
			$data['goods_name']	= I('post.goods_name');	
		}
		if(isset($_POST['search_key']) && !empty($_POST['search_key'])){
			$data['search_key']	= I('post.search_key');	
		}
		$data['is_login']	= empty($session['user']) ? false : true;									//是否登录
		$data['ref'] 		= isset($server['HTTP_REFERER']) ? $server['HTTP_REFERER'] : null;			//上一个访问页面
		$data['url'] = $this->_curPageURL($server);
		//$data['sub']		= explode('.', $server['HTTP_HOST'])[0];									//访问链接的主机名
		//$data['we_ref_sub']	= self::parseRefererUrl($data['ref']);
		//$data['is_we_ref'] 	= is_null($data['we_ref_sub']) ? false : true;		
		$data['date'] 		= date('Y-m-d');															//日期
		$data['datetime']	= date('Y-m-d H:i:s');														//时间
		$data['time_stamp']	= time();																	//时间戳
		$data['residence_time'] = 0;																	//页面停留时间
		$data['last_page'] = true;																		//是否该用户访问的最后一页
		$data['first_page'] = false;																	//是否该用户访问的第一页
		$data['hour']		= date('H');																//小时
		$data['ip']			= $server['REMOTE_ADDR'];													//IP地址
		$data['city']		= $this->_getIpLookup($data['ip']);											//访问城市
		$data['key'] 		= I('post.session_id');														//session_id
		$data['shop_id']	= $shop_info['id'];															//店铺ID
		$data['terminal']	= I('post.terminal');														//终端
		$data['page_type']	= I('post.page_type');														//页面类别 
		$data['source']		= $this->_getSource($data['ref']);											//来源 
		
		if($data['is_login']){
			$data['uid'] = $session['user']['id'];
			$data['user_name'] = $session['user']['nick'];
			$data['user_level'] = $session['user']['level_name'];
			
		}
		$data['user']		= $data['is_login'] ? $session['user'] : null ;								//会员信息
		$data['server']		= $server;
		$data['session']		= $session;
		$data['shop_info']		= $shop_info;
		$model = new Mongo(self::dbTable(), null, self::dbConfig());
		//查询此用户是否已经访问过
		$prev_visit = $model->field('_id,time_stamp')->where(['key'=>I('post.session_id')])->order('time_stamp desc')->find();
		if(!empty($prev_visit)){
			//计算停留时间
			$residence_time = ((time())-$prev_visit['time_stamp']);
			$model->where(['_id'=>$prev_visit['_id']])->data(['residence_time'=>$residence_time,'last_page'=>false])->save();
		}else{
			//将此次访问定义为第一次访问
			$data['first_page'] = true;
		}
		$model->add($data);
		$this->apiReturn(1,['data' => $data]);
	}

	//流量统计-流量总览
	public function flow_basic(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_flow_basic')->field('sum(`uv`) as uv,sum(`pv`) as pv,sum(`jump`) as jump,avg(`avg_pv`) as avg_pv,avg(`avg_residence_time`) as avg_residence_time')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();

		//查询上一周期的数据
		$last_list = $this->sql->table('ylh_flow_basic')->field('sum(`uv`) as uv,sum(`pv`) as pv,sum(`jump`) as jump,avg(`avg_pv`) as avg_pv,avg(`avg_residence_time`) as avg_residence_time')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['lastDay'].'" and atime < "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();
		
		$res['uv'] = $list['uv'];
		$res['uv_percen'] = sprintf("%.2f",($list['uv']-$last_list['uv'])/$last_list['uv']*100);
		$res['uv_type'] = $list['uv']>$last_list['uv']?1:0;
		
		$res['pv'] = $list['pv'];
		$res['pv_percen'] = sprintf("%.2f",($list['pv']-$last_list['pv'])/$last_list['pv']*100);
		$res['pv_type'] = $list['pv']>$last_list['pv']?1:0;
		
		$res['jump'] = $list['jump'];
		$res['jump_percen'] = sprintf("%.2f",($list['jump']-$last_list['jump'])/$last_list['jump']*100);
		$res['jump_type'] = $list['jump']>$last_list['jump']?1:0;
		
		$res['avg_pv'] = sprintf("%.2f",$list['avg_pv']);
		$res['avg_pv_percen'] = sprintf("%.2f",($list['avg_pv']-$last_list['avg_pv'])/$last_list['avg_pv']*100);
		$res['avg_pv_type'] = $list['avg_pv']>$last_list['avg_pv']?1:0;
		
		$res['avg_residence_time'] =  sprintf("%.2f",$list['avg_residence_time']);
		$res['avg_residence_time_percen'] = sprintf("%.2f",($list['avg_residence_time']-$last_list['avg_residence_time'])/$last_list['avg_residence_time']*100);
		$res['avg_residence_time_type'] = $list['avg_residence_time']>$last_list['avg_residence_time']?1:0;
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-流量趋势
	public function flow_trend(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
        $this->setting();
		
		$total_day = $this->_time_type(I('post.dayType'));
		$total_terminal = $this->_terminal_type(I('post.terminal'));
	
		//查询日期的数据
		$list = $this->sql->table('ylh_flow_basic')->field('year,month,day,uv,pv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "'.$total_terminal.'"')->order('atime asc')->select();
		
		$data['sql'] = $this->sql->getlastsql();
		$data['data'] = $list;
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-流量来源
	public function flow_source(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$total_day = $this->_time_type(I('post.dayType'));
		
		$all_list = $this->sql->table('ylh_flow_source')->field('source_title,pv,buy_percen')->where('shop_id = "'.I('post.shop_id').'" and atime > "'.$total_day['startDay'].'" and terminal = "all"')->order('pv desc')->limit(5)->select();
		$pc_list = $this->sql->table('ylh_flow_source')->field('source_title,pv,buy_percen')->where('shop_id = "'.I('post.shop_id').'" and atime > "'.$total_day['startDay'].'" and terminal = "pc"')->order('pv desc')->limit(5)->select();
		$wap_list = $this->sql->table('ylh_flow_source')->field('source_title,pv,buy_percen')->where('shop_id = "'.I('post.shop_id').'" and atime > "'.$total_day['startDay'].'" and terminal = "wap"')->order('pv desc')->limit(5)->select();
		
		$res['pc_list'] = $pc_list;
		$res['wap_list'] = $wap_list;
		$res['all_list'] = $all_list;
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-搜索关键字
	public function flow_search(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','limit');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		$limit = I('post.limit');
		
		//取得所查询期间的总uv
		$total_uv = $this->sql->table('ylh_flow_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		//$sql = $this->sql->getlastsql();
		
		//取得所查询期间的总搜索次数
		$total_num = $this->sql->table('ylh_user_search')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('num');
		$total_num = $total_num > 0 ? $total_num : 0 ;
	
		//取得所查询期间的搜索关键字数据
		$list = $this->sql->table('ylh_user_search')->field('`keyword`,sum(`num`) as num,sum(`order_num`) as order_num')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('keyword')->order('num desc')->limit($limit)->select();
		
		
		foreach($list as $k => $v){
			$list[$k]['num_percen'] = sprintf("%.2f",$v['num']/$total_num*100);
			$list[$k]['uv_order_percen'] = sprintf("%.2f",$v['order_num']/$total_uv*100);
		}
		
		
		$data['data'] = $list;
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-商品top3
	public function flow_goods_top(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$total_day = $this->_time_type(I('post.dayType'));
		$total_terminal = $this->_terminal_type(I('post.terminal'));
		
		$list = $this->sql->table('ylh_goods_effect_list')->field('goods_name,price,images,uv,uv_buy_percen')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "'.$total_terminal.'"')->order('uv desc')->limit(3)->select();
		
		$data['data'] = $list;
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-每小时访客记录
	public function flow_hour(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$total_day = $this->_time_type(I('post.dayType'));
		$total_terminal = $this->_terminal_type(I('post.terminal'));
		
		$res = $this->sql->table('ylh_flow_hour')->field('hour,uv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "'.$total_terminal.'"')->order('atime asc')->select();
		
		foreach($res as $v){
			$r[$v['hour']]+=$v['uv'];
		}
		ksort($r);
	
		if($r){
            $this->apiReturn(1,['data' => $r]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-城市分布
	public function flow_city(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','limit');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		$limit = I('post.limit');
		
		//取得所查询期间的总uv
		$total_uv = $this->sql->table('ylh_flow_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		
		//取得所查询期间的总支付用户数
		$buy_user_total = $this->sql->table('ylh_user_city')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('buy_user_total');
		$buy_user_total = $buy_user_total > 0 ? $buy_user_total : 0 ;
		
		//取得所查询期间的数据
		$list = $this->sql->table('ylh_user_city')->field('city,sum(`uv`) as uv,sum(`order_user_total`) as order_user_total,sum(`buy_user_total`) as buy_user_total')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('city')->order('uv desc')->limit($limit)->select();
		
		foreach($list as $k=>$v){
			$list[$k]['uv_percen'] =sprintf("%.2f",$v['uv']/$total_uv*100);
			$list[$k]['uv_order_percen'] =sprintf("%.2f",$v['order_user_total']/$total_uv*100);
			$list[$k]['buy_user_percen'] =sprintf("%.2f",$v['buy_user_total']/$buy_user_total*100);
		}
		
		$data['data'] = $list;
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-新老客户占比
	public function flow_new_customer(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$total_day = $this->_time_type(I('post.dayType'));
		$total_terminal = $this->_terminal_type(I('post.terminal'));
		
		$res = $this->sql->table('ylh_flow_basic')->field('uv,new_customer')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "'.$total_terminal.'"')->find();

		$res['old_customer'] = $res['uv']-$res['new_customer'];
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-设备占比
	public function flow_terminal(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$total_day = $this->_time_type(I('post.dayType'));
		
		
		$pc_data = $this->sql->table('ylh_flow_basic')->field('uv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "pc"')->find();
		$wap_data = $this->sql->table('ylh_flow_basic')->field('uv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$total_day['startDay'].'" and terminal = "wap"')->find();

		if(!empty($pc_data)){
			$res['pc'] = $pc_data['uv'];
		}else{
			$res['pc'] = 0;
		}
		if(!empty($wap_data)){
			$res['wap'] = $wap_data['uv'];
		}else{
			$res['wap'] = 0;
		}
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//流量统计-页面占比
	public function flow_page_percen(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		$res = $this->sql->table('ylh_flow_basic')->field('uv,uv_home,uv_view,uv_search,uv_list,uv_other')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->select();

		foreach($res as $v){
			$total_uv += $v['uv'];
			$total_uv_home += $v['uv_home'];
			$total_uv_view += $v['uv_view'];
			$total_uv_search += $v['uv_search'];
			$total_uv_list += $v['uv_list'];
			$total_uv_other += $v['uv_other'];
		}
		
		$data['uv'] = $total_uv;
		$data['uv_home'] = $total_uv_home;
		$data['uv_home_percen'] = sprintf("%.2f",$total_uv_home/$total_uv*100);
		
		$data['uv_view'] = $total_uv_view;
		$data['uv_view_percen'] = sprintf("%.2f",$total_uv_view/$total_uv*100);
		
		$data['uv_search'] = $total_uv_search;
		$data['uv_search_percen'] = sprintf("%.2f",$total_uv_search/$total_uv*100);
		
		$data['uv_list'] = $total_uv_list;
		$data['uv_list_percen'] = sprintf("%.2f",$total_uv_list/$total_uv*100);
		
		$data['uv_other'] = $total_uv_other;
		$data['uv_other_percen'] = sprintf("%.2f",$total_uv_other/$total_uv*100);
		
		
		if($data){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//页面访客列表
	public function flow_url_residence(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','now_page');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		//查询总记录
		$res = $this->sql->table('ylh_flow_url_residence')->field('COUNT(DISTINCT url) as count')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->find();
		$res['nowPage'] = I('post.now_page');
		//分页
		$page = $this->_page($res);
		
		$list = $this->sql->table('ylh_flow_url_residence')->field('url,page_type,sum(uv) as uv,sum(pv) as pv,avg(avg_residence_time) as avg_residence_time')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('url')->order('pv desc')->limit($page['start'],$page['pageNum'])->select();
		
		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
			$list[$k]['page_title'] = $this->page_type[$v['page_type']];
			$list[$k]['avg_residence_time'] = sprintf("%.2f",$v['avg_residence_time']);
		}
		
		$data['page'] = $page;
		$data['list'] = $list;
		$data['sql'] = $this->sql->getlastsql();
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//页面来源列表
	public function flow_url_come(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','now_page');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		//查询总记录
		$res = $this->sql->table('ylh_flow_url_come')->field('COUNT(DISTINCT url) as count')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->find();
		$res['nowPage'] = I('post.now_page');
		//分页
		$page = $this->_page($res);
		
		$list = $this->sql->table('ylh_flow_url_come')->field('url,page_type,sum(uv) as uv,sum(pv) as pv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('url')->order('pv desc')->limit($page['start'],$page['pageNum'])->select();
		
		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
			$list[$k]['page_title'] = $this->page_type[$v['page_type']];
			$total_pv += $v['pv'];
		}
		
		foreach($list as $k => $v){
			$list[$k]['pv_percen'] = sprintf("%.2f",$v['pv']/$total_pv*100);
		}
		$data['page'] = $page;
		$data['list'] = $list;
		$data['sql'] = $this->sql->getlastsql();
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}	
	//页面离开列表
	public function flow_url_go(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','now_page');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		//查询总记录
		$res = $this->sql->table('ylh_flow_url_go')->field('COUNT(DISTINCT url) as count')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->find();
		$res['nowPage'] = I('post.now_page');
		//分页
		$page = $this->_page($res);
		
		$list = $this->sql->table('ylh_flow_url_go')->field('url,page_type,sum(uv) as uv,sum(pv) as pv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('url')->order('pv desc')->limit($page['start'],$page['pageNum'])->select();
		
		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
			$list[$k]['page_title'] = $this->page_type[$v['page_type']];
			$total_pv += $v['pv'];
		}
		
		foreach($list as $k => $v){
			$list[$k]['pv_percen'] = sprintf("%.2f",$v['pv']/$total_pv*100);
		}
		$data['page'] = $page;
		$data['list'] = $list;
		$data['sql'] = $this->sql->getlastsql();
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//会员等级分布
	public function flow_user_level(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		$list = $this->sql->table('ylh_user_level')->field('level_name,uv,order_num')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->order('uv desc')->select();
		
		foreach($list as $v){
			$data[$v['level_name']]['level_name'] = $v['level_name'];
			$data[$v['level_name']]['uv'] += $v['uv'];
			$data[$v['level_name']]['order_num'] += $v['order_num'];
			$total_uv += $v['uv'];
			$total_order_num += $v['order_num'];
		}
		foreach($data as $k => $v){
			$data[$k]['uv_percen'] =  sprintf("%.2f",$v['uv']/$total_uv*100);
			$data[$k]['uv_order_percen'] =  sprintf("%.2f",$v['order_num']/$total_order_num*100);
		}
		$res['sql'] = $this->sql->getlastsql();
		$res['data'] = $data;
		if($data){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//付款金额分布
	public function flow_money_level(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		//取得所查询期间的总uv
		$total_uv = $this->sql->table('ylh_flow_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		
		//取得所查询期间的总下单用户 总支付金额
		$total = $this->sql->table('ylh_trans_basic')->field('sum(`order_user_total`) as order_user_total,sum(`buy_money_total`) as buy_money_total')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->find();
		$order_user_total = $total['order_user_total'] > 0 ? $total['order_user_total'] : 0 ;
		$buy_money_total = $total['buy_money_total'] > 0 ? $total['buy_money_total'] : 0 ;
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_trans_money')->field('title,sum(`order_user`) as order_user,sum(`buy_money`) as buy_money,sum(`buy_goods_num`) as buy_goods_num,sum(`buy_user`) as buy_user')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('money_level')->order('money_level asc')->select();
		
		foreach($list as $k => $v){
			
			$list[$k]['order_user_percen'] = sprintf("%.2f",$v['order_user']/$order_user_total*100);
			$list[$k]['buy_money_percen'] = sprintf("%.2f",$v['buy_money']/$buy_money_total*100);
			$list[$k]['uv_order_percen'] = sprintf("%.2f",$v['order_user']/$total_uv*100);
			$list[$k]['uv_buy_percen'] = sprintf("%.2f",$v['buy_user']/$total_uv*100);
		}
		$res['data'] = $list;
		/*
		$list = $this->sql->table('ylh_trans_money')->field('title,order_user')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->order('money_level asc')->select();
		//取得每个价格带的下单用户总数
		foreach($list as $v){
			$data[$v['title']]['title'] = $v['title'];
			$data[$v['title']]['order_user'] += $v['order_user'];
			$total_order_user += $v['order_user'];
		}
		//取得所查询期间的总uv
		$list = $this->sql->table('ylh_flow_basic')->field('uv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->select();
		foreach($list as $v){
			$total_uv += $v['uv'];
		}
		//取得下单用户占比 和 下单转化率
		foreach($data as $k =>$v){
			$data[$k]['order_user_percen'] =  sprintf("%.2f",$v['order_user']/$total_order_user*100);
			$data[$k]['uv_order_percen'] =  sprintf("%.2f",$v['order_user']/$total_uv*100);
		}
		$res['data'] = $data;
		*/
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//浏览量分布
	public function flow_user_pv(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','limit');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		$limit = I('post.limit');
		
		//取得所查询期间的总访问用户
		$total_uv = $this->sql->table('ylh_user_pv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		
		//取得所查询期间的浏览量分布数据
		$list = $this->sql->table('ylh_user_pv')->field('title,sum(`uv`) as uv')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('pv_level')->order('pv_level asc')->limit($limit)->select();
		
		foreach($list as $k => $v){
			$list[$k]['uv_percen'] =  sprintf("%.2f",$v['uv']/$total_uv*100);
		}
		
		$data['data'] = $list;
		$data['sql'] = $this->sql->getlastsql();
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//商品基本情况
	public function goods_basic(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_goods_basic')->field('sum(`uv`) as uv,sum(`pv`) as pv,sum(`jump`) as jump,sum(`visit_num`) as visit_num,avg(`avg_residence_time`) as avg_residence_time,sum(`jump`) as jump,sum(`into_cart`) as into_cart,sum(`pay_num`) as pay_num,sum(`abnormal`) as abnormal,sum(`fav`) as fav')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();
		
		//查询上一周期的数据
		$last_list = $this->sql->table('ylh_goods_basic')->field('sum(`uv`) as uv,sum(`pv`) as pv,sum(`jump`) as jump,sum(`visit_num`) as visit_num,avg(`avg_residence_time`) as avg_residence_time,sum(`jump`) as jump,sum(`into_cart`) as into_cart,sum(`pay_num`) as pay_num,sum(`abnormal`) as abnormal,sum(`fav`) as fav')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['lastDay'].'" and atime < "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();
		
		$res['uv'] = $list['uv'];
		$res['uv_percen'] = sprintf("%.2f",($list['uv']-$last_list['uv'])/$last_list['uv']*100);
		$res['uv_type'] = $list['uv']>$last_list['uv']?1:0;
		
		$res['pv'] = $list['pv'];
		$res['pv_percen'] = sprintf("%.2f",($list['pv']-$last_list['pv'])/$last_list['pv']*100);
		$res['pv_type'] = $list['pv']>$last_list['pv']?1:0;
		
		$res['visit_num'] = $list['visit_num'];
		$res['visit_num_percen'] = sprintf("%.2f",($list['visit_num']-$last_list['visit_num'])/$last_list['visit_num']*100);
		$res['visit_num_type'] = $list['visit_num']>$last_list['visit_num']?1:0;
		
		$res['avg_residence_time'] =  sprintf("%.2f",$list['avg_residence_time']);
		$res['avg_residence_time_percen'] = sprintf("%.2f",($list['avg_residence_time']-$last_list['avg_residence_time'])/$last_list['avg_residence_time']*100);
		$res['avg_residence_time_type'] = $list['avg_residence_time']>$last_list['avg_residence_time']?1:0;
		
		$res['jump'] = $list['jump'];
		$res['jump_percen'] = sprintf("%.2f",($list['jump']-$last_list['jump'])/$last_list['jump']*100);
		$res['jump_type'] = $list['jump']>$last_list['jump']?1:0;
		
		$res['into_cart'] = $list['into_cart'];
		$res['into_cart_percen'] = sprintf("%.2f",($list['into_cart']-$last_list['into_cart'])/$last_list['into_cart']*100);
		$res['into_cart_type'] = $list['into_cart']>$last_list['into_cart']?1:0;
		
		$res['pay_num'] = $list['pay_num'];
		$res['pay_num_percen'] = sprintf("%.2f",($list['pay_num']-$last_list['pay_num'])/$last_list['pay_num']*100);
		$res['pay_num_type'] = $list['pay_num']>$last_list['pay_num']?1:0;
		
		$res['abnormal'] = $list['abnormal'];
		$res['abnormal_percen'] = sprintf("%.2f",($list['abnormal']-$last_list['abnormal'])/$last_list['abnormal']*100);
		$res['abnormal_type'] = $list['abnormal']>$last_list['abnormal']?1:0;
		
		$res['fav'] = $list['fav'];
		$res['fav_percen'] = sprintf("%.2f",($list['fav']-$last_list['fav'])/$last_list['fav']*100);
		$res['fav_type'] = $list['fav']>$last_list['fav']?1:0;
		
		
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//商品销售趋势
	public function goods_trend(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		$list = $this->sql->table('ylh_goods_basic')->field('year,month,day,uv,visit_num,pay_num')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->order('atime asc')->select();
		
		$data['data'] = $list;
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//商品排行
	public function goods_list(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','order','now_page','page_num');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		$order = I('post.order');
		
		$where['shop_id'] = I('post.shop_id');
		$where['atime'] = ['gt',$day['startDay']];
		$where['terminal'] = $terminal;
		
		if(isset($_POST['keyword']) && !empty($_POST['keyword'])){
			$where['goods_name'] = ['like','%'.I('post.keyword').'%'];
		}
		
		//查询总记录
		$res = $this->sql->table('ylh_goods_effect_list')->field('COUNT(DISTINCT goods_id) as count')->where($where)->find();
		$res['nowPage'] = I('post.now_page');
		$res['pageNum'] = I('post.page_num');
		//分页
		$page = $this->_page($res);
		
		$list = $this->sql->table('ylh_goods_effect_list')->field('goods_id,goods_name,images,price,sum(uv) as uv,sum(pv) as pv,sum(buy_money) as buy_money,sum(buyer) as buyer,sum(into_cart) as into_cart,sum(fav_num) as fav_num,avg(avg_residence_time) as avg_residence_time,sum(buy_num) as buy_num')->where($where)->group('goods_id')->order($order.' desc')->limit($page['start'],$page['pageNum'])->select();
		
		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
			$list[$k]['uv_buy_percen'] = sprintf("%.2f",$v['buyer']/$v['uv']*100);
		}
		
		$data['page'] = $page;
		$data['list'] = $list;
		$data['sql'] = $this->sql->getlastsql();

		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3,['data' => $data]);
        }
	}
	//
	public function goods_abnormal_count(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$day = $this->_time_type(I('post.dayType'));
		
		$res['pv_down'] = 0;
		$res['buy_down'] = 0;
		$res['zero'] = 0;
		
		
		$list = $this->sql->table('ylh_goods_abnormal')->field('type,count(*) as num')->where('shop_id = '.I('post.shop_id').' and atime> "'.$day['startDay'].'"')->group('type')->select();
		foreach($list as $v){
			if($v['type'] == 1){
				$res['pv_down'] = $v['num'];
			}else if($v['type'] == 2){
				$res['buy_down'] = $v['num'];
			}else if($v['type'] == 3){
				$res['zero'] = $v['num'];
			}
		}
		if($list){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	//异常商品列表
	public function goods_abnormal(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','type','now_page');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(1);
		//异常类型
		$type = I('post.type');
		
		//查询总记录
		$res = $this->sql->table('ylh_goods_abnormal')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and type = "'.$type.'"')->count();
		$page['count'] = $res;
		$page['nowPage'] = I('post.now_page');
		//分页
		$page = $this->_page($page);
		
		$list = $this->sql->table('ylh_goods_abnormal')->field('goods_name,images,price,prev_pv,pv,prev_buy_money,buy_money')->where('shop_id = '.I('post.shop_id').' and atime> "'.$day['startDay'].'" and type = "'.$type.'"')->limit($page['start'],$page['pageNum'])->order('pv asc')->select();

		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
			$list[$k]['pv_down_percen'] = sprintf("%.2f",($v['prev_pv']-$v['pv'])/$v['prev_pv']*100);
			$list[$k]['buy_money_down_percen'] = sprintf("%.2f",($v['prev_buy_money']-$v['buy_money'])/$v['prev_buy_money']*100);
		}
		
		$data['page'] = $page;
		$data['data'] = $list;
		$data['sql'] = $this->sql->getlastsql();
		
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//交易基本情况
	public function trans_basic(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_trans_basic')->field('sum(`uv`) as uv,sum(`order_user_total`) as order_user_total,sum(`order_money_total`) as order_money_total,sum(`buy_user_total`) as buy_user_total,sum(`buy_money_total`) as buy_money_total,avg(`avg_buy_money`) as avg_buy_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();

		//查询上一周期的数据
		$last_list = $this->sql->table('ylh_trans_basic')->field('sum(`uv`) as uv,sum(`order_user_total`) as order_user_total,sum(`order_money_total`) as order_money_total,sum(`buy_user_total`) as buy_user_total,sum(`buy_money_total`) as buy_money_total,avg(`avg_buy_money`) as avg_buy_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['lastDay'].'" and atime < "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();
		
		//$res['list'] = $list;
		//$res['last_list'] = $last_list;
		
		$res['uv'] = $list['uv'];
		$res['uv_percen'] = sprintf("%.2f",($list['uv']-$last_list['uv'])/$last_list['uv']*100);
		$res['uv_type'] = $list['uv']>$last_list['uv']?1:0;
		
		$res['order_user_total'] = $list['order_user_total'];
		$res['order_user_total_percen'] = sprintf("%.2f",($list['order_user_total']-$last_list['order_user_total'])/$last_list['order_user_total']*100);
		$res['order_user_total_type'] = $list['order_user_total']>$last_list['order_user_total']?1:0;
		
		$res['order_money_total'] = $list['order_money_total'];
		$res['order_money_total_percen'] = sprintf("%.2f",($list['order_money_total']-$last_list['order_money_total'])/$last_list['order_money_total']*100);
		$res['order_money_total_type'] = $list['order_money_total']>$last_list['order_money_total']?1:0;
		
		$res['buy_user_total'] = sprintf("%.2f",$list['buy_user_total']);
		$res['buy_user_total_percen'] = sprintf("%.2f",($list['buy_user_total']-$last_list['buy_user_total'])/$last_list['buy_user_total']*100);
		$res['buy_user_total_type'] = $list['buy_user_total']>$last_list['buy_user_total']?1:0;
		
		$res['buy_money_total'] =  sprintf("%.2f",$list['buy_money_total']);
		$res['buy_money_total_percen'] = sprintf("%.2f",($list['buy_money_total']-$last_list['buy_money_total'])/$last_list['buy_money_total']*100);
		$res['buy_money_total_type'] = $list['buy_money_total']>$last_list['buy_money_total']?1:0;
		
		$res['avg_buy_money'] =  sprintf("%.2f",$list['avg_buy_money']);
		$res['avg_buy_money_percen'] = sprintf("%.2f",($list['avg_buy_money']-$last_list['avg_buy_money'])/$last_list['avg_buy_money']*100);
		$res['avg_buy_money_type'] = $list['avg_buy_money']>$last_list['avg_buy_money']?1:0;
		
		
		$res['uv_order_percen'] = sprintf("%.2f",$list['order_user_total']/$list['uv']*100);
		$res['uv_buy_percen'] = sprintf("%.2f",$list['buy_user_total']/$list['uv']*100);
		$res['order_buy_percen'] = sprintf("%.2f",$list['buy_user_total']/$list['order_user_total']*100);
		
		
	
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
		
	}
	//交易趋势
	public function trand_trend(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		$list = $this->sql->table('ylh_trans_basic')->field('year,month,day,uv,order_user_total,buy_money_total,buy_user_total,order_money_total,avg_buy_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->order('atime asc')->select();
		
		foreach($list as $k => $v){
			$list[$k]['uv_order_percen'] = sprintf("%.2f",$v['order_user_total']/$v['uv']*100);
			$list[$k]['uv_buy_percen'] = sprintf("%.2f",$v['buy_user_total']/$v['uv']*100);
			$list[$k]['order_buy_percen'] = sprintf("%.2f",$v['buy_user_total']/$v['order_user_total']*100);
		}
		$data['data'] = $list;
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//交易终端构成
	public function trand_terminal(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_trans_basic')->field('terminal,sum(`uv`) as uv,sum(`buy_goods_total`) as buy_goods_total,sum(`buy_user_total`) as buy_user_total,sum(`buy_money_total`) as buy_money_total')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'"')->group('terminal')->select();
		$res['sql'] = $this->sql->getlastsql();
		
		foreach($list as $v){
			$v['uv_buy_percen'] = sprintf("%.2f",$v['buy_user_total']/$v['uv']*100);
			$terminal_list[$v['terminal']] = $v;
		}
		
		$terminal_list['pc']['buy_money_total_percen'] = sprintf("%.2f",$terminal_list['pc']['buy_money_total']/$terminal_list['all']['buy_money_total']*100);
		$terminal_list['wap']['buy_money_total_percen'] = sprintf("%.2f",$terminal_list['wap']['buy_money_total']/$terminal_list['all']['buy_money_total']*100);
		unset($terminal_list['all']);
		$res['data'] = $terminal_list;
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
		
	}
	//交易分类构成
	public function trand_category(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		
		//取得所查询期间的总uv
		$total_uv = $this->sql->table('ylh_flow_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		
		//取得所查询期间的总购买金额
		$total_buy_money = $this->sql->table('ylh_trans_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('buy_money_total');
		$total_buy_money = $total_buy_money > 0 ? $total_buy_money : 0 ;
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_trans_cat')->field('cat_name,sum(`buy_money`) as buy_money,sum(`buy_goods_num`) as buy_goods_num,sum(`buy_user`) as buy_user')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'"')->group('cat_name')->order('buy_money desc')->limit(3)->select();
		$res['sql'] = $this->sql->getlastsql();
		
		
		foreach($list as $k => $v){
			$list[$k]['buy_money_percen'] = sprintf("%.2f",$v['buy_money']/$total_buy_money*100);
			$list[$k]['uv_buy_percen'] = sprintf("%.2f",$v['buy_user']/$total_uv*100);
		}
	
		
		$res['data'] = $list;
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
		
	}
	//交易品牌构成
	public function trand_brand(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		
		//取得所查询期间的总uv
		$total_uv = $this->sql->table('ylh_flow_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('uv');
		$total_uv = $total_uv > 0 ? $total_uv : 0 ;
		
		//取得所查询期间的总购买金额
		$total_buy_money = $this->sql->table('ylh_trans_basic')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->sum('buy_money_total');
		$total_buy_money = $total_buy_money > 0 ? $total_buy_money : 0 ;
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_trans_brand')->field('b_name,sum(`buy_money`) as buy_money,sum(`buy_goods_num`) as buy_goods_num,sum(`buy_user`) as buy_user')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'"')->group('b_name')->order('buy_money desc')->limit(3)->select();
		$res['sql'] = $this->sql->getlastsql();
		
		
		foreach($list as $k => $v){
			$list[$k]['buy_money_percen'] = sprintf("%.2f",$v['buy_money']/$total_buy_money*100);
			$list[$k]['uv_buy_percen'] = sprintf("%.2f",$v['buy_user']/$total_uv*100);
		}
	
		
		$res['data'] = $list;
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
		
	}
	//促销基本情况
	public function promotion_basic(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		
		//查询选择周期的数据
		$list = $this->sql->table('ylh_promotion_basic')->field('sum(`buy_num`) as buy_num,sum(`buy_money`) as buy_money,sum(`ad_num`) as ad_num,sum(`ad_money`) as ad_money,sum(`coupon_money`) as coupon_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();

		//查询上一周期的数据
		$last_list = $this->sql->table('ylh_promotion_basic')->field('sum(`buy_num`) as buy_num,sum(`buy_money`) as buy_money,sum(`ad_num`) as ad_num,sum(`ad_money`) as ad_money,sum(`coupon_money`) as coupon_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['lastDay'].'" and atime < "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->group('shop_id')->find();
		
		$res['list'] = $list;
		$res['last_list'] = $last_list;
		
		$res['buy_num'] = $list['buy_num'];
		$res['buy_num_percen'] = sprintf("%.2f",($list['buy_num']-$last_list['buy_num'])/$last_list['buy_num']*100);
		$res['buy_num_type'] = $list['buy_num']>$last_list['buy_num']?1:0;
		
		$res['buy_money'] = $list['buy_money'];
		$res['buy_money_percen'] = sprintf("%.2f",($list['buy_money']-$last_list['buy_money'])/$last_list['buy_money']*100);
		$res['buy_money_type'] = $list['buy_money']>$last_list['buy_money']?1:0;
		
		$res['ad_num'] = $list['ad_num'];
		$res['ad_num_percen'] = sprintf("%.2f",($list['ad_num']-$last_list['ad_num'])/$last_list['ad_num']*100);
		$res['ad_num_type'] = $list['ad_num']>$last_list['ad_num']?1:0;
		
		$res['ad_money'] = $list['ad_money'];
		$res['ad_money_percen'] = sprintf("%.2f",($list['ad_money']-$last_list['ad_money'])/$last_list['ad_money']*100);
		$res['ad_money_type'] = $list['ad_money']>$last_list['ad_money']?1:0;
		
		$res['coupon_money'] = $list['coupon_money'];
		$res['coupon_money_percen'] = sprintf("%.2f",($list['coupon_money']-$last_list['coupon_money'])/$last_list['coupon_money']*100);
		$res['coupon_money_type'] = $list['coupon_money']>$last_list['coupon_money']?1:0;
		
		
		if($res){
            $this->apiReturn(1,['data' => $res]);
        }else{
            $this->apiReturn(3);
        }
	}
	//促销趋势
	public function promotion_trend(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();

		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
		$list = $this->sql->table('ylh_promotion_basic')->field('year,month,day,buy_num,buy_money,ad_money,coupon_money')->where('shop_id = '.I('post.shop_id').' and atime > "'.$day['startDay'].'" and terminal = "'.$terminal.'"')->order('atime asc')->select();
	
		$data['data'] = $list;
		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3);
        }
	}
	//促销列表
	public function promotion_list(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','dayType','terminal','now_page','page_num','pro_type');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$day = $this->_time_type(I('post.dayType'));
		$terminal = $this->_terminal_type(I('post.terminal'));
		
	
		
		$where['shop_id'] = I('post.shop_id');
		$where['atime'] = ['gt',$day['startDay']];
		$where['terminal'] = $terminal;
		
		//查询的促销类型 0为查询全部
		if(I('post.pro_type') != 0){
			$where['type'] = I('post.pro_type');
		}
		
		//查询总记录
		$res = $this->sql->table('ylh_promotion_effect')->field('COUNT(DISTINCT goods_name) as count')->where($where)->find();
		$res['nowPage'] = I('post.now_page');
		$res['pageNum'] = I('post.page_num');
		//分页
		$page = $this->_page($res);
		
		$list = $this->sql->table('ylh_promotion_effect')->field('goods_name,images,price,type,sum(total_money) as total_money,avg(user_money) as user_money,sum(buyer) as buyer,sum(new_buyer) as new_buyer')->where($where)->group('goods_name')->order('total_money desc')->limit($page['start'],$page['pageNum'])->select();
		
		foreach($list as $k => $v){
			$list[$k]['no'] = $page['start']+$k+1;
		}
		
		$data['page'] = $page;
		
		$data['data'] = $list;
		$data['sql'] = $this->sql->getlastsql();

		if($list){
            $this->apiReturn(1,['data' => $data]);
        }else{
            $this->apiReturn(3,['data' => $data]);
        }
	}
	
	//我的报表
	public function my_report(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$list = M('shop_analysis_report')->where('shop_id = "'.I('post.shop_id').'"')->order('atime desc')->select();
		
		if($list){
			
			foreach($list as $k => $v){
				$tmp = array();
				$fields = explode(',',$v['quota_fields']);
				
				foreach($fields as $va){
					$array = explode('|',$va);
					$tmp[] = $this->quota[$array[0]]['data'][$array[1]]['title'];
				}
				$list[$k]['fields'] = $tmp;
			}
			
			$res['data'] = $list;
			
			$this->apiReturn(1,['data' => $res]);
		}else{
			$this->apiReturn(0);
		}
	}
	//获取指标字段
	public function get_quota(){
		$this->need_param=array('openid','sign','shop_id');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$this->apiReturn(1,['data' => $this->quota]);
		
		
	}
	//添加我的报表
	public function add_my_report(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','time_type','quota_fields');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$data['shop_id'] = I('post.shop_id');
		$data['time_type'] = I('post.time_type');
		$data['quota_fields'] = I('post.quota_fields');
		$data['name'] = '我的报表';
		
		$res = M('shop_analysis_report')->add($data);
		if($res){
			$this->apiReturn(1,['data' => $res]);
		}else{
			$this->apiReturn(0);
		}
		
	}
	//获取数据报表
	public function get_report(){
		//频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign','shop_id','time_type','quota_fields');
        $this->_need_param();
        $this->_check_sign();
		$this->setting();
		
		$quota_fields = explode(',',I('post.quota_fields'));
		$time_type = I('post.time_type');

		foreach($quota_fields as $v){
			$array = explode('|',$v);
			$quota_data['ylh_'.$array[0].'_basic']['table'] = 'ylh_'.$array[0].'_basic';
			$quota_data['ylh_'.$array[0].'_basic']['fileds'][] = $array[1];
		}
		
		
		$start = date('Y-m-d',(time()-86400*$time_type));
		$end = date('Y-m-d',time());
	
		
		
		foreach($quota_data as $v){
			$fileds = implode(',',$v['fileds']);
			$fileds = 'year,month,day,'.$fileds;
			
			$res[$v['table']] = $this->sql->table($v['table'])->field($fileds)->where('atime >= "'.$start.'" and atime < "'.$end.'" and terminal="all" and shop_id = "'.I('post.shop_id').'"')->order('atime asc')->select();
			
		}
		$result = array();
		for($i = 0;$i<$time_type;$i++){
			
			$tmp = array();
			
			$data['fields'][$i][] = array('title'=>'年','field'=>'year');
			$data['fields'][$i][] = array('title'=>'月','field'=>'month');
			$data['fields'][$i][] = array('title'=>'日','field'=>'day');
			
			foreach($quota_fields as $v){
				$array = explode('|',$v);
				if(empty($res['ylh_'.$array[0].'_basic'][$i]['year'])){
					continue;
				}
				$tmp['year'] = $res['ylh_'.$array[0].'_basic'][$i]['year'];
				$tmp['month'] = $res['ylh_'.$array[0].'_basic'][$i]['month'];
				$tmp['day'] = $res['ylh_'.$array[0].'_basic'][$i]['day'];
				$tmp[$array[0].'_'.$array[1]] = $res['ylh_'.$array[0].'_basic'][$i][$array[1]];
				
				$tmp2['title'] = $this->quota[$array[0]]['data'][$array[1]]['title'];
				$tmp2['field'] = $array[0].'_'.$array[1];
				$data['fields'][$i][] = $tmp2;
			}
			if(!empty($tmp)){
				$result[] = $tmp;
			}
			
		}
		$data['data'] = $result;
		$data['fields'] = $data['fields'][0];

		if(!empty($result)){
			$this->apiReturn(1,['data' => $data]);
		}else{
			$this->apiReturn(3);
		}
		
	}
	//终端类型
	public function _terminal_type($type=1){
		if($type == 2){
			$terminal = 'pc';
		}else if($type == 3){
			$terminal = 'wap';
		}else{
			$terminal = 'all';
		}
		return $terminal;
	}
	//时间类型
	public function _time_type($type=1){
		//查询日期
		if($type == 2){
			$startDay = date('Y-m-d',(time() - 86400*7));
		}else if($type == 3){
			$startDay = date('Y-m-d',(time() - 86400*30));
		}else if($type == 4){
			$startDay = date('Y-m-d',(time() - 86400*90));
		}else{
			$startDay = date('Y-m-d',(time()));
		}
		//上一个周期
		if($type == 2){
			$lastDay = date('Y-m-d',(strtotime($startDay) - 86400*7));
		}else if($type == 3){
			$lastDay = date('Y-m-d',(strtotime($startDay) - 86400*30));
		}else if($type == 4){
			$lastDay = date('Y-m-d',(strtotime($startDay) - 86400*90));
		}else{
			$lastDay = date('Y-m-d',(strtotime($startDay) - 86400));
		}
		
		
		$res['startDay'] = $startDay;
		$res['lastDay'] = $lastDay;
		return $res;
		
	}
	//获取来源
	public function _getSource($url){
		
		//广告来源
		$rule  = "/^(.*)click.".C('DOMAIN')."(.*)$/";  
		preg_match($rule,$url,$result);
		if($result){
			return 'ad';exit();
		}
		//站内搜索来源
		$rule  = "/^(.*)s.".C('DOMAIN')."(.*)$/";  
		preg_match($rule,$url,$result);
		if($result){
			return 'search';exit();
		}
		
		//商品收藏来源
		$rule  = "/^(.*)my.".C('DOMAIN')."/fav(.*)$/";  
		preg_match($rule,$url,$result);
		if($result){
			return 'goods_fav';exit();
		}
		//店铺收藏来源
		$rule  = "/^(.*)my.".C('DOMAIN')."/favshop(.*)$/";  
		preg_match($rule,$url,$result);
		if($result){
			return 'shop_fav';exit();
		}
		
		//店内跳转来源
		$rule  = "/^(.*).".C('DOMAIN')."(.*)$/";  
		preg_match($rule,$url,$result);
		if($result){
			return 'shop';exit();
		}
		return 'other';
	}
	//获取当前页面链接
	function _curPageURL($server){
		$pageURL = 'http';

		if ($server["HTTPS"] == "on") 
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";

		if ($server["SERVER_PORT"] != "80") 
		{
			$pageURL .= $server["SERVER_NAME"] . ":" . $server["SERVER_PORT"] . $server["REQUEST_URI"];
		} 
		else 
		{
			$pageURL .= $server["SERVER_NAME"] . $server["REQUEST_URI"];
		}
		$pageURL = str_replace('.html','',$pageURL);
		return $pageURL;
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
	//$page['nowPage'] 当前页
	//$page['count'] 数据总条数
	//$page['pageNum'] 每页显示条数
	//$page['pageShow'] 显示页面数量
	public function _page($page){
		if(!isset($page['nowPage'])){return false;}
		if(!isset($page['count'])){return false;}
		if(!isset($page['pageNum'])){$page['pageNum'] = 5;}
		if(!isset($page['pageShow'])){$page['pageShow'] = 5;}
		
		$page['start'] = ($page['nowPage']-1)*$page['pageNum'];
		
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