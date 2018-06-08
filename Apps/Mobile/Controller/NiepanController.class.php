<?php
/**
 * -------------------------------------------------
 * 涅槃活动页
 * -------------------------------------------------
 * Create by liangfeng
 * -------------------------------------------------
 * 2017-04-15
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class NiepanController extends CommonController {
    public function _initialize(){
        parent::_initialize();
    }
	
    public function index(){
		$this->display();
    }

}