<?php
namespace Home\Controller;
use Think\Controller;
class MuqinjieController extends Controller {

	public function index(){		
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://item.'.C('domain').'/';
        //$url = 'https://item.trj.cc/';
        $this->assign('url',$url);
		
		$goods_id = [
			/*鲜花*/
			'948763',
			/*套装系列*/
			'967275',
			'892761',
			'967103',
			/*护肤系列*/
			'966590',
			'1028413',
			'943770',
			'765402',
			
			'932743',
			'1022095',
			'966434',
			'900968',
			/*香氛彩妆系列*/
			'966360',
			'933044',
			'766874',
			'765978',
			
			'1022080',
			'1028394',
			'1028397',
			'1022228',
			/*洗护系列*/
			'1022113',
			'1022157',
			'941777',
			'916605',
			/*包包墨镜*/
			'871749',
			'871738',
			'871978',
			'881563',
			
			'872416',
			'881535',
			'871777',
			'872787',
			/*服饰*/
			'800786',
			'843259',
			'842435',
			'793486',
			
			'809320',
			'794073',
			'798563',
			'830934',
			
			'781022',
			'797555',
			'830875',
			'793653',
			/*小家电*/
			'692237',
			'730003',
			'729331',
			'692523',
			
			'678201',
			'683386',
			'811185',
			'797606',
			/*健康器具*/
			'812613',
			'933481',
			'917287',
			'872804',
			/*食品*/
			'909943',
			'932660',
			'930941',
			'915870',
			
			'883208',
			'881452',
			'881736',
			'843492',
			/*茗茶*/
			'888885',
			'903105',
			'855672',
			'705892',
			
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