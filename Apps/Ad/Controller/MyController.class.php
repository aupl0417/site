<?php
namespace Ad\Controller;

class MyController extends AuthController
{


	public function index(){
		$params = array();
		# 状态
		$s = array(
			'all'	=> array('name' => '全部'),
			0 		=> array('name' => '未付款'),
			4 		=> array('name' => '待投放'),
			3 		=> array('name' => '投放中'),
			2 		=> array('name' => '强制下架'),
			5 		=> array('name' => '已过期'),
		);
		$status = I('status', '', 'int');
		if(isset($s[$status])){
			$data['status'] = $params['status'] = $status;
			$s[$status]['class'] = 'layui-this';
		}else{
			$s['all']['class'] 	= 'layui-this';
		}
		
		# 分页
		$p = I('p', 1, 'int');
		
		$data['p'] = $p > 0 ? $p : 1;
		$data['pagesize'] = 10;

		$this->authApi('/SellerAd/orders_list', $data)->with();
		# 统计
		$tongji = array();
		$this->authApi('/SellerAd/ad_total');
		$tongji = $this->_data['data'];
		
		C('seo', ['title' => ' 第' . $p . '页 - ' . '我的广告']);
		
		$this->assign('params', $params);
		$this->assign('tongji', $tongji);
		$this->assign('status', $s);
		$this->display();
	}


	/**
	 * 付款
	 */
	public function pay(){
		$this->info('广告订单付款');
		$this->display();
	}

	/**
	 * 详情
	 */
	public function orders_view(){
		$this->info('广告订单详情');
		$this->display();

	}

	private function info($seoTitle = '广告订单'){
		# 订单信息
		$this->authApi('/SellerAd/orders_view', ['a_no' => I('a_no', '')])->with();
		# 日期表
		$calendar = $this->_data['calendar'];
		$aid	  = $this->_data['data']['id'];
		# 广告位信息
		$this->api('/Ad/position_view',['id' => $this->_data['data']['position_id']])->with('position');
		
		# 截止到昨日的统计数据
		$this->authApi('/Tj/adstatisticalByaid',['aid'=>$aid])->with('tjdata');
		
		# 广告数据图表
		$this->authApi('/Tj/adstbByaid',['aid'=>$aid]);
		$this->assign('adstj',$this->_data['data']);
		
		C('seo', ['title' => $seoTitle]);
		$this->assign('calendar', $calendar);
	}
	
	public function adsShowPath(){
		$this->authApi('/Tj/adsShowPath', ['aid'=>I('post.aid'),'device'=>I('post.device',0),'date'=>I('post.date',0),'pagesizec'=>I('post.pagesize',3),'cpage'=>I('post.cpage',1),'group'=>'a.uid','maction'=>0,'is_ajax'=>1]);
		$this->ajaxReturn($this->_data['data'],'JSON');
	}



}