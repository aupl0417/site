<?php
namespace Home\Controller;
use Think\Controller;
class ChunjiController extends Controller {

	public function index(){
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://item.'.C('domain').'/';
        //$url .= '://item.trj.cc/';
        $this->assign('url',$url);
		
		$goods_id = [
			'60672',
			'60801',
			'59364',
			'57536',
			'57508',
			'48129',
			'50307',
			'39328',
			'29051',
			'43194',
			'30733',
			'30728',
			'59117',
			'40521',
			'50456',
			'47086',
			
			'16457',
			'18735',
			'18812',
			'18858',
			'18801',
			'19568',
			'18775',
			'18816',
			'18826',
			'61681',
			'34829',
			'53716',
			'30159',
			'26033',
			'26031',
			'26019',

			'32407',
			'40666',
			'32157',
			'60875',
			'61126',
			'61137',
			'57859',
			'39523',
			
			'42143',
			'19068',
			'30077',
			'30080',
			'32084',
			'32118',
			'44819',
			'21938',
			
			
			'60704',
			'57516',
			'57537',
			'60812',
			'52738',
			'61544',
			'36836',
			'20799',
			
			'29983',
			'28769',
			'31125',
			'30082',
			'19052',
			'44622',
			'30039',
			'43592',
			
			'58971',
			'58984',
			'58980',
			'58982',
			'60575',
			'57855',
			'53703',
			'59491',	

		];
		
		//$goods_id = ['19508','19506'];
		/*
		foreach($goods_id as $k => $v){
			
			if($v == '59364'){
				$attr_id = '318427';
			}else if($v == '60704'){
				$attr_id = '315798';
			}else if($v == '31125'){
				$attr_id = '162602';
			}else if($v == '30039'){
				$attr_id = '233397';
			}else if($v == '43592'){
				$attr_id = '222149';
			}
			
			if($attr_id){
				$goods_urls[$k] = $this->get_goods_url($url,$v,$attr_id);
			}else{
				$goods_urls[$k] = $this->get_goods_url($url,$v);
			}
			unset($attr_id);
		}
		*/
		foreach($goods_id as $k => $v){
			$goods_urls[$k] = $this->get_goods_url($url,$v);
		}
		
		//dump($goods_urls);
		
		$this->assign('goods_urls',$goods_urls);
		$this->display();

	}


	//获取商品信息
	private function get_goods_url($url,$goods_id,$attr_id=0){
		$goods_name = M('goods')->where(['id'=>$goods_id])->getField('goods_name');
		if($attr_id != 0){
			$res = M('goods_attr_list')->field('id,price')->where(['goods_id'=>$goods_id,'id'=>$attr_id])->order('price asc')->find();
		}else{
			$res = M('goods_attr_list')->field('id,price')->where(['goods_id'=>$goods_id])->order('price asc')->find();
		}

        $result['goods_url'] = $url.'goods/'.$res['id'].'.html';
		$result['price'] = $res['price'];
		$result['goods_name'] = $goods_name;
		return $result;
	}



}