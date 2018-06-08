<?php
namespace Common\Widget;
use Think\Controller;
class SeoWidget extends Controller
{



	public function index(){
		
		$where = array(
			'modules' => strtolower(MODULE_NAME),
			'controller' => strtolower(CONTROLLER_NAME),
			'action' 	=> strtolower(ACTION_NAME),
		);
		$one = M('seo')->where($where)->order('id desc')->find();
		$seoData = (isset($one['id']) && $one['active'] == 1) ? $one: $this->set();
		# 参数设置 获取值还是获取分类表
		# if($seoData['params']){
		# 	$params = json_decode(html_entity_decode($seoData['params']),true);
		# 	foreach($params as $ko => $vo){
		# 		switch ($vo['val']) {
		# 			case 'isValue':
		# 				$seoData['title'] .= ' ' . I($vo['key']);
		# 				break;
		# 			case 'isCategory':
		# 				$category = M('goods_category')->field('category_name')->find(I($vo['key']));
		# 				$seoData['title'] .= ' ' . ($category['category_name'] ? $category['category_name'] : '');
		# 				break;
		# 			default:
		# 				$seoData['title'] .= ' ' . I($vo['key']);
		# 				break;
		# 		}
		# 	}
		# }
		# 是否是首页
		# if($seoData['modules'] == 'home' && $seoData['controller'] == 'index' && $seoData['action'] == 'index'){

		# }else{
		# 	$seoData['title'] .= ' 乐兑';
		# }

		$this->assign('seoData', $seoData);
		$this->display(T('Common@Widget:seo'));
	}

	public function set(){
		$key = md5('seo_set_default_messages');
		$cache = S($key);
		$default = array(
			'title' => '乐兑 - 引领全球消费的电商平台',
			'keywords' => '乐兑,网上购物,积分购物,唐宝购物,男女服饰,男鞋女鞋,箱包皮具,美妆洗护,钟表,珠宝,配饰,母婴玩具',
			'description' => '乐兑-引领全球消费的电商平台,提供数万个品牌优质商品，便捷、诚信服务，为您提供愉悦的网上购物体验。',
		);
		return $cache ? $cache : $default;
	}
}