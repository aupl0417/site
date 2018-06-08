<?php
//** 快递接口类
namespace Org\Util;

class Express{
	/*
	* 采集网页内容的方法
	*/
	private function getcontent($url){
		if(function_exists("file_get_contents")){
			$file_contents = file_get_contents($url);
		}else{
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}
		return $file_contents;
	}

	private function object_array($array) {
		if(is_object($array)) {
			$array = (array)$array;
		} if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = $this->object_array($value);
			}
		}
		return $array;
	}

	//物流公司对应代码查询
	public function GetLogisticsCode($code=''){
		$logistics = array('ems'=>'EMS','AAE全球专递'=>'aae','安捷快递'=>'anjiekuaidi','安信达快递'=>'anxindakuaixi',
			'百福东方'=>'baifudongfang','彪记快递'=>'biaojikuaidi','BHT'=>'bht','希伊艾斯快递'=>'cces',
			'中国东方（COE）'=>'Coe','长宇物流'=>'changyuwuliu','大田物流'=>'datianwuliu','德邦物流'=>'debangwuliu',
			'DPEX'=>'dpex','DHL'=>'dhl','D速快递'=>'dsukuaidi','fedex'=>'fedex','飞康达物流'=>'feikangda',
			'凤凰快递'=>'fenghuangkuaidi','港中能达物流'=>'ganzhongnengda','广东邮政物流'=>'guangdongyouzhengwuliu',
			'汇通快运'=>'huitongkuaidi','恒路物流'=>'hengluwuliu','华夏龙物流'=>'huaxialongwuliu','佳怡物流'=>'jiayiwuliu',
			'京广速递'=>'jinguangsudikuaijian','急先达'=>'jixianda','佳吉物流'=>'jiajiwuliu','加运美'=>'jiayunmeiwuliu',
			'快捷速递'=>'kuaijiesudi','联昊通物流'=>'lianhaowuliu','龙邦物流'=>'longbanwuliu','民航快递'=>'minghangkuaidi',
			'配思货运'=>'peisihuoyunkuaidi','全晨快递'=>'quanchenkuaidi','全际通物流'=>'quanjitong','全日通快递'=>'quanritongkuaidi',
			'全一快递'=>'quanyikuaidi','盛辉物流'=>'shenghuiwuliu','速尔物流'=>'suer','盛丰物流'=>'shengfengwuliu','天地华宇'=>'tiandihuayu',
			'天天快递'=>'tiantian','TNT'=>'tnt','UPS'=>'ups','万家物流'=>'wanjiawuliu','文捷航空速递'=>'wenjiesudi','伍圆速递'=>'wuyuansudi',
			'万象物流'=>'wanxiangwuliu','新邦物流'=>'xinbangwuliu','信丰物流'=>'xinfengwuliu','星晨急便'=>'xingchengjibian',
			'鑫飞鸿物流快递'=>'xinhongyukuaidi','亚风速递'=>'yafengsudi','一邦速递'=>'yibangwuliu','优速物流'=>'youshuwuliu',
			'远成物流'=>'yuanchengwuliu','圆通速递'=>'yuantong','源伟丰快递'=>'yuanweifeng','元智捷诚快递'=>'yuanzhijiecheng',
			'越丰物流'=>'yuefengwuliu','韵达快运'=>'yunda','源安达'=>'yuananda','运通快递'=>'Yuntongkuaidi','宅急送'=>'zhaijisong',
			'中铁快运'=>'zhongtiewuliu','中通速递'=>'zhongtong','中邮物流'=>'zhongyouwuliu','申通快递'=>'shentong','顺丰快递'=>'shunfen'
		);
		return !empty($code) ? (!empty($logistics[$code])?$logistics[$code]:false) : false;
	}

	public  function getorder($name,$order){
		$name = trim($name);
		$keywords = $this->GetLogisticsCode($name);

		if($keywords){
			$result = $this->getcontent("http://www.kuaidi.com/index-ajaxgetcourier-{$order}-{$keywords}.html");

			$result = json_decode($result);
			$data = $this->object_array($result);
			return $data;
		}else{
			return array('success'=>false);
		}

	}
}
?>