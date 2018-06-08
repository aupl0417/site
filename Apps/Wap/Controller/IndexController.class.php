<?php
namespace Wap\Controller;
use Think\Controller;
class IndexController extends CommonController {

    public function index(){		
		$this->display();
    }

    public function home(){
        $do = new \Wap\Controller\MiaoshaController();
        $res = $do->item();
        $this->assign('miaosha_item',$res);



        $this->display();
    }
}