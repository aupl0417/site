<?php
namespace Ad\Controller;

class IndexController extends AuthController
{



	public function index(){
		# 统计消费等
		$tongji = array();
		$this->authApi('/SellerAd/ad_total');
		if($this->_data['code'] == 1){
			$tongji = $this->_data['data'];
		}
		$this->assign('tongji', $tongji);
		
		# 统计广告信息
		$this->authApi('/Tj/adstatistical',['date' => 2, 'device'=>0, 'group'=>'a.date', 'order'=>'a.`date` DESC', 'join'=>0, 'field'=>',1', '_string'=>0, 'limit'=>2, 'maction'=>0, 'cp'=>0]);
		$data=[];
		foreach ($this->_data['data'] as $v){
			if ($v['date']==date('Y-m-d',strtotime('-1 day'))){
				$data[0]=$v;
			}else {
				$data[1]=$v;
			}
		}
		$this->assign('tjTwoDay',$data);
		
		// 热门广告Top5
		$this->authApi('/Tj/adstatistical',['date' => 0, 'device'=>0, 'group'=>'a.aid', 'order'=>'a.`hit` DESC', 'join'=>C('DB_PREFIX').'ad b on a.aid=b.id|'.C('DB_PREFIX').'ad_position c on b.position_id=c.id', 'field'=>',b.name,b.images,b.type,b.url,c.device', '_string'=>0, 'limit'=>5, 'maction'=>0, 'cp'=>0]);
		$this->assign('topAd',$this->_data['data']);

		// 热门宝贝Top5
		$this->authApi('/Tj/adstatistical',['date' => 0, 'device'=>0, 'group'=>'a.goods_id', 'order'=>'a.`orders` DESC', 'join'=>C('DB_PREFIX').'goods b on a.goods_id=b.id|'.C('DB_PREFIX').'ad c on a.aid=c.id', 'field'=>',b.id,b.goods_name,b.images,b.price,c.url', '_string'=>'a.goods_id>0', 'limit'=>5, 'maction'=>0, 'cp'=>0]);
		$this->assign('topGoods',$this->_data['data']);
		
		# 账户信息
		$account = array();
		$this->authApi('/Erp/account');
		if($this->_data['code'] == 1){
			$account = $this->_data['data'];
		}
		$this->assign('account', $account);
		
		C('seo', ['title' => '广告投放']);
		
		$this->display();
	}





}