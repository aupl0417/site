<?php
namespace Admin\Widget;
use Think\Controller;

class TotalsWidget extends Controller
{
	/**
	 * 近七天的数据统计
	 */
	public function flow_7day(){
    	$this->assign('data', D('totals')->flow_7day());
    	$this->display('Totals:flow_7day');
	}


	/**
	 * 近七天的交易数据
	 */
	public function jiaoyi_7day(){
		$this->assign('data', D('totals')->jiaoyi_7day());
    	$this->display('Totals:jiaoyi_7day');
	}
}