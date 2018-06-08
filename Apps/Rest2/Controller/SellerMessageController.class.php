<?php
namespace Rest2\Controller;

class SellerMessageController extends ApiController
{

	/**
	 * subject: 商家消息 - 用户店铺信息
	 * api: /SellerMessage/userList
	 * day: 2017-03-13
	 * author: Lzy
	 * param: username,string,1,用户nick，多个用逗号隔开
	 */
	public function userList(){
		$field = 'username,sign';
		$this->check($field,false);
		
		$res = $this->_userList($this->post);
        $this->apiReturn($res);	
	}


	public function _userList($param){
		$list = D('Common/UserShopRelation')->relation(true)->field('id,nick')->where(['nick' => ['in',$param['username']]])->select();
		foreach ($list as $key => $value) {
			$list[$value['nick']] = $value;
		}
		return  ['code' => 1, 'data' => $list];
	}














}