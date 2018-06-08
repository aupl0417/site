<?php
namespace Wap\Controller;

class RefundController extends CommonController
{
	public function index(){
		$this->display();
	}

	public function upload(){
		$res = $this->_upload('imageData');
		$this->ajaxReturn($res);
	}
}