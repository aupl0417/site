<?php
namespace Seller\Controller;
/**
 * 运费模板
 */

class FreightTemplateController extends AuthController
{

	public function _initialize(){
		parent::_initialize();
	}

	/**
	 * 运费模板主页
	 */
	public function index(){
		$this->authApi('/SellerExpress/express_list')->with();
		$this->seo(['title' => '运费模板']);
		$this->display();
	}


	/**
	 * 运费模板添加
	 */
	public function create(){
		$this->authApi('/SellerExpress/express_company', array(), 'openid')->with('company');
		$this->seo(['title' => '添加运费模板']);
		$this->display();
	}


	/**
	 * 修改
	 */
	public function edit(){
		
		$this->authApi('/SellerExpress/express_view', array('id'=>I('id', 0, 'int')))->with();
		$this->authApi('/SellerExpress/express_company', array(), 'openid')->with('company');
		$this->seo(['title' => '修改运费模板']);
		$this->display();
	}

	/**
	 * 添加指定区域运费模板
	 */
	public function create_area(){
		$this->api('/Tools/one_two_city');
		$city = $this->_data['data'];

		// 获取运费模板详情
		$this->authApi('/SellerExpress/express_view', array('id'=>I('express_id', 0, 'int')));
		$express = $this->_data['data'];
		// 获取不能勾选选的列表
		$disable = array();
		foreach($express['express_area'] as $vo){
			$disable = array_merge($disable, explode(',', $vo['city_ids']));
		}
		// 去掉不能勾选
		foreach ($city as $ko =>$vo) {
			foreach ($vo['child'] as $k => $v) {
				if(in_array($v['id'], $disable)){
					$city[$ko]['child'][$k]['disable'] = 1;
				}
			}
		}
		
		$this->assign('express', $express);
		$this->seo(['title' => '添加指定区域运费模板']);
		$this->assign('city',$city);
		$this->display();
	}
	/**
	 * 修改指定区域运费模板
	 */
	public function edit_area(){

		$id = I('id', 0, 'int');
		$express_id = I('express_id', 0, 'int');
		// 获取指定地区列表
		$this->authApi('/SellerExpress/express_area', array('express_id'=>$express_id));
		$areas = $this->_data['data'];
		$area = null;
		foreach($areas as $vo){
			if($vo['id'] == $id){
				$area = $vo;
			}
		}
		// 默认选中
		$checked = explode(',', $area['city_ids']);
		// 获取运费模板详情
		$this->authApi('/SellerExpress/express_view', array('id'=>$express_id));
		$express = $this->_data['data'];
		$disable = array();
		// 获取不能勾选选的列表,当前修改的除外
		foreach($express['express_area'] as $vo){
			if($vo['id'] != $id){
				$disable = array_merge($disable,explode(',', $vo['city_ids']));
			}
		}

		// 获取一二级城市列表
		$this->api('/Tools/one_two_city');
		$city = $this->_data['data'];
		// 去掉不能勾选和设置默认选中
		foreach ($city as $ko =>$vo) {
			foreach ($vo['child'] as $k => $v) {
				if(in_array($v['id'], $checked)){
					$city[$ko]['child'][$k]['checked'] = 1;
				}
				if(in_array($v['id'], $disable)){
					$city[$ko]['child'][$k]['disable'] = 1;
				}
			}
		}
		// print_r($city);
		$this->assign('area', $area);
		$this->assign('city', $city);
		$this->assign('express', $express);
        $this->seo(['title' => '修改指定区域运费模板']);
		$this->display();
	}

}