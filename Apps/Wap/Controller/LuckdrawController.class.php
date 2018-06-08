<?php
namespace Wap\Controller;
use Think\Controller;
class LuckdrawController extends ApiController {

    public function index(){
        //echo '<div align="center"><img src="/Apps/Wap/View/default/Public/Images/luckdraw_stop.jpg"></div>';
        //exit();
        $url = DM('m', '/drawluck/item/id/13');
        redirect($url);
		$url = 'http';
		if ($_SERVER["HTTPS"] == "on"){
			$url .= "s";
		}
		$url .= '://wap.'.C('domain').'/Index/index?url=';
		$this->assign('url',$url);
		$this->display();
    }

	public function luck_list(){
		$url = 'http';
		if ($_SERVER["HTTPS"] == "on"){
			$url .= "s";
		}
		$url .= '://wap.'.C('domain').'/Index/index?url=';
		$this->assign('url',$url);
		$this->display();
	}
	public function award(){
		$url = 'http';
		if ($_SERVER["HTTPS"] == "on"){
			$url .= "s";
		}
		$url .= '://wap.'.C('domain').'/Index/index?url=';
		$this->assign('url',$url);
		/*
		$res =$this->_api(['apiurl'=>'/TjShop/visit','server'=>json_encode($_SERVER),'session_id'=>session_id(),'session'=>json_encode($_SESSION),'shop_info'=>json_encode($shop_info['data']),'terminal'=>'wap','page_type'=>I('post.page_type')]);
		*/
		$res = $this->_api(['apiurl'=>'/Luckdraw/get_winning','is_openid'=>'1','uid'=>I('get.u'),'no'=>I('get.n')]);
		$res = json_decode(json_encode($res),true);

		$this->assign('prize_info',$res['data']);
		
		//如果是实物，获取用户的收货地址
		if($res['data']['prize_type'] == 1){
			$addr = $this->_api(['apiurl'=>'/Address/address_list','is_openid'=>'1']);
			$addr = json_decode(json_encode($addr),true);
			$this->assign('addr',$addr['data']);
			
			if($res['data']['express_code'] != 0 && $res['data']['is_deliver'] == 2){
				$rs = $this->_api(['apiurl'=>'/Luckdraw/query_express','no'=>I('get.n')]);
				$rs = json_decode(json_encode($rs),true);
			}
			$this->assign('express_log',$rs['data']['express']['data']);
			
		}
		
		//dump($res['data']);
		//dump($addr['data']);
		
		$this->display();
		
		
	}
	public function check_address(){
		$addr = $this->_api(['apiurl'=>'/Address/address_list','is_openid'=>'1']);
		//echo $addr;exit;
		echo json_encode($addr);exit;
		
	}
}