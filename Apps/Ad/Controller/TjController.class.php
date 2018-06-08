<?php
namespace Ad\Controller;

/**
 * 广告统计
 */

class TjController extends AuthController
{

	public function index(){
		$device=I('get.device',0);
		$this->authApi('/Tj/adstatistical',['date' => 2, 'device'=>$device, 'group'=>'a.date', 'order'=>'a.`date` DESC', 'join'=>0, 'field'=>',1', '_string'=>0, 'limit'=>2, 'maction'=>0, 'cp'=>0]);
		$data=[];
		foreach ($this->_data['data'] as $v){
			if ($v['date']==date('Y-m-d',strtotime('-1 day'))){
				$data[0]=$v;
			}else {
				$data[1]=$v;
			}
		}
		$this->assign('tjTwoDay',$data);//昨日数据
		
		$this->authApi('/Tj/adstatistical',['date' => 7, 'device'=>$device, 'group'=>'a.date', 'order'=>'a.`date` ASC', 'join'=>0, 'field'=>',1', '_string'=>0, 'limit'=>7, 'maction'=>0, 'cp'=>0]);
		$this->assign('adstj',$this->_data['data']);//最近7天图标数据
		
		// 热门广告Top5
		$this->authApi('/Tj/adstatistical',['date' => 0, 'device'=>$device, 'group'=>'a.aid', 'order'=>'a.`hit` DESC', 'join'=>C('DB_PREFIX').'ad b on a.aid=b.id|'.C('DB_PREFIX').'ad_position c on b.position_id=c.id', 'field'=>',b.name,b.images,b.type,b.url,c.device', '_string'=>0, 'limit'=>5, 'maction'=>0, 'cp'=>0]);
		$this->assign('topAd',$this->_data['data']);
		
		// 热门宝贝Top5
		$this->authApi('/Tj/adstatistical',['date' => 0, 'device'=>$device, 'group'=>'a.goods_id', 'order'=>'a.`orders` DESC', 'join'=>C('DB_PREFIX').'goods b on a.goods_id=b.id|'.C('DB_PREFIX').'ad c on a.aid=c.id', 'field'=>',b.id,b.goods_name,b.images,b.price,c.url', '_string'=>'a.goods_id>0', 'limit'=>5, 'maction'=>0, 'cp'=>0]);
		$this->assign('topGoods',$this->_data['data']);
		
		$this->display();
	}

	public function live(){
		$ads=M('ad')->field('id')->where(['uid'=>session('user.id')])->select();
		$aids=[];
		foreach ($ads as $v){
			$aids[]=$v['id'];
		}
		if ($aids){
			$aids=implode(',', $aids);
		}else {
			$aids=0;
		}
		$date=I('get.date');
		if (!$date){
			$date=0;
		}
		$this->authApi('/Tj/adsShowPath', ['aid'=>$aids,'device'=>I('get.device',0),'date'=>$date,'pagesizec'=>6,'cpage'=>I('get.p',1),'group'=>'a.uid,a.aid','maction'=>U('Tj/live',I('get.'),''),'is_ajax'=>0]);
		$this->assign('data', $this->_data['data']);
		$this->display();
	}

	public function sucai(){
		$data['pagesize'] = 8;

		$data['p'] = $params['p'] = I('get.p', 1, 'int');
		$this->authApi('/Tj/getAdtjsc',$data, 'device')->with();
		# $this->assign('data', $this->_data['data']);
		# print_r($this->_data);
		$this->assign('params', $params);
		$this->display();
	}
	
	public function jcdata(){
		$device=I('get.device',0);
		$this->authApi('/Tj/adstatistical',['date' => 0, 'device'=>$device, 'group'=>'a.aid', 'order'=>'a.`date` DESC', 'join'=>C('DB_PREFIX').'ad b on a.aid=b.id', 'field'=>',b.name,b.sort,b.sday,b.eday,b.images,b.type', '_string'=>0, 'limit'=>-1, 'maction'=>'Tj/jcdata/device/'.$device, 'cp'=>I('get.p',1)]);
		$this->assign('data',$this->_data['data']);
		$this->display();
	}


}