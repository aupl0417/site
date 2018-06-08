<?php
namespace Mobile\Controller;

/**
 * 消息页面
 * @author Lzy
 * @date 2017-03-13 10:00:00
 */

class MessageController extends CommonController
{

	# 线上是否展示商家消息列表和进入聊天
	private $productionShow = true;
	# 线上domain
	private $productionDomain = 'trj.cc';

	/**
	 * url配置
	 */
	private function shopMessageUrl($key = ''){
		# 正式环境
		$production = array(
			'info'	=> 'https://imweb.dtfangyuan.com:9443',
			'api'	=> 'https://im.dtfangyuan.com:9091',
		);
		# 测试环境
		$test = array(
			'info'	=> 'http://192.168.3.219:8088',
			'api'	=> 'http://192.168.3.218:9090',
		);
		return C('DOMAIN') == $this->productionDomain ? $production[$key] : $test[$key];
	}


	public function index(){
		$this->check_logined();

		
		//$res = $this->doApi2('/Im/message_list',['nick'=>session('user.nick')]);
		//dump($res);
		//$this->assign('message_list',$res['data']);
		
		/*
		# 商家消息列表
		if(C('DOMAIN') == $this->productionDomain){
			if($this->productionShow){
				$shop_message = $this->SellerMessage();
				$this->assign('shop_message', $shop_message);
			}
		}else{
			$shop_message = $this->SellerMessage();
			$this->assign('shop_message', $shop_message);
		}
		*/

		//获取c+一条最新系统公告
//		$data['openid'] = session('user')['openid'];
//		$cnews = $this->doApi2('/Erp/new_news',$data);
		//dump($cnews);
		//获取乐兑一条最新系统通知
		if($_SESSION['user']) $notice = $this->doApi2('/Notice/new_notice',$data);
	
	    //获取乐兑一条最新公告
		$data['num'] = 1;
		$res = $this->doApi2('/News/getNew',$data);
		
		
		$this->assign('trj_news', $res['data'][0]);
		$this->assign('trj_notice', $notice['data']);
		$this->assign('c_news', $cnews['data']);
		$this->display();
	}
	
	/**
	 * 读取消息列表
	 * @author liangfeng 
	 * 2017-05-18
	 */
	public function ajax_im_list(){
		$res = $this->doApi2('/Im/message_list',['nick'=>session('user.nick')]);
		//dump($res);
		foreach($res['data'] as $k => $v){
			if(!$v['shop_logo']) $res['data'][$k]['shop_logo'] = '/Public/images/nopic.png';
		}
		$this->ajaxReturn($res);
	}
	
	/**
	 * 删除消息
	 * @author liangfeng 
	 * 2017-05-18
	 */
	public function ajax_del_message(){
		$res = $this->doApi2('/Im/del_message',I('post.'));
		$this->ajaxReturn($res);
	}
	
	

	/**
	 * 是否有商家消息，code为0代表无消息，1代表有消息
	 */
	public function have_message(){
		# 是否有消息，code为1有消息
		if(empty(session('user.nick'))){
			return 0;
		}
		$param['fromName'] = session('user.nick');
		$param['userstatus'] = 'a';
		$param['signValue'] = $this->MessageEncRypt($param);
		$url = $this->shopMessageUrl('api') . '/plugins/userService/userserviceConversationAlert?' . $this->http_build_query_new($param);
		
		$sellerMsg = json_decode($this->curl_get($url),true);
		
		return (int) $sellerMsg['code'];
	}

	/**
	 * 删除聊天
	 */
	public function delete_message(){
		IS_POST or die();
		$param['fromName'] 	= I('post.formName');
		$param['toName'] 	= I('post.toName');
		$param['userstatus'] = 'a';
		$param['signValue'] = $this->MessageEncRypt($param);
		$url = $this->shopMessageUrl('api') . '/plugins/userService/userserviceConversationDel?' . $this->http_build_query_new($param);
		# dump($url);
		$request = $this->curl_post($url);
		# dump($request);
		$request = json_decode($request, true);
		$this->ajaxReturn($request);
	}



	/**
	 * 商家消息列表
	 */
	private function SellerMessage(){
		$param['fromName'] = session('user.nick');
		$param['userstatus'] = 'a';
		$param['signValue'] = $this->MessageEncRypt($param);
		$url = $this->shopMessageUrl('api') . '/plugins/userService/userserviceConversationList?' . $this->http_build_query_new($param);
		$request = $this->curl_get($url);
		$request = json_decode($request, true);
		# dump($request);
		$list = [];
		$userList = [];
		if($request['code'] == 1 && $request['data']){
			$list = $request['data'];
			foreach ($list as $key => $value) {
				$list[$key]['time'] = explode(".", $value['time'])[0];
				$userList[] = $value['username'];
			}
			$userList = $this->doApi2('/SellerMessage/userList',['username' => implode(",", $userList)]);
			$userList = $userList['data'];
		}
		
		return array(
			'list' => $list,
			'userList' => $userList,
		);
	}

	/**
	 * 商家消息聊天页
	 * type 1从列表进来 0从其他地方
	 */
	public function seller_message_info($type = 0){
		$this->check_logined();
		# 测试控制
		if(C('DOMAIN') == $this->productionDomain){
			if($this->productionShow == false){
				$this->redirect('/shop/contact', ['shop_id' => I('shop_id',0,'int')]);
				exit();
			}
		}
		$shopInfo 	= $this->doApi2('/Shop/info',['shop_id' => I('shop_id',0,'int')]);
		$goodsInfo 	= $this->doApi2('/Goods/view',['id' => I('goods_id')]);
		
		$param['sendert'] 	= session('user.nick');
		$param['type'] 		= I('type',0,'int');
		$param['receiver'] 	= I('nick');
		if($goodsInfo['code'] == 1){
			$param['shop_name']			= $goodsInfo['data']['shop']['shop_name'];
			$param['commodity_name'] 	= $goodsInfo['data']['goods']['goods_name'];
			$param['commodity_price'] 	= $goodsInfo['data']['price'];
			$param['integral'] 			= $goodsInfo['data']['score'];
			$param['commodity_image'] 	= $goodsInfo['data']['images'];
			$param['goods_url'] 		= sub_domain()['m'] . U('/Goods/view',['id' => $goodsInfo['data']['id']]);
		}
		$param['signValue'] = $this->MessageEncRypt($param);
		
		$iframeUrl = $this->shopMessageUrl('info') . '/storeim/wap.html?' . $this->http_build_query_new($param);

		$this->assign('receiver',$param['receiver']);
		$this->assign('shopInfo',$shopInfo['data']);
		$this->assign('iframeUrl', $iframeUrl);
		$this->display();
	}

	private function MessageEncRypt($data = array()){
		# a=443&ka=ff&p=1&pagesize=10&q=%E5%95%8A%E8%B7%AF
		# a=443&ka=ff&p=1&pagesize=10&q=%E5%95%8A%E8%B7%AF
		# $data['a'] = 443;
		# $data['p'] = 1;
		# $data['ka'] = 'ff';
		# $data['pagesize'] = 10;
		# $data['q'] = '啊路';
		
		ksort($data);
		return md5($this->http_build_query_new($data) . '&C394D38AF05D4D5CA0C8E7655A39F0A4');
	}

	private function http_build_query_new($data){
		$str = array();
		foreach ($data as $key => $value) {
			$str[] = $key . '=' . $value;
		}
		return implode("&", $str);
	}
}