<?php
namespace Rest2\Controller;

class ImController extends ApiController
{
	//正式环境
	private $production = array(
				'info'	=> 'https://imweb.dtfangyuan.com:9443',
				'api'	=> 'https://im.dtfangyuan.com:9091',
			);
	//测试环境
	private $test = array(
				'info'	=> 'http://192.168.3.219:8088',
				'api'	=> 'http://192.168.3.218:9090',
			);
	
	


	/**
     * subject: 消息列表
     * api: /Im/message_list
     * author: liangfeng
     * day: 2017-05-06
     *
     * [字段名,类型,是否必传,说明]
     * param: nick,string,1,用户昵称
     */
    public function message_list(){
        $this->check('nick',false);
        $res = $this->_message_list($this->post);
        $this->apiReturn($res);
    }
	
    public function _message_list($param){
		$data['fromName'] = $param['nick'];
        $res=$this->imApi('userserviceConversationList',$data);
		
		if($res['code'] == 1 && !empty($res['data'])){
			foreach($res['data'] as $k => $v){
				$nicks .= $v['username'].',';
				$res['data'][$k]['content'] = json_decode($v['content'],true);
			}
			$nicks = substr($nicks,0,strlen($nicks)-1); 
			$map['ylh_user.nick'] = ['in',$nicks];
			$shop_names = M('shop')->join('left join ylh_user ON ylh_shop.uid=ylh_user.id')->field('ylh_shop.id,ylh_shop.shop_name,ylh_shop.shop_logo,ylh_user.nick')->where($map)->select();
			
			foreach($res['data'] as $k => $v){
				foreach($shop_names as $ke => $va){
					if($v['username'] == $va['nick']){
						$res['data'][$k]['shop_name'] = $va['shop_name'];
						$res['data'][$k]['shop_logo'] = $va['shop_logo'];
						$res['data'][$k]['shop_id'] = $va['id'];
						unset($shop_names[$ke]);
						break;
					}
				}
				
				//提示信息
				if($v['content']['type'] == 'images'){
					$res['data'][$k]['content']['content'] = '[图片]';
				}else if($v['content']['type'] == 'voice'){
					$res['data'][$k]['content']['content'] = '[语音]';
				}else if($v['content']['type'] == 'video'){
					$res['data'][$k]['content']['content'] = '[视频]';
				}else if($v['content']['type'] == 'DTCard'){
					$res['data'][$k]['content']['content'] = '[名片]';
				}else if($v['content']['type'] == 'recallchat'){
					$res['data'][$k]['content']['content'] = '[撤回了一条消息]';
				}else if($v['content']['type'] == 'redpacket'){
					$res['data'][$k]['content']['content'] = '[红包]';
				}else if($v['content']['type'] == 'address'){
					$res['data'][$k]['content']['content'] = '[分享了一个位置]';
				}else if($v['content']['type'] == 'notification'){
					$res['data'][$k]['content']['content'] = '[通知消息]';
				}else if($v['content']['type'] == 'approval'){
					$res['data'][$k]['content']['content'] = '[审批]';
				}else if($v['content']['type'] == 'goodsInfo'){
					$res['data'][$k]['content']['content'] = '[分享了一个商品]';
				}else if($v['content']['type'] == 'dealInfo'){
					$res['data'][$k]['content']['content'] = '[交易信息]';
				}else{
					$res['data'][$k]['content']['content'] = '[点击阅读消息]';
				}
				
				
				
			}
		}else if($res['code'] == 1 && empty($res['data'])){
			return ['code'=>3];
		}
		return $res;
		
    }
	
	/**
     * subject: 是否有新消息
     * api: /Im/have_message
     * author: liangfeng
     * day: 2017-05-08
     *
     * [字段名,类型,是否必传,说明]
     * param: fromName,string,1,用户昵称
     * param: toName,string,1,对方昵称
     */
	public function have_message(){
		$this->check('openid',false);
        $res = $this->_have_message($this->post);
        $this->apiReturn($res);
	}

	public function _have_message($param){
		$data['fromName'] = $this->user['nick'];
		$res=$this->imApi('userserviceConversationAlert',$data);
		return $res;
	}
	
	/**
     * subject: 删除消息
     * api: /Im/del_message
     * author: liangfeng
     * day: 2017-05-08
     *
     * [字段名,类型,是否必传,说明]
     * param: fromName,string,1,用户昵称
     * param: toName,string,1,对方昵称
     */
	public function del_message(){
		$this->check('fromName,toName',false);
        $res = $this->_del_message($this->post);
        $this->apiReturn($res);
	}

	public function _del_message($param){
		$data['fromName'] = $param['fromName'];
		$data['toName'] = $param['toName'];
		$res=$this->imApi('userserviceConversationDel',$data,'post');
		return $res;
	}


	/**
     * Im统一请求方法
     * @param string $apiurl 要请求的接口
     * @param array	$data 	要请求的数据
     */
	private function imApi($apiurl,$data,$content='get'){
		$url = (C('DOMAIN') == 'trj.cc' ? $this->production['api'] : $this->test['api']).'/plugins/userService/'.$apiurl.'?';
		
		$data['userstatus'] = 'a';
		$data['signValue'] = $this->MessageEncRypt($data);
		$url .= $this->http_build_query_new($data);
		

		if($content =='get'){
			$res = json_decode($this->curl_get($url), true);
		}else if($content == 'post'){
			$res = json_decode($this->curl_post($url), true);
		}
		
		return $res;

		
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