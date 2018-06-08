<?php
namespace Wap\Controller;
use Think\Controller;
use Common\Common\TestUser;
class OrdersController extends CommonController {
    public function index(){		
		$this->display();
    }
	

	public function upload(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);   
    }

    /**
     * 选择支付类型
     */
    public function paytype() {
        $file = '';
        if (in_array($_SESSION['user']['id'], TestUser::UID)) $file = 'dt_paytype';
        $this->display($file);
    }
}