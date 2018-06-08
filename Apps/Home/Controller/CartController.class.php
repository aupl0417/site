<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends CommonController {

    public function index(){
        $do=D('Cart/CartView');
        $list=$do->cartList(session('user.id'));
        $this->assign('list',$list);
		$this->display();
    }




}