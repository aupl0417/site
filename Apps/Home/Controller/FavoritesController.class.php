<?php
namespace Home\Controller;
use Think\Controller;
class FavoritesController extends CommonController {

    public function index(){
    	$do=D('MyenshrineProductsView');
    	$products=$do->where(array('userid'=>session('user.id'),'sid'=>2))->field('id,enshrine_id,name,images,price')->order('atime desc')->limit(10)->select();
    	$this->assign('products',$products);

    	$do=D('MyenshrineStoreView');
    	$store=$do->where(array('userid'=>session('user.id'),'sid'=>1))->field('id,enshrine_id,memberid,shop_name,qq,wang,province,city,level_point,domain,username,is_name,is_xiaobao')->order('atime desc')->limit(10)->select();
    	$this->assign('store',$store);

    	//dump($do->getLastSQL());
		$this->display();
    }

    //浏览记录
    public function history(){
    	if(empty($_SESSION['user']['id'])) exit;
    	$do=D('ViewhistoryView');
    	$list=$do->where(array('memberid'=>session('user.id')))->field('id,infoid,name,images,price')->limit(12)->order('atime desc')->select();
    	$this->assign('list',$list);

		$this->display();
    }


}