<?php
namespace Ad\Controller;

/**
 * 素材
 */

class SucaiController extends AuthController
{



	public function index(){
		$t = I('t', '', 'int');
		# 审核状态
		! is_int($t) or $data['status'] = $t;
		$params['t'] = $t;
		
		$p = $data['p'] = $params['p'] = I('p', 1, 'int');
		
		$nosign = '';
		if(isset($_GET['size'])){
			$data['size'] = I('size','');
			$nosign = 'size';
			$params['size'] = $data['size'];
		}
		$data['pagesize'] = 10;
		# 获取素材尺寸列表
		$this->api('/Ad/condition')->with('size');
		# 获取素材列表
		$this->authApi('/SellerAd/sucai_list', $data, $nosign)->with('data');
		# 素材统计信息
		$tongji = array();
		$this->authApi('/SellerAd/ad_total');
		if($this->_data['code'] == 1){
			$tongji = $this->_data['data'];
		}
		# dump($tongji);
		C('seo', ['title' => '第' . $p . '页 - 投放素材']);
		# dump($this->_data);

		$this->assign('tongji',$tongji);
		$this->assign('params', $params);
		if ($t=="0"){
			$t=".";
		}
		$this->assign('t',$t);
		$this->display();
	}

	public function create(){
		// 类目
		$this->api('/Ad/category')->with('category');
		// dump($this->_data);
		$category 	=	[];
		foreach ($this->_data['data'] as $value) {
			foreach($value['dlist'] as $v){
				$category[$v['id']]	=	$v['category_name'];
			}
		}
		// 尺寸
		$this->api('/Ad/condition')->with('condition');
		$size	=	[];
		$bSize  =   [];
		foreach ($this->_data['data']['size'] as $value) {
			$size[$value['name']]	=	$value['value'];
		}
		foreach ($this->_data['data']['bsize'] as $value) {
		    $bSize[$value['b_name']]	=	$value['b_value'];
		}
		$this->builderForm()
		->keySelect('category_id', '投放类目', $category,  1)
		->keySelect('size', '尺寸px', $size, 1)
		->keySelect('bsize', '背景尺寸px', $bSize)
		->keyText('sucai_name', '素材标题', 1)
		->keySingleImages('images', '素材图片', 1)
		->keySingleImages('background_images', '素材背景图片')
		->view();

		// 统计
		$tongji = array();
		$this->authApi('/SellerAd/ad_total');
		if($this->_data['code'] == 1){
			$tongji = $this->_data['data'];
		}
		C('seo', ['title' => '提交素材']);
		// $this->api('/Ad/category')->with('category');
		// $this->api('/Ad/condition')->with('condition');
		// print_r($this->_data['data']);exit;
		$this->assign('tongji',$tongji);
		$this->display();
	}

	public function edit(){
		
		
		// 类目
		$this->api('/Ad/category')->with('category');
		$category 	=	[];
		foreach ($this->_data['data'] as $value) {
			foreach($value['dlist'] as $v){
				$category[$v['id']]	=	$v['category_name'];
			}
		}
		// 尺寸
		$this->api('/Ad/condition')->with('condition');
		$size	=	[];
		foreach($this->_data['data']['size'] as $value){
			$size[$value['name']]	=	$value['value'];
		}
		foreach ($this->_data['data']['bsize'] as $value) {
		    $bSize[$value['b_name']]	=	$value['b_value'];
		}
		// 详情
		$id = I('id', 0, 'int');
		$this->authApi('/SellerAd/sucai_view', ['id' => $id]);
		$info = $this->_data['data'];
		$info['size'] = $info['width'] . 'x' . $info['height'];
		$info['bsize'] = $info['background_width'] . 'x' . $info['background_height'];

		$this->builderForm()
		->keyId()
		->keySelect('category_id', '投放类目', $category,  1)
		->keySelect('size', '尺寸px', $size, 1)
		->keySelect('bsize', '背景尺寸px', $bSize)
		->keyText('sucai_name', '素材标题', 1)
		->keySingleImages('images', '素材图片', 1)
		->keySingleImages('background_images', '素材背景图片')
		->data($info)
		->view();
		// 统计
		$tongji = array();
		$this->authApi('/SellerAd/ad_total');
		if($this->_data['code'] == 1){
			$tongji = $this->_data['data'];
		}
		C('seo', ['title' => '编辑素材']);
		$this->assign('tongji',$tongji);
		$this->display();
	}

	/**
	 * 添加素材,选择图片
	 */
	public function cteateImages(){
		$this->display();
	}



}