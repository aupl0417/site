<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends CommonController {
   
    /*
    public function test() {
		$list = M('goods_attr_list')->field('id,goods_id,attr,attr_name,attr_id')->select();
		
		foreach($list as $k=>$v){
			$attr_name = '';
			$attr_id = '';
			$attr = '';
			
			$attrs = explode(',',$v['attr_id']);
			foreach($attrs as $ke=>$va){
				$ids = explode(':',$va);
				$map['goods_id'] = $v['goods_id'];
				$map['attr_id'] = $ids[0];
				$map['option_id'] = $ids[1];
				$res = M('goods_attr_value')->field('attr_value,attr_id,option_id')->where($map)->find();
				$attr_name .= $res['attr_value'].',';
				$attr_id .= $res['attr_id'].':'.$res['option_id'].',';
				$attr .= $res['attr_id'].':'.$res['option_id'].':'.$res['attr_value'].',';
			}
			
			$data['attr'] = substr($attr,0,-1);
			$data['attr_name'] = substr($attr_name,0,-1);
			$data['attr_id'] = substr($attr_id,0,-1);
			
			if($data['attr'] != $v['attr']){
				//M('goods_attr_list')->where('id = '.$v['id'])->data($data)->save();
				dump($v);
				dump($data);
				//exit();
			}
		}

    }
    */
	
	public function mongo(){
		echo 1;
	}
    
}

