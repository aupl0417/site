<?php
/**
 * -------------------------------------------------
 * 父亲节活动页
 * -------------------------------------------------
 * Create by lizuheng
 * -------------------------------------------------
 * 2017-06-02
 * -------------------------------------------------
 */
namespace Mobile\Controller;
class FuqinjieController extends CommonController {
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
			/*爸爸该添夏装了*/
			'771166',
			'766917',
			'770604',
			'784757',
			
			'807634',
			'808458',
			'770633',
			'783908',
			
			'181786',
			'158795',
			'168445',
			'168481',
			
			'1057669',
			'1163052',
			'1080391',
			'1126711',
			
			'195431',
			'742887',
			'779898',
			'971690',
			/*爸爸还缺一件配饰*/
			'796843',
			'729643',
			'1337013',
			'730238',
			
			'741280',
			'785092',
			'742597',
			'892136',
			
			'1006966',
			'436937',
			'800166',
			'799487',
			
			'162998',
			'162043',
			'637480',
			'637550',
			/*爸的日常该换了*/
			'1062245',
			'973730',
			'963308',
			'934177',

			'778459',
			'994936',
			'560150',
			'449619',
			
			'965839',
			'986440',
			'941482',
			'704974',
			/*爸别的不爱就好这口*/
			'856264',
			'900969',
			'1260557',
			'1260663',
			
			'605968',
			'605285',
			'605181',
			'710033',
			
			'498374',
			'491018',
			'490576',
			'490966',

			'487964',
			'483941',
			'495573',
			'493249',
			/*爸想过的更惬意一点*/			
			'682105',
			'348584',
			'1255532',
			'615309',

			'872887',
			'439024',
			'719805',
			'670605',
			/*爸想要更健康的身体*/
			'812416',
			'256556',
			'687079',
			'1256273',

			'916891',
			'910605',
			'909729',
			'931448',
			
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