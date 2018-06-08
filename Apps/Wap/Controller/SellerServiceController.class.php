<?php
namespace Wap\Controller;

class SellerServiceController extends CommonController
{

	public function index(){
		$this->display();
	}
	
	public function upload(){
		$res = $this->_upload('imageData');
		$this->ajaxReturn($res);
	}
}