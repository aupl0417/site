<?php
namespace Rest\Controller;
use Think\Model\MongoModel as Mongo;
use Think\Page;

# 统计
class TjController extends CommonController
# class TjController
{

	# 统计类型
	const ad_visit 	= 1;
	const ad_show 	= 2;
	const goods_visit = 3;

	private $pageVisitData;

	private function dbConfig(){
		return C('DB_MONGO_CONFIG');
	}

	private function dbTable($default = 'tongji'){
		return C('DB_MONGO_CONFIG.DB_PREFIX') . $default;
	}

	public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
	 * PC 记录获取广告数,广告展示
	 */
	public function ad_show(){
		$this->need_param = ['ads','ip','key','device','user'];

		$this->_need_param();
		$this->_check_sign();

		$ads 	= json_decode(I('post.ads'), true);
		$ip 	= I('post.ip');
		# $ip 	= '116.23.126.105';

		$device = I('post.device');
		$user 	= json_decode($_POST['user'], true);

		$data 	= [];

		foreach ($ads as $key => $value) {
			$data[$value] = $ip;
		}
		$key 	= I('post.key');
		$data['date'] 	= date('Y-m-d');
		$data['type'] 	= self::ad_show;
		$data['device'] = $device;
		$data['time'] 	= time();
		$data['key']	= $key;
		$data['user']	= $user;
		$data['area'] 	= $this->GetIpLookup($ip);

		$Mongo 	= new Mongo($this->dbTable(), null, $this->dbConfig());
		$result = $Mongo->add($data);
		# $this->apiReturn(1);
	}

    /**
	 * 商品记录访问
	 */
	public function ad_visit(){
		$this->need_param = ['aid','ip','user','server','device','key'];
		if($_POST['device'] == 'wap') $this->need_param[] = 'attr_id';

		$this->_need_param();
		$this->_check_sign();

		$aid 	= I('post.aid');
		$ip 	= I('post.ip');
		# $ip 	= '116.23.126.105';
		$user 	= json_decode($_POST['user'], true);
		$server = json_decode($_POST['server'], true);
		$device = I('post.device');
		$key 	= I('post.key');

		# 现在只记录商品访问
		if($_POST['device'] == 'wap'){
			$attr_id = I('post.attr_id');
			$goods_id = M('goods_attr_list')->cache(true)->where(['id' => $attr_id])->getField('goods_id');
			$where = 'goods_id="' . $goods_id . '" and find_in_set("' . date('Y-m-d') . '",days)';
			$ad = M('ad')->cache(true)->where($where)->find();
			if($ad['id']){
				$data = [];
				$data['is_login'] 	= empty($user) ? 0 : 1;
				$data['user']		= $user;
				$data['sub']		= explode('.', $server['HTTP_HOST'])[0];
				$data['ref'] 		= $server['HTTP_REFERER'];
				$data['we_ref_sub']	= self::parseRefererUrl($data['ref']);
				$data['is_we_ref'] 	= is_null($data['we_ref_sub']) ? false : true;
				$data['date'] 		= date('Y-m-d');
				$data['datetime']	= date('Y-m-d H:i:s');
				$data['time']		= time();
				$data['ip']			= $ip;
				$data['area']		= $this->GetIpLookup($data['ip']);
				$data['type'] 		= self::ad_visit;
				$data['key'] 		= I('post.key');
				$data['device'] 	= $device;
				$data['server']		= $server;
				$data['gid']		= $goods_id;
				$data['g_attr_id']  = $attr_id;
				$data['ad']			= $ad;
				$data['seller_id']	= $ad['uid'];
				$data['position'] 	= M('ad_position')->cache(true)->find($ad['position_id']);
				$data['sucai']		= M('ad_sucai')->cache(true,86400)->find($ad['sucai_id']);

				$model = new Mongo($this->dbTable(), null, $this->dbConfig());
				$model->add($data);
			}
		}else if($_POST['device'] == 'pc'){
			$ad = M('ad')->cache(true)->find($aid);
			if($ad['goods_id'] > 0 || $ad['shop_id'] > 0){
				$data = [];
				$data['is_login'] 	= empty($user) ? 0 : 1;
				$data['user']		= $user;
				$data['sub']		= explode('.', $server['HTTP_HOST'])[0];
				$data['ref'] 		= $server['HTTP_REFERER'];
				$data['we_ref_sub']	= self::parseRefererUrl($data['ref']);
				$data['is_we_ref'] 	= is_null($data['we_ref_sub']) ? false : true;
				$data['date'] 		= date('Y-m-d');
				$data['datetime']	= date('Y-m-d H:i:s');
				$data['time']		= time();
				$data['ip']			= $ip;
				$data['area']		= $this->GetIpLookup($data['ip']);
				$data['type'] 		= self::ad_visit;
				$data['key'] 		= I('post.key');
				$data['device'] 	= $device;
				$data['server']		= $server;
				$data['gid'] 		= $ad['goods_id'];
				$data['g_attr_id'] 	= explode('.',end(explode('/', $ad['url'])))[0];
				$data['ad']			= $ad;
				$data['seller_id']	= $ad['uid'];
				$data['position'] 	= M('ad_position')->cache(true)->find($ad['position_id']);
				$data['sucai']		= M('ad_sucai')->cache(true,86400)->find($ad['sucai_id']);

				$model = new Mongo($this->dbTable(), null, $this->dbConfig());
				$model->add($data);
			}
		}
	}


	/**
	 * 上级链接解析出是否本站二级域名
	 * @return string | null
	 */
	static public function parseRefererUrl($url){
		# $rules = C('APP_SUB_DOMAIN_RULES');
		# $domain = C('DOMAIN');
		$parse_host = parse_url($url, PHP_URL_HOST);
		# var_dump(explode($domain, $parse_host));exit;
		$ex_host = explode(trim(C('DOMAIN'), '.'), $parse_host);
		# var_dump($ex_host);exit;
		# $sub = isset($ex_host[1]) ? (empty($ex_host[0]) ? '' : trim($ex_host[0],'.')) : null;
		return isset($ex_host[1]) ? (empty($ex_host[0]) ? '' : trim($ex_host[0],'.')) : null;
		# $sub = is_null($parse_host) ? null : explode('.', $parse_host)[0];
		# is_null($host) or $sub = explode('.', $host)[0];
		# $sub = ($host == null) ? null : explode('.', $host)[0];
		# if(strpos('.', $host) !== false){
		# 	echo 222;
		# 	$sub = explode('.', $host)[0];
		# }
		# $preg = '/^http[s]?:\/\/\w+$/';

		# if(preg_match($preg, $prefix)){
		# 	$sub = explode('//', $prefix)[1];
		# }
		# var_dump($parse_host);exit;
		# return (isset($rules[$sub]) && strpos($parse_host, $sub . '.' . $domain) === 0) ? $sub : null;
	}




	/**
	 * 统计每天的广告记录
	 * @param string $openid 统计哪个卖家的
	 * @param date $date Y-m-d 哪一天
	 */
	public function adCountDay(){

		set_time_limit(0);
		$this->need_param = ['openid'];
		if(isset($_POST['date'])) $this->need_param[] = 'date';
		$this->_need_param();
		$this->_check_sign();

        //判断用户是否开店
        if($this->user['shop_id'] == 0) return;

		$date = I('date',date('Y-m-d',strtotime('-1 day')));
		$one = M('ad_tj_ads')->where(['date'=>date('Ymd', strtotime($date)), 'uid' => $this->uid])->field('id')->find();
		if(isset($one['id'])){
			$this->apiReturn(0, ['msg' => "uid:" . $this->uid . " $date:已统计"]);
		}

		$Mongo = new Mongo($this->dbTable(), null, $this->dbConfig());
		# 广告点击记录
		$visit = objectToArray($Mongo->where(['date' => $date,'type' => self::ad_visit, 'seller_id' => $this->uid])->select());

		# 广告获取记录
		$show = objectToArray($Mongo->where(['date'=>$date,'type'=>self::ad_show])->select());
		
		$adShow = [];
		foreach($show as $value){
			foreach($value as $k => $v){
				if((int) $k > 0){
					$adShow[$k]['ip'][] 	= $v;
					$adShow[$k]['area'][] 	= $value['area']['province'];
				}
			}
		}
		# print_r($adShow);exit;

		$userKey = $this->userKey($visit);
		$keyUser = $this->keyUser($visit);
		# print_r($keyUser);exit;

		$tj_ads 	= [];# 广告统计
		$tj_sucai 	= [];# 素材统计
		$tj_goods 	= [];# 商品统计
		$tj_area 	= [];# 地区统计

		$_ad_goods = [];# 广告中的商品
		$_ad_shops = [];# 广告中的店铺
		$click_ads = [];# 所有广告点击记录

		# 广告展示地区

		foreach($adShow as $aid => $value){
			$seller_id = M('ad')->where(['id'=>$aid])->getField('uid');
			$i = 0;
			foreach($value['ip'] as $ip){
				$area = $adShow[$aid]['area'][$i];
				$tj_area[$seller_id][$area]['uid'] 			= $seller_id;
				$tj_area[$seller_id][$area]['show'] 		+= 1;
				$tj_area[$seller_id][$area]['hit'] 			= 0;
				$tj_area[$seller_id][$area]['orders'] 		= 0;
				$tj_area[$seller_id][$area]['orders_price'] = 0;
				$i++;
			}
			unset($i);
		}
		# print_r($tj_area);exit;
		$ads = [];
		foreach ($visit as $value) {
			$device = $value['device'];
			$aid 	= $value['ad']['id'];
			$click_ads[$device][$aid]['time'][] 	= $value['time'];
			$click_ads[$device][$aid]['key'][]  	= $value['key'];
			$click_ads[$device][$aid]['ip'][] 		= $value['ip'];
			$click_ads[$device][$aid]['area'][]		= $value['area']['province'] ? $value['area']['province'] : null;
			# 访客数
			if($value['is_login'] && ! in_array($value['user']['id'], $click_ads[$device][$aid]['user'])){
				$click_ads[$device][$aid]['user'][] = $value['user']['id'];
			}
			# 商品统计浏览
			# $attr_id = (int) end(explode('/', rtrim($value['server']['REDIRECT_URL'], '.html')));
			$attr_id = $value['g_attr_id'];
			$tj_goods[$attr_id]['show'] = (int) $tj_goods[$attr_id]['show'] + 1;
			# echo $attr_id,',',$tj_goods[$attr_id]['show'],':';
			$tj_goods[$attr_id]['uid'] = M('goods_attr_list')->where(['id'=>$attr_id])->limit(1)->getField('seller_id');
			$tj_goods[$attr_id]['orders'] = 0;
			$tj_goods[$attr_id]['orders_price'] = 0;
			$tj_goods[$attr_id]['goods_id'] = $value['goods_id'];
			# parse_str(explode('?', $value['ref'])[1],$parse);# 解析出url后面参数
			$ads[] = $aid;
		}
		unset($click_ads[0]);

		# print_r($click_ads);exit;
		! $ads or $ads = M('ad')->where(['id' => ['in', array_unique($ads)]])->select();

		# 广告素材统计
		# print_r($ads);exit;
		$sucaiShow = [];
		foreach($ads as $key => $value){
			$sucaiShow[$value['sucai_id']] += (int) count($adShow[$value['position_id']]['ip']);
		}
		# print_r($sucaiShow);exit;
		foreach($click_ads as $key => $value){
			foreach($value as $k => $v){
				$ad = M('ad')->cache(true)->find($k);
				$scid = $ad['sucai_id'];
				$click_ads[$key][$ad['id']]['sucai_id'] 	= $scid;
				# var_dump($ad);exit;
				$tj_sucai[$scid]['uid'] 			= (int) $ad['uid'];
				$tj_sucai[$scid]['aid'] 			= (int) $k;
				$tj_sucai[$scid]['sucai_id'] 		= (int) $scid;
				$tj_sucai[$scid]['goods_id'] 		= (int) $ad['goods_id'];
				$tj_sucai[$scid]['shop_id'] 		= (int) $ad['shop_id'];
				$tj_sucai[$scid]['date'] 			= $date;
				$tj_sucai[$scid]['show'] 			= (int) $sucaiShow[$scid];
				$tj_sucai[$scid]['hit'] 			= count($v['time']);
				$tj_sucai[$scid]['orders']			= 0;
				$tj_sucai[$scid]['orders_price']	= 0;
				$tj_sucai[$scid]['position_id'] 	= (int) $ad['position_id'];

				foreach($v['area'] as $v1){
					$tj_area[$uid][$v1]['hit'] += 1;
				}
			}
		}
		# print_r($tj_sucai);exit;

		foreach($ads as $value){
			if(isset($click_ads['pc'][$value['id']])){
				$device = 'pc';
			}else if(isset($click_ads['wap'][$value['id']])){
				$device = 'wap';
			}else{
				$device = '';
			}

			$tj_ads[$device][$value['uid']]['uid'] = $value['uid'];

			$tj_ads[$device][$value['uid']]['show'] += count($adShow[$value['position_id']]['ip']);

			# 用户的广告点击量等

			$hit = count($click_ads[$device][$value['id']]['time']);
			$v_num = count($click_ads[$device][$value['id']]['user']);

			$tj_ads[$device][$value['uid']]['hit'] 				= $hit;
			$tj_ads[$device][$value['uid']]['orders'] 			= 0;
			$tj_ads[$device][$value['uid']]['orders_price'] 	= 0;
			$tj_ads[$device][$value['uid']]['visit_user_num'] 	= $v_num;
			$tj_ads[$device][$value['uid']]['order_user_num'] 	= 0;
			$tj_ads[$device][$value['uid']]['date'] 			= $date;
			$tj_ads[$device][$value['uid']]['device']			= $device;
			$aid = (int) $value['id'];
			$gid = (int) $value['goods_id'];
			$sid = (int) $value['shop_id'];
			if($gid) $_ad_goods[$aid] = $gid;
			if($sid) $_ad_shops[$aid] = $sid;
		}
		# 查询今日订单和广告中的订单
		$ordersMap['atime'] = ['between', [date('Y-m-d H:i:s',strtotime($date)), date('Y-m-d H:i:s', strtotime($date) + 24 * 3600 - 1)]];

		$todayOrders = M('orders_goods')->where($ordersMap)->select();
		# print_r($tj_ads);exit;
		$ad_orders = [];
		foreach($todayOrders as $value){
			if(in_array($value['goods_id'], $_ad_goods) || in_array($value['shop_id'], $_ad_shops)){
				$ad_orders[] = $value;
			}
		}
		# 统计下单量和金额等
		foreach($ad_orders as $value){
			$aid = array_search($value['goods_id'], $_ad_goods);
			if(isset($click_ads['pc'][$aid])){
				$device = 'pc';
			}else if(isset($click_ads['wap'][$aid])){
				$device = 'wap';
			}else{
				$device = '';
			}
			# var_dump($value['uid']);
			# print_r($aid);exit;
			$aid or $aid = array_search($value['shop_id'], $_ad_shops);
			$atime = date('Y-m-d', strtotime($value['atime']));
			if($atime != $date){
				continue;
			}

			# echo $value['goods_id'],',';
			# print_r($_ad_goods);exit;

			if(isset($click_ads['pc'][$aid]) || isset($click_ads['wap'][$aid])){

				# echo $device;echo $aid;
				# 下单之前客户是否点过广告
				$uKey = $userKey[$value['uid']];
				# print_r($click_ads);exit;
				$minTime = $minKey = null;
				foreach($click_ads[$device][$aid]['time'] as $k => $v){
					# echo 1,',';
					if(in_array($click_ads[$device][$aid]['key'][$k], $uKey)){
						if($minTime){
							if($minTime >= $v){
								$minTime = $v;
							}
						}else{
							$minTime = $v;
						}
					}
				}
				# 计入下单量和金额
				$o_time = strtotime($value['atime']);
				if($minTime != null && $minTime < $o_time){
					# 广告下单数
					$tj_ads[$device][$value['seller_id']]['orders'] += 1;
					$tj_ads[$device][$value['seller_id']]['orders_price'] += $value['total_price'];
					# 记录下单客户数
					$order_user[$value['seller_id']][] = $value['uid'];
					# 商品下单数
					$tj_goods[$value['attr_list_id']]['uid'] = $value['seller_id'];
					$tj_goods[$value['attr_list_id']]['orders'] += 1;
					$tj_goods[$value['attr_list_id']]['orders_price'] += $value['total_price'];
					$tj_goods[$value['attr_list_id']]['goods_id'] = $value['goods_id'];
					# 素材下单数
					$scid = $click_ads[$device][$aid]['sucai_id'];
					$tj_sucai[$scid]['orders'] += 1;
					$tj_sucai[$scid]['orders_price'] += $value['total_price'];
					# 地区下单数
					$area = $this->GetIpLookup($value['id'])['province'];
					$tj_area[$value['seller_id']][$area]['orders'] += 1;
					$tj_area[$value['seller_id']][$area]['orders_price'] += $value['total_price'];
				}
			}
		}

		# 记录下单客户数

		foreach($tj_ads as $key => $value){
			foreach($value as $k => $v){
				$tj_ads[$key][$k]['order_user_num'] = count(array_unique($order_user[$k]));
			}
		}
		$tj_ads_n = $tj_ads;$tj_ads = [];
		foreach($tj_ads_n as $key => $value){
			foreach ($value as $k => $v) {
				$tj_ads[] = $v;
			}
		}

		# print_r($tj_goods);exit;
		M('ad_tj_ads')->addAll($tj_ads);

		# 商品浏览、下单、下单金额数据
		foreach($tj_goods as $key => $value){
			$tj_goods[$key]['attr_id'] = $key;
			$tj_goods[$key]['show'] = $tj_goods[$key]['show'];
			$tj_goods[$key]['goods_id'] = M('goods_attr_list')->where(['id'=>$key])->getField('goods_id');
		}
		# print_r($tj_goods);exit;
		foreach(array_values($tj_goods) as $key => $value){
			$where = ['attr_id'=>$value['attr_id']];
			$one = M('ad_tj_goods')->where($where)->find();

			if(isset($one['id'])){
				$data = [
					'orders'		=> $value['orders'] + $one['orders'],
					'orders_price' 	=> $value['orders_price'] + $one['orders_price'],
					'show' 			=> $value['show'] + $one['show'],
				];
				M('ad_tj_goods')->where(['id'=>$one['id']])->data($data)->save();
			}else{
				# print_r($value);exit;
				M('ad_tj_goods')->add($value);
			}
		}
		# print_r($tj_sucai);exit;
		$result = M('ad_tj_sucai')->addAll(array_values($tj_sucai));

		foreach ($tj_area as $key => $value){
			$one = M('ad_tj_area')->where(['uid'=>$key])->find();
			if($one['id']){
				$data = array(
					'show' 			=> $value['show'] + $one['show'],
					'hit' 			=> $value['hit'] + $one['hit'],
					'orders' 		=> $value['orders'] + $one['orders'],
					'orders_price' 	=> $value['orders_price'] + $one['orders_price'],
				);
				M('ad_tj_area')->where(['id'=>$one['id']])->data($data)->save();
			}else{
				M('ad_tj_area')->add($value);
			}
		}

		# print_r($result);exit;
		$tj_ads['uid']=$this->uid;
		$this->apiReturn(1,['tj_sucai' => $tj_sucai,'tj_goods' => $tj_goods,'tj_ads' => $tj_ads,'tj_area' => $tj_area]);

	}

	private function keyUser($data){
		$result;
		array_walk($data, function($value, $key) use (&$result) {
			$sKey = $value['key'];
			isset($result[$sKey]) or $result[$sKey] = null;
			$user = $value['user']['id'];
			is_null($user) or $result[$sKey] = $user;
		});
		return $result;
	}

	private function userKey($data){
		$keyUser = $this->keyUser($data);
		$result;
		array_walk($keyUser, function($user, $key) use (&$result) {
			isset($result[$user]) or $result[$user] = [];
			is_null($user) or (in_array($result[$user], $key) or $result[$user][] = $key);
		});
		return $result;
	}

	public function test(){
		set_time_limit(0);

		$where['shop_id'] = "160";
		# echo C('DB_MONGO_CONFIG.DB_PREFIX') . 'tongji_shop';
		$Mongo = new Mongo(C('DB_MONGO_CONFIG.DB_PREFIX') . 'tongji_shop', null, self::dbConfig());
		# $where = ['date' => '2016-09-29','type' => self::page_visit];
		# $where = ['date' => $date, 'we_ref_sub' => 'click', 'type' => self::page_visit];

		$data = $Mongo->where($where)->field('shop_id')->select();
		# $this->apiReturn(3,['msg'=>'无浏览记录']);
		print_r($data);exit;
	}

	/**
	 * 是否允许的二级域名统计
	 */
	static public function isAllow(){
		$allow_sub_host = ['www','item'];
		$parse_host = trim(strtolower(parse_url($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST)), '.');
		$sub = explode('.', $parse_host)[0];
		$domain = trim(strtolower(C('DOMAIN')), '.');
		return (in_array($sub, $allow_sub_host) || $parse_host === $domain);
	}

	/**
	 * 获取IP所在城市
	 */
	private function GetIpLookup($ip = ''){
		# $ip or $ip = get_client_ip();
		$res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip);
		$res = json_decode($res, true);
		if(isset($res['ret']) && $res['ret'] == 1){
			# return $res['province'];
			return array(
				'country' 	=> $res['country'],
				'province' 	=> $res['province'],
				'city' 		=> $res['city'],
				'district' 	=> $res['district'],
			);
		}else{
			return null;
		}
	}


	/**
	 * 广告 根据日期获取统计数据
	 * @param int 		$uid  			用户id
	 */
	public function getAdtjads(){
		# $this->apiReturn(0);
		$this->need_param = ['openid'];
		if(isset($_POST['isDiffer'])) $this->need_param[] = 'isDiffer';# 是否区分pc和wap
		$this->_need_param();
		$this->_check_sign();

		$isDiffer = I('isDiffer', 1, 'intval');
		$where['uid'] = $this->uid;
		if(in_array(I('post.device'),['wap','pc'])) $where['device'] = strtolower(I('post.device'));
		$pagesize = I('post.pagesize')?I('post.pagesize'):2;

		$list = pagelist(array(
            'table'     => 'ad_tj_ads',
            'do'        => 'M',
            'map'       => $where,
            'order'     => 'id desc',
            'fields'    => '*',
            'pagesize'  => $isDiffer ? $pagesize : $pagesize * 2,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));

        if($list){
        	if(! $isDiffer){
        		$list_new = [];
        		foreach ($list as $key => $value) {
        			if(isset($list_new[$value['date']])){
        				$list_new[$value['date']]['show'] 			+= $value['show'];
        				$list_new[$value['date']]['hit'] 			+= $value['hit'];
        				$list_new[$value['date']]['orders'] 		+= $value['orders'];
        				$list_new[$value['date']]['orders_price'] 	+= $value['orders_price'];
        				$list_new[$value['date']]['visit_user_num'] += $value['visit_user_num'];
        				$list_new[$value['date']]['order_user_num'] += $value['order_user_num'];
        			}else{
        				$list_new[$value['date']] = $value;
        			}
        		}
        		$list = array_values($list_new);
        	}
        	$this->apiReturn(1, ['data' => $list]);
        }else{
        	$this->apiReturn(3);
        }


	}
	
	/**
	 * 新广告统计数据
	 * @param	string	$date		日期
	 * @param	string	$device		平台（pc or wap）
	 * @param	string	$group		分组字段
	 * @param	string	$order		排序字段
	 * @param	string	$join		JOIN信息多个用|分割
	 * @param	string	$field		需要的字段
	 * @param	string	$_string	字符串条件
	 * @param	integer	$limit		查询长度
	 * @param	string	$maction	分页Action
	 * @param	integer	$cp			当前页		
	 */
	public function adstatistical(){
		$this->need_param = ['date','device','group','order','join','field','_string','limit','maction','cp','openid'];
		$this->_need_param();
		$this->_check_sign();
		
		$device=I('post.device',0);//平台pc,wap
		$date=I('post.date',2);//查询统计日期
		$inDate=array();$where=array();
		$where['a.uid']=$this->uid;
		$order=I('post.order','a.date DESC');
		$group=I('post.group');
		$join=I('post.join');
		$field=I('post.field');
		$_string=I('post._string');
		$limit=I('post.limit',2);
		$limit2=$limit;
		$action=I('post.maction');
		$p=I('post.cp',1);
		if ($date){
			for ($i=1;$i<=$date;$i++){
				$inDate[]=date('Y-m-d',strtotime('-'.$i.' day'));
			}
			$where['a.date']=array('in',$inDate);
		}
		if ($device){
			$where['a.device']=$device;
		}
		if ($_string){
			$where['_string']=htmlspecialchars_decode($_string);
		}
		if ($limit==-1){
			$totalRows=M('ad_tj_ads a')->where($where)->count('DISTINCT a.aid');
			$page=new Page($totalRows,5,'','',$action,$p);
			$limit=$page->firstRow.','.$page->listRows;
		}
		if ($group){
			$arr=explode('|', $join);
			if ($join){
				if (count($arr)==2){
					$result=M('ad_tj_ads a')->join($arr[0])->join($arr[1])->field('sum(a.`show`) as `show`,sum(a.`hit`) as `hit`,sum(a.`orders`) as `orders`,sum(a.`orders_price`) as `orders_price`,sum(a.`visit_user_num`) as `visit_user_num`,sum(a.`order_user_num`) as `order_user_num`,a.`date`,a.`device`'.$field)->where($where)->order($order)->group($group)->limit($limit)->select();
				}else {
					$result=M('ad_tj_ads a')->join($join)->field('sum(a.`show`) as `show`,sum(a.`hit`) as `hit`,sum(a.`orders`) as `orders`,sum(a.`orders_price`) as `orders_price`,sum(a.`visit_user_num`) as `visit_user_num`,sum(a.`order_user_num`) as `order_user_num`,a.`date`,a.`device`'.$field)->where($where)->order($order)->group($group)->limit($limit)->select();
				}
			}else {
				$result=M('ad_tj_ads a')->field('sum(a.`show`) as `show`,sum(a.`hit`) as `hit`,sum(a.`orders`) as `orders`,sum(a.`orders_price`) as `orders_price`,sum(a.`visit_user_num`) as `visit_user_num`,sum(a.`order_user_num`) as `order_user_num`,a.`date`,a.`device`'.$field)->where($where)->order($order)->group($group)->limit($limit)->select();
			}
		}else {
			if ($join){
				$result=M('ad_tj_ads a')->join($join)->field('a.show,a.hit,a.orders,a.orders_price,a.visit_user_num,a.order_user_num,a.date,a.device'.$field)->where($where)->order($order)->limit($limit)->select();
			}else {
				$result=M('ad_tj_ads a')->field('a.show,a.hit,a.orders,a.orders_price,a.visit_user_num,a.order_user_num,a.date,a.device'.$field)->where($where)->order($order)->limit($limit)->select();
			}
		}
		if ($result){
			if ($limit2==-1){
				$this->apiReturn(1, ['data' => ['list'=>$result,'page'=>$page->show_btn()]]);
			}else {
				$this->apiReturn(1, ['data' => $result]);
			}
		}else {
			$this->apiReturn(3);
		}
	}
	
	/**
	 * 截止到某个时间的统计数据
	 */
	public function adstatisticalByaid(){
		$this->need_param = ['aid','openid'];
		$this->_need_param();
		$this->_check_sign();
	
		$aid=I('post.aid',0);
		$where=array();
		$where['uid']=$this->uid;
		$where['date']=array('elt',date('Y-m-d',strtotime('-1 day')));
		$where['aid']=$aid;
		$result=M('ad_tj_ads')->field('sum(`show`) `show`,sum(hit) hit,sum(orders) orders,sum(orders_price) orders_price,sum(visit_user_num) visit_user_num,sum(order_user_num) order_user_num,date,device')->where($where)->order('date DESC')->group('aid')->find();
		if ($result){
			$this->apiReturn(1, ['data' => $result]);
		}else {
			$this->apiReturn(3);
		}
	}
	
	/**
	 * 新广告统计(单个广告所有数据)
	 */
	public function adstbByaid(){
		$this->need_param = ['aid','openid'];
		$this->_need_param();
		$this->_check_sign();
	
		$aid=I('post.aid',0);
		$where=array();
		$where['uid']=$this->uid;
		$where['date']=array('elt',date('Y-m-d',strtotime('-1 day')));
		$where['aid']=$aid;
		$result=M('ad_tj_ads')->field('sum(`show`) `show`,sum(hit) hit,sum(orders) orders,sum(orders_price) orders_price,sum(visit_user_num) visit_user_num,sum(order_user_num) order_user_num,date,device')->where($where)->order('date ASC')->group('date')->select();
		if ($result){
			$this->apiReturn(1, ['data' => $result]);
		}else {
			$this->apiReturn(3);
		}
	}
	
	/**
	 * 获取广告访问路径
	 * @param	string		$aid		广告ID
	 * @param	string		$device		平台
	 * @param	date		$date		日期
	 * @param	integer		$pagesiec	每页条数
	 * @param	integer		$cpage		当前页
	 * @param	string		$group		分组字段
	 * @param	string		$maction	分页Action
	 * @param	string		$is_ajax	是否返回Ajax分页HTML
	 */
	public function adsShowPath(){
		$this->need_param = ['aid','device','date','pagesizec','cpage','group','maction','is_ajax','openid'];
		$this->_need_param();
		$this->_check_sign();
		
		$aid=I('post.aid',0);
		$device=I('post.device');
		$date=I('post.date');
		$pagesize=I('post.pagesizec',3);
		$cpage=I('post.cpage',1);
		$group=I('post.group','a.uid');
		$maction=I('post.maction');
		
		$where['a.aid']=['in',$aid];
		$where['a.uid']=['gt',0];
		if ($device){
			$where['a.device']=$device;
		}if ($date){
			$date=strtotime($date);
			$where['a.time']=['between',[$date,$date+86400]];//strtotime($date);
		}
		$do=M('ad_sv_ads a');
		$totalRows=$do->where($where)->count('DISTINCT a.uid');
		$page=new Page($totalRows,$pagesize,['p'=>$cpage],'',$maction);
		$list=$do->cache(true)->field('a.device,a.ip,a.uid,a.time,b.nick,a.aid,a.province,a.city,c.name,c.sort,c.type,c.images')->join('left join '.C('DB_PREFIX').'user b on a.uid=b.id')->join('left join '.C('DB_PREFIX').'ad c on a.aid=c.id')->where($where)->order('a.time DESC')->group($group)->limit($page->firstRow.','.$page->listRows)->select();
		if ($list){
			foreach ($list as $k=>$v){
				$list[$k]['nick']=$list[$k]['nick']?$list[$k]['nick']:'访客';
				$list[$k]['time']=date('Y-m-d H:i:s',$v['time']);
				if (!$v['province']&&!$v['city']){
					$area=$this->GetIpLookup($v['ip']);
					$list[$k]['province']=$area['province'];
					$list[$k]['city']=$area['city'];
				}
				$list[$k]['list']=$do->cache(true)->field('c.images,c.goods_name,a.type,a.time,d.shop_logo')->join('left join '.C('DB_PREFIX').'goods c on a.goods_id=c.id')->join('left join '.C('DB_PREFIX').'shop d on a.shop_id=d.id')->where(['a.aid'=>$v['aid'],'a.uid'=>$v['uid']])->order('a.time DESC')->group('a.type')->select();
				foreach ($list[$k]['list'] as $kk=>$vv){
					if ($vv['images'])
						$list[$k]['list'][$kk]['images']=myurl($vv['images'],100);
					if ($vv['shop_logo'])
						$list[$k]['list'][$kk]['shop_logo']=myurl($vv['shop_logo'],100);
					$list[$k]['list'][$kk]['time']=date('Y-m-d H:i:s',$vv['time']);
					switch ($vv['type']){
						case 1:
							$list[$k]['list'][$kk]['type']='点击';
							break;
						case 2:
							$list[$k]['list'][$kk]['type']='浏览';
							break;
						case 11:
							$list[$k]['list'][$kk]['type']='加入购物车';
							break;
						case 12:
							$list[$k]['list'][$kk]['type']='下单';
							break;
						default:
							$list[$k]['list'][$kk]['type']='浏览';
							break;
					}
				}
			}
			if (I('post.is_ajax',1)){
				$this->apiReturn(1,['data'=>['page'=>$page->show_ajax_btn(),'list'=>$list]]);
			}else {
				$this->apiReturn(1,['data'=>['page'=>$page->show_btn(),'list'=>$list]]);
			}
			
		}else {
			$this->apiReturn(3);
		}
	}



	/**
	 * 广告 根据日期获取素材点击
	 * @param int 		$openid 用户openid
	 */
	public function getAdtjsc(){
		$this->need_param = ['openid'];
		$this->_need_param();
		$this->_check_sign();

        $where['uid'] = $this->uid;
		$pagesize = I('post.pagesize')?I('post.pagesize'):2;

		$list = pagelist(array(
            'table'     => 'Common/AdtjscRelation',
            'do'        => 'D',
            'map'       => $where,
            'order'     => 'id desc',
            'fields'    => '*',
            'relation'	=> true,
            'pagesize'  => $pagesize,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));
        if($list){
        	$this->apiReturn(1, ['data' => $list, 'pagesize' => $pagesize]);
        }else{
        	$this->apiReturn(3);
        }
	}

	/**
	 * 素材Top
	 * @param string $openid 用户openid
	 * @param int $top Top几
	 */
	public function sucaiTop(){
		$this->need_param = ['openid','top'];
		$this->_need_param();
		$this->_check_sign();

		$where['uid'] = $this->uid;
		$top = I('top', 1, 'int');

		$list = pagelist(array(
            'table'     => 'ad_tj_sucai',
            'do'        => 'M',
            'map'       => $where,
            'order'     => 'orders desc',
            'fields'    => '*',
            'pagesize'  => $top,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));
		if($list){
        	$this->apiReturn(1, ['data' => $list]);
        }else{
        	$this->apiReturn(3);
        }
	}


	/**
	 * 商品Top
	 * @param string $openid
	 * @param int $top Top几
	 */
	public function goodsTop(){
		$this->need_param = ['openid','top'];
		$this->_need_param();
		$this->_check_sign();

		$where['uid'] = $this->uid;
		$top = I('top', 1, 'int');
		$list = pagelist(array(
            'table'     => 'Common/AdtjgoodsRelation',
            'do'        => 'D',
            'map'       => $where,
            'order'     => 'orders_price desc',
            'fields'    => '*',
            'pagesize'  => $top,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));

		if($list){
        	$this->apiReturn(1, ['data' => $list]);
        }else{
        	$this->apiReturn(3);
        }

	}

	/**
	 * 地区Top
	 */
	public function areaTop(){
		$this->need_param = ['openid','top'];
		$this->_need_param();
		$this->_check_sign();

		$where['uid'] = $this->uid;
		$top = I('top', 1, 'int');

		$list = pagelist(array(
            'table'     => 'ad_tj_area',
            'do'        => 'M',
            'map'       => $where,
            'order'     => '`orders_price` desc,orders desc',
            'fields'    => '*',
            'pagesize'  => $top,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));

		if($list){
			$this->apiReturn(1,['data' => $list]);
		}else{
			$this->apiReturn(3);
		}
	}

	/**
	 * 地区统计
	 */
	public function areaTj(){
		$this->need_param = ['openid'];
		$this->_need_param();
		$this->_check_sign();

		$where['uid'] = $this->uid;
		$list = pagelist(array(
            'table'     => 'ad_tj_area',
            'do'        => 'M',
            'map'       => $where,
            'order'     => '`orders_price` desc,orders desc',
            'fields'    => '*',
            # 'pagesize'  => $top,
            'action'    => I('post.action'),
            'query'     => I('post.query')?query_str_(I('post.query')):'',
            'p'         => I('post.p'),
            # 'cache_name'        => md5(implode(',',$_POST).__SELF__),
            # 'cache_time'        => C('CACHE_LEVEL.XXL'),
        ));
		if($list){
			$this->apiReturn(1, ['data' => $list]);
		}else{
			$this->apiReturn(3, ['data' => null]);
		}
	}

	/**
	 * 广告实时浏览统计
	 */
	public function adLive(){
		$this->need_param = ['openid'];
		$this->_need_param();
		$this->_check_sign();

		$uid = $this->uid;
		$Mongo = new Mongo(self::dbTable(), null, self::dbConfig());
		$where = ['date' => date('Y-m-d'),'type' => self::ad_visit,'seller_id'=> '686959'];

		$list = $Mongo->where($where)->order('datetime desc')->limit(100)->select();
		if($list){
			$this->apiReturn(1, ['data' => $list]);
		}else{
			$this->apiReturn(3);
		}
	}

	/**
	 * 获取Mongo广告访问数据存入Mysql
	 */
	public function adShowClickData(){
		set_time_limit(0);
		
		$this->_check_sign();
		
		$date = date('Y-m-d',strtotime('-1 day'));
		$one = M('ad_tj_ads')->where(['date'=>$date])->field('id')->find();
		if(isset($one['id'])){
			$this->apiReturn(0, ['msg' => $date.':已统计']);
		}
		
		$Mongo = new Mongo($this->dbTable(), null, $this->dbConfig());
		# 广告点击记录
		$visit = objectToArray($Mongo->where(['date' => $date,'type' => self::ad_visit])->select());
		
		# 广告浏览记录
		$show = objectToArray($Mongo->where(['date'=>$date,'type'=>self::ad_show])->select());
		$adShow = [];$position_ids=[];$tjads=[];
		foreach($show as $value){
			foreach($value as $k => $v){
				if((int) $k > 0){
					if ($value['device']=='pc'){
						$adShow['pc'][$k]['ip'][] 	= $v;
						$adShow['pc'][$k]['area'][] 	= $value['area']['province'];
						$adShow['pc'][$k]['device'] = $value['device'];
						$adShow['pc'][$k]['user'][]=$value['user']['id'];
						$adShow['pc'][$k]['time'][]=$value['time'];
						$adShow['pc'][$k]['type'][]=$value['type'];
					}elseif ($value['device']=='wap'){
						$adShow['wap'][$k]['ip'][] 	= $v;
						$adShow['wap'][$k]['area'][] 	= $value['area']['province'];
						$adShow['wap'][$k]['device'] = $value['device'];
						$adShow['wap'][$k]['user']=$value['user']['id'];
						$adShow['wap'][$k]['time'][]=$value['time'];
						$adShow['wap'][$k]['type'][]=$value['type'];
					}else {
						$adShow['Unknown'][$k]['ip'][] 	= $v;
						$adShow['Unknown'][$k]['area'][] 	= $value['area']['province'];
						$adShow['Unknown'][$k]['device'] = $value['device'];
						$adShow['Unknown'][$k]['user']=$value['user']['id'];
						$adShow['Unknown'][$k]['time'][]=$value['time'];
						$adShow['Unknown'][$k]['type'][]=$value['type'];
					}
					$position_ids[]=$k;
				}
			}
		}
		$position_ids=array_unique($position_ids);
		$adshowList=M('ad')->cache(3600)->field('id,uid,sucai_id,shop_id,goods_id,type,position_id')->where(['position_id'=>['in',$position_ids],'status'=>1,'_string'=>'FIND_IN_SET(\''.$date.'\',days)'])->select();
		foreach (['pc','wap','Unknown'] as $device){
			if ($adShow[$device]){
				foreach ($adshowList as $v){
					$tjads[]=[
							'uid'=>$v['uid'],
							'aid'=>$v['id'],
							'sucai_id'=>$v['sucai_id'],
							'goods_id'=>$v['goods_id'],
							'shop_id'=>$v['shop_id'],
							'show'=>count($adShow[$device][$v['position_id']]['ip']),
							'hit'=>0,
							'orders'=>0,
							'orders_price'=>0,
							'visit_user_num'=>0,
							'order_user_num'=>0,
							'date'=>$date,
							'device'=>$adShow[$device][$v['position_id']]['device']
					];
				}
			}
		}
		
		$userKey=[];
		foreach ($visit as $k=>$v){
			if ($v['user']){
				$userKey[$v['user']['id']]=$v['time'];
			}
			foreach ($tjads as $kk=>$vv){
				if ($v['device']==$tjads[$kk]['device']&&$v['ad']['id']==$tjads[$kk]['aid']){
					if (array_key_exists($v['user']['id'], $userKey)){
						$tjads[$kk]['visit_user_num']=$tjads[$kk]['visit_user_num']+1;
					}
					$tjads[$kk]['hit']=$tjads[$kk]['hit']+1;
					unset($visit[$k]);
				}
			}
		}
		$tjadsv=[];$svrecord=[];
		foreach ($visit as $v){
			$isSetV=true;
			foreach ($tjadsv as $kk=>$vv){
				if ($v['ad']['id']==$vv['aid']){
					$tjadsv[$kk]['hit']=$tjadsv[$kk]['hit']+1;
					if (array_key_exists($v['user']['id'], $userKey)){
						$tjadsv[$kk]['visit_user_num']=$tjadsv[$kk]['visit_user_num']+1;
					}
					$isSetV=false;
				}
			}
			if ($isSetV){
				$tjadsv[]=[
						'uid'=>$v['ad']['uid'],
						'aid'=>$v['ad']['id'],
						'sucai_id'=>$v['sucai']['id']?$v['sucai']['id']:0,
						'goods_id'=>$v['gid'],
						'shop_id'=>$v['ad']['shop_id'],
						'show'=>0,
						'hit'=>1,
						'orders'=>0,
						'orders_price'=>0,
						'visit_user_num'=>0,
						'order_user_num'=>0,
						'date'=>$date,
						'device'=>$v['device']
				];
			}
			$svrecord[]=[
					'position_id'=>$v['ad']['position_id'],
					'aid'=>$v['ad']['id'],
					'shop_id'=>$v['ad']['shop_id'],
					'goods_id'=>$v['ad']['goods_id'],
					'ip'=>$v['ip'],
					'uid'=>$v['user']['id']?$v['user']['id']:0,
					'type'=>$v['type'],
					'device'=>$v['device'],
					'time'=>$v['time']
			];
		}
		// 查询今日订单和广告中的订单
		$ordersMap['a.atime'] = ['between', [date('Y-m-d H:i:s',strtotime($date)), date('Y-m-d H:i:s', strtotime($date) + 24 * 3600 - 1)]];
		$ordersMap['b.status']=['gt',1];
		$todayOrders = M('orders_goods a')->join('left join '.C('DB_PREFIX').'orders b on a.o_id=b.id')->cache(3600)->field('a.atime,a.uid,a.goods_id,a.shop_id,a.total_price_edit')->where($ordersMap)->select();
		$allTjData=array_merge($tjads,$tjadsv);
		foreach ($allTjData as $k=>$v){
			foreach ($todayOrders as $kk=>$vv){
				if ($v['goods_id']==$vv['goods_id']){
					$allTjData[$k]['orders']=$allTjData[$k]['orders']+1;
					$allTjData[$k]['orders_price']=$allTjData[$k]['orders_price']+$vv['total_price_edit'];
					unset($todayOrders[$kk]);
				}
				if ($v['shop_id']==$vv['shop_id']){
					$allTjData[$k]['orders']=$allTjData[$k]['orders']+1;
					$allTjData[$k]['orders_price']=$allTjData[$k]['orders_price']+$vv['total_price_edit'];
					unset($todayOrders[$kk]);
				}
				if (array_key_exists($vv['uid'], $userKey)&&$userKey[$vv['uid']]<=$vv['atime']){
					$allTjData[$k]['order_user_num']=$allTjData[$k]['order_user_num']+1;
				}
			}
		}
		foreach ($adShow as $k=>$v){
			foreach ($v as $kk=>$vv){
				foreach ($vv['ip'] as $kkk=>$vvv){
					foreach ($adshowList as $asl){
						if ($asl['position_id']==$kk){
							$svrecord[]=[
									'position_id'=>$kk,
									'aid'=>$asl['id'],
									'shop_id'=>$asl['shop_id'],
									'goods_id'=>$asl['goods_id'],
									'ip'=>$vvv,
									'uid'=>$vv['user'][$kkk]?$vv['user'][$kkk]:0,
									'type'=>$vv['type'][$kkk],
									'device'=>$k,
									'time'=>$vv['time'][$kkk]
							];
						}
					}
				}
			}
		}
		// 查询今日购物车
		$cartsMap['atime'] = ['between', [date('Y-m-d H:i:s',strtotime($date)), date('Y-m-d H:i:s', strtotime($date) + 24 * 3600 - 1)]];
		$todayCarts = M('cart')->field('uid,ip,atime,goods_id')->where($cartsMap)->select();
		
		foreach ($svrecord as $k=>$v){
			foreach ($todayCarts as $vv){
				if ($vv['goods_id']==$v['goods_id']&&strtotime($vv['atime'])>=$v['time']&&$vv['uid']==$v['uid']){
					$svrecord[$k]['type']=11;//加入购物车
				}
			}
			foreach ($todayOrders as $vo){
				if ($vv['goods_id']==$vo['goods_id']&&strtotime($vo['atime'])>=$v['time']&&$vv['uid']==$vo['uid']){
					$svrecord[$k]['type']=12;//下单
				}
			}
// 			$area=$this->GetIpLookup($v['ip']);//由于过于消耗时间导致超时，暂时关闭
// 			if ($area['province'])
// 				$svrecord[$k]['province']=$area['province'];
// 			if ($area['city'])
// 				$svrecord[$k]['city']=$area['city'];
		}
		
		$result=M('ad_tj_ads')->addAll($allTjData);
		if($result){
			M('ad_sv_ads')->addAll($svrecord);
			$this->apiReturn(1);
		}else{
			$this->apiReturn(3);
		}
	}

}