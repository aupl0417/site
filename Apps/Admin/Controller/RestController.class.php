<?php
namespace Admin\Controller;
use Think\Controller;
class RestController extends CommonController {
	public function city(){
		$do=M('area');
		$list=$do->where(array('sid'=>I('get.sid')))->field('id,name')->select();
		$this->ajaxReturn($list);
	}
	
	public function citylist(){
		$list=get_category(array('table'=>'area','level'=>2,'cache'=>1,'cache_name'=>'citylist_level_2'));
		$this->ajaxReturn($list);
	}
}

		
