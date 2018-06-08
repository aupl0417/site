<?php
namespace Rest\Controller;

class MakeMobileController extends CommonController
{

	const Types = array(
		1 => array(
			'name' => '轮播图',
			'html' => 'slide',
			'icon' => 'slide.jpg',
			'image' => 'slide.jpg',
		),
		2 => array(
			'name' => '图片',
			'html' => 'images',
			'icon' => 'image.jpg',
			'image' => 'images.png',
		),
	);

	public function plist(){
		$this->need_param = ['openid'];
		$this->_need_param();
		$this->_check_sign();
		$list = M('shop_make_m_edit')->where(['uid' => $this->uid])->order('sort desc,id asc')->select();
		if($list){
			$this->apiReturn(1,['data' => $list]);
		}else{
			$this->apiReturn(0);
		}
	}

	public function view(){
		$this->need_param = ['id','openid'];
		$this->_need_param();
		$this->_check_sign();
		$one = M('shop_make_m_edit')->find(I('id',0,'int'));
		if($one && $one['uid'] == $this->uid){
			$this->apiReturn(1,['data' => $one]);
		}else{
			$this->apiReturn(3);
		}
	}


	public function create_save(){
		$this->need_param = array('openid','type','type_name','data');
		if(isset($_POST['id'])) $this->need_param[] = 'id';
		$this->_need_param();
		$this->_request_check(0.5);
		$this->_check_sign();

		if(M('shop_make_m_edit')->where(['uid' => $this->uid])->count() >= 10){
			$this->apiReturn(0,['msg' => '添加的板块过多！']);
		}

		$data = array(
			'type' 		=> I('post.type',0,'int'),
			'type_name' => I('post.type_name'),
			'uid'		=> $this->uid,
			'data'		=> I('post.data'),
		);
		$id = I('post.id',0,'int');
		$model = M();
		$model->startTrans();
		$one = M('shop_make_m_edit')->find($id);
		if($one && $one['uid'] == $this->uid){
			$result = M('shop_make_m_edit')->data($data)->where(['id' => $id])->save();
		}else{
			$data['shop_id'] = M('shop')->where(['uid' => $this->uid])->getField('id');
			$result = M('shop_make_m_edit')->data($data)->add();
		}

		if($result !== false){
			$model->commit();
			$this->apiReturn(1);
		}else{
			$model->rollback();
			$this->apiReturn(0);
		}
	}

	public function sort_save(){
		$this->need_param = array('ids','openid');
		$this->_need_param();
		$this->_request_check(1);
		$this->_check_sign();
		$ids = $sort = explode(",", trim(I('post.ids'),','));
		sort($sort);
		foreach($ids as $id){
			$data 	= array();
			$s 		= array_pop($sort);
			$where 	= array(
				'uid' 	=> $this->uid,
				'id' 	=> $id,
				'sort' 	=> ['neq',$s],
			);
			$data['sort'] = $s;
			M('shop_make_m_edit')->where($where)->data($data)->save();
		}
		$this->apiReturn(1);
	}

	public function delete(){
		$this->need_param = ['openid','id'];
		$this->_need_param();
		$this->_request_check(0.5);
		$this->_check_sign();
		$one = M('shop_make_m_edit')->field('uid,id')->find(I('id',0,'int'));
		if($one && $one['uid'] == $this->uid){
			if(false !== M('shop_make_m_edit')->where(['id' => $one['id']])->delete()){
				$this->apiReturn(1);
			}else{
				$this->apiReturn(0);
			}
		}else{
			$this->apiReturn(3);
		}
	}

	public function publish(){
		$this->need_param = ['openid'];
		$this->_need_param();
		$this->_request_check(5);
		$this->_check_sign();

		$model 	= M();
		$data 	= array();
		$model->startTrans();
		$list = M('shop_make_m_edit')->where(['uid' => $this->uid])->select();
		if(empty($list)){
			$model->rollback();
			$this->apiReturn(3);
		}

		# 删除发布表表的数据
		M('shop_make_m_publish')->where(['uid' => $this->uid])->delete();
		# $this->apiReturn(3);
		foreach($list as $value){
			$data[] = array(
				'type' 		=> $value['type'],
				'type_name' => $value['type_name'],
				'uid'		=> $value['uid'],
				'data'		=> $value['data'],
				'sort' 		=> $value['sort'],
				'shop_id'	=> $value['shop_id'],
			);
		}
		$ids = M('shop_make_m_publish')->addAll($data);
		# 插入数量是否和$data的数量一致
		if(M('shop_make_m_publish')->where(['uid' => $this->uid])->count() == count($data)){
			$model->commit();
			$this->apiReturn(1);
		}else{
			$model->rollback();
			$this->apiReturn(0);
		}
	}

}