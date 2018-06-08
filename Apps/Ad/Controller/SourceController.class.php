<?php
namespace Ad\Controller;

/**
 * 资源位
 */

class SourceController extends AuthController
{
	private $ts = array(
		0 => '商品',
		1 => '店铺',
		2 => '站外链接',
	);


	public function index2(){
		// $data['device'] = 1;# PC端
		$params = array();# 参数
		// 尺寸
		$this->api('/Ad/condition')->with('condition');
		$condition = $this->_data['data'];
		// 分页
		$p = I('p', 1, 'int');
		if($p > 1){
			$data['p'] = $p;
			$params['p'] = $p;
		}
		$data['pagesize'] = 10;

		// 搜索
		if(isset($_GET['device'])){
			
			$sValue = I('device','');
			foreach ($condition['device'] as $vo) {
				if($vo['value'] == $sValue){
					$params['device'] 	= $sValue;
					$data['device'] 	= $sValue;
					break;
				}
			}
		}
		if(isset($_GET['size'])){
			$sValue = I('size','');
			foreach ($condition['size'] as $vo) {
				if($vo['value'] == $sValue){
					$params['size'] = $sValue;
					$data['size'] 	= $sValue;
					break;
				}
			}
		}
		// dump($data);
		// 资源位列表
		$nosign = 'device,size,type,channel,category_id';
		$this->api('/Ad/position_list',$data,$nosign)->with();
		# print_r($this->_data);

		if(! empty($params)){
			$this->assign('params', $params);
		}
		// dump($params);
		C('seo', ['title' => "资源位_第".$p."页_广告位"]);
		$this->display();
	}
	
	public function index(){
		$channel = I('get.channel','index');
		$data['device'] = I('get.device',1);
		# 获取频道资源位
		$this->api('/Ad/position_list',$data)->with();
		$data['channel'] = $channel;
		$this->assign('parm',$data);
		C('seo', ['title' => '投放广告']);
		if ($data['device']==2){
			$channel='wap-'.$channel;
		}
		$this->display($channel);
	}
	


	public function create_orders(){
		# dump(session());
		# $result=ad_days_check('',130,1,1);
		# dump($result);
		# 获取资源详情
		$id = I('id', 0, 'int');
		$sort = I('sort', 0, 'int');
		$this->api('/Ad/position_view', ['id' => $id, 'sort' => $sort], 'sort')->with('data');
		# dump($this->_data);
		$calendar = $this->_data['calendar'];# 日期表
		$info = $this->_data['data'];
		# 超出位置num-1 或小于0 重定向到默认第一个位置,防止被手动输入sort参数
		if($sort > $info['num'] - 1 || $sort < 0){
			$this->redirect('Source/create_orders',['id' => $id]);
			exit;
		}
		# 获取素材,根据资源的尺寸和类目
		$data['pagesize']	= 1000;
		$data['size'] 		= $info['width'] . 'x' . $info['height'];
		if ($info['background_width'] > 0) {
		    $data['bsize']  = $info['background_width'] . 'x' . $info['background_height'];
		}
		$data['category'] 	= $info['category_id'];
		$data['status'] 	= 1;
		# dump($category);
		$nosign = 'size,category,bsize';
		$this->authApi('/SellerAd/sucai_list',$data,$nosign);
		$sucai = array();
		# dump($this->_data);
		if($this->_data['code'] == 1){
			$sucai = $this->_data['data'];
		}
		# print_r($sucai);
		# 在售商品
		$this->authApi('/SellerGoods/goods_online',['pagesize' => 100]);
		$sale = array();
		if($this->_data['code'] == 1){
			$sale = $this->_data['data'];
		}
		# dump($this->_data);
		
		C('seo', ['title' => '投放广告']);
		$this->assign('sort',$sort);
		$this->assign('id',$id);
		$this->assign('sale', $sale);
		$this->assign('sucai', $sucai);
		$this->assign('calendar', $calendar);
		$this->assign('days_use', $days_use);
		$this->assign('ts', $this->ts);
		$this->display();
	}

	/**
	 * 创建延期订单
	 */
	public function create_defer_orders($a_no){
		$ad = $this->doApi('/SellerAd/orders_view',['openid' => session('user.openid'),'a_no' => $a_no],'', 1);
		if(! isset($ad['code']) || $ad['code'] != 1){
			$this->redirect('/My/index');
			exit;
		}
		$sucai = $this->doApi('/SellerAd/sucai_view',['openid' => session('user.openid'),'id' => $ad['data']['sucai_id']],'', 1)['data'];
		$goods = M('goods')->where()->field('id,images,goods_name,price,score_ratio,num')->find($ad['data']['goods_id']);
		# dump($goods);exit;
		$id = $ad['data']['ad_position']['id'];
		
		$position = $this->doApi('/Ad/position_view',['id' => $id], '', 1);
		if(! isset($position['code']) || $position['code'] != 1){
			$this->redirect('/My/index');
			exit;
		}
		$sort 	= I('sort',-1, 'int');

		if($sort > $position['data']['num'] - 1 || $sort < 0){
			$sort 	= $ad['data']['sort'];
		}
		# dump($position);exit;
		# 日期表
		$calendar 	= $position['calendar'];
		# dump($calendar);exit;

		C('seo', ['title' => '延期投放广告']);

		$this->assign('sucai',$sucai);
		$this->assign('goods', $goods);
		$this->assign('ad',$ad['data']);
		$this->assign('data',$position['data']);
		$this->assign('sort',$sort);
		$this->assign('id',$id);
		$this->assign('calendar', $calendar);
		$this->display();
	}

	

	/**
	 * 选择商品
	 */
	public function choose_goods($device, $p = 1, $q = '') {
	    $data   = [
	        'pagesize'          => 6,
	        'q'                 => $q,
	        # 'code'              => I('get.code'),
	        # 'category_id'       => I('get.category_id'),
	        # 'shop_category_id'  => I('get.shop_category_id'),
	        # 's_price'           => I('get.s_price'),
	        # 'e_price'           => I('get.e_price'),
	        # 's_sale'            => I('get.s_sale'),
	        # 'e_sale'            => I('get.e_sale'),
	        # 'is_best'           => I('get.is_best')
	    ];
	    $nosign = 'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best';
	    
	    $this->assign('q', $q);
	    $this->authApi('/SellerGoods/goods_online',$data,$nosign)->with();
	    $this->display();
	}

	/**
	 * 选择素材
	 */
	public function choose_sucai($size){
		$data = array(
			'pagesize'  => 6,
			'size' 		=> $size,
			'status'	=> 1,
		);
		$nosign = 'p,pagesize,action,q,code,size,category,bsize';
		$this->authApi('/SellerAd/sucai_list', $data, $nosign)->with();
		$this->display();
	}
	
	/**
	 * 检查素材或者商品类目是否符合广告要求
	 */
	public function checksad(){
		$id=I('post.id');
		$w=I('post.w');
		$h=I('post.h');
		$bw=I('post.bw');
		$bh=I('post.bh');
		if ($id&&$w&&$h){
			$this->authApi('/SellerAd/checksucai', ['id'=>$id,'w'=>$w,'h'=>$h,'bw'=>$bw,'bh'=>$bh]);
			$data=$this->_data['data'];
			$this->ajaxReturn($data,'JSON');
		}else {
			$this->ajaxReturn(0);
		}
	}
}