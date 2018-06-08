<?php
/**
 * -------------------------------------------------
 * 儿童节活动页
 * -------------------------------------------------
 * Create by liangfeng 
 * -------------------------------------------------
 * 2017-05-22
 * -------------------------------------------------
 */
namespace Mobile\Controller;
class ErtongController extends CommonController {
    public function _initialize(){
        parent::_initialize();
    }
	
    public function index(){
		$url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://m.'.C('domain').'/';
        //$url = 'https://item.trj.cc/';
        $this->assign('url',$url);
		
		$goods_id = [
			/*学习用品*/
			'119675',
			'119658',
			'594441',
			'161758',
			
			'549653',
			'869940',
			'134699',
			'136461',
			/*儿童玩具*/
			'401428',
			'689584',
			'604027',
			'689637',
			
			'751135',
			'871027',
			'995811',
			'994411',
			/*儿童鞋服*/
			'578141',
			'502848',
			'517852',
			'477192',
			
			'537954',
			'552776',
			'1053287',
			'732317',
			
			'771762',
			'928559',
			'928466',
			'499263',
			
			'1069679',
			'1058967',
			'1060427',
			'1069045',
			/*零食*/
			'881024',
			'1062274',
			'1061966',
			'871635',
			
			'881449',
			'822081',
			'881314',
			'863001',
			/*运动户外*/
			'873711',
			'347702',
			'703261',
			'433517',
			
			'785629',
			'887833',
			'1036613',
			'957145',
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
			
			$goods[$k]['goods']['url'] = $url.'Goods/view/id/'.$v;
			$goods[$k]['goods']['images'] = myurl($goods[$k]['goods']['images'],200,200);
		}
		$this->assign('goods',$goods);

        $this->display();
    }




}