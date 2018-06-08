<?php
namespace Rest\Controller;

class ShopNewsController extends CommonController
{

	/**
	 * 创建更新公告
	 */
	public function create(){
		if(empty($_POST['remark'])){
			$this->apiReturn(0,['msg' => '公告内容不能为空']);
		}
		$this->need_param = array('openid','remark','status');
		$this->_need_param();
		$this->_check_sign();

		$status = I('status',0,'int');
		$this->checkStatus($status);
		$id = M('shop_news')->field('id')->where(['uid' => $this->uid])->getField('id');
		
		if($id){
			$data = array(
				'remark' => I('post.remark'),
				'status' => $status,
			);
			$result = M('shop_news')->where(['id' => $id])->data($data)->save();
			S($this->cacheKey(M('shop')->where(['uid' => $this->uid])->getField('id')),null);
		}else{
			$data = array(
				'uid' 		=> $this->uid,
				'shop_id' 	=> M('shop')->where(['uid' => $this->uid])->getField('id'),
				'remark' 	=> I('post.remark'),
				'status' 	=> $status,
			);
			$result = M('shop_news')->data($data)->add();
		}
		if($result !== false){
			$this->apiReturn(1);
		}else{
			$this->apiReturn(0);
		}
		
	}


	/**
	 * 公告详情
	 * @param string $openid 卖家openid
	 */
	public function info(){
		$this->need_param = array('shop_id');
		$this->_need_param();
		$this->_check_sign();

		$shop_id = I('shop_id');
		$one = M('shop_news')->cache($this->cacheKey($shop_id),1800)->where(['shop_id' => $shop_id])->find();
		if($one){
			$one['remark'] = html_entity_decode($one['remark']);
			$this->apiReturn(1, ['data' => $one]);
		}else{
			$this->apiReturn(3);
		}
	}

	private function checkStatus($status){
		if(! in_array($status, [0,1],true)) $this->apiReturn(0);
	}

	private function cacheKey($shop_id){
		return md5('_ShopNewsInfo_' . $shop_id);
	}



}