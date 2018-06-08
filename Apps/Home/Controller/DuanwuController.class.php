<?php
namespace Home\Controller;
use Think\Controller;
class DuanwuController extends Controller {

	public function index(){		
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://item.'.C('domain').'/';
        //$url = 'https://item.trj.cc/';
        $this->assign('url',$url);
		
		$goods_id = [
			/*端午节美食文化*/
			'1074408',
			'1081649',
			'1074561',
			'1081943',
			
			'1074631',
			'1074644',
			'1074706',
			/*端午节必备*/
			'1076620',
			'1074613',
			'1074276',
			'873679',
			'1074493',
			
			'1022568',
			'728379',
			'1044853',
			/*品牌精选*/
			'1062185',
			'1048157',
			'1062298',
			'1061682',
			'1063959',
			
			'1061324',
			'1049022',
			'1049052',
			'1042312',
			'1081584',
			
			'958942',
			'958415',
			'1077258',
			'1081266',
			'958308',
			
			'1041667',
			/*猜你喜欢*/
			'863318',
			'881154',
			'903475',
			'821498',
			'758175',
			
			'861932',
			'810351',
			'726028',
			'863164',
			'810706',
			
			'728339',
			'903671',
		];
		////////////////
		/*
		$goods_id = array();
		$tmp = M('goods')->join('ylh_goods_attr_list ON ylh_goods.id = ylh_goods_attr_list.goods_id')->field('ylh_goods_attr_list.id')->where(['ylh_goods.status'=>1])->limit(70)->select();
		foreach($tmp as $v){
			$goods_id[] = $v['id'];
		}
		//dump($goods_id);
		*/
		///////////////
		$goods = array();
		foreach($goods_id as $k=>$v){
			$goods[$k]['goods'] = M('goods')->join('ylh_goods_attr_list ON ylh_goods.id = ylh_goods_attr_list.goods_id')->field('ylh_goods.goods_name,ylh_goods.images,ylh_goods.price')->where(['ylh_goods_attr_list.id'=>$v])->find();
			$goods[$k]['goods']['url'] = $url.'goods/'.$v.'.html';
			$goods[$k]['goods']['images'] = myurl($goods[$k]['goods']['images'],300,300);
		}
		//dump($goods);
		$this->assign('goods',$goods);
		$this->display();

	}
}