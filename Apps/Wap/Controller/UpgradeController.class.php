<?php
namespace Wap\Controller;
use Think\Controller;
class UpgradeController extends CommonController {
    public function index(){		
		$this->display();
    }
	
	//检查某地区是否存在代理
	public function check_city_agent(){
		$res=$this->doApi('/UserUpgrade/check_city_agent',array('city_id'=>I('get.city_id')));
		$this->ajaxReturn($res);
	}

}