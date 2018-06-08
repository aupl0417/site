<?php
namespace Wap\Controller;
class DaigouController extends CommonController {
    public function index(){
        $this->display();
    }
    
	public function apply(){
		$daigou = getSiteConfig('daigou');

		$this->assign('daigou_cost_ratio',$daigou["daigou_cost_ratio"]);
		$this->assign('daigou_max_cost',$daigou["daigou_max_cost"]);
		$this->assign('daigou_min_cost',$daigou["daigou_min_cost"]);
        $this->display();
    }
    public function intro(){
		$daigou = getSiteConfig('daigou');

		$this->assign('daigou_cost_ratio',$daigou["daigou_cost_ratio"]);
		$this->assign('daigou_max_cost',$daigou["daigou_max_cost"]);
		$this->assign('daigou_min_cost',$daigou["daigou_min_cost"]);
        $this->display();
    }
	
	public function edit_apply(){
		$daigou = getSiteConfig('daigou');

		$this->assign('daigou_cost_ratio',$daigou["daigou_cost_ratio"]);
		$this->assign('daigou_max_cost',$daigou["daigou_max_cost"]);
		$this->assign('daigou_min_cost',$daigou["daigou_min_cost"]);
        $this->display();
    }
    
    public function upload(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);
    }
}