<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class IndexController extends InitController {
    public function index(){
        C('seo',['title' => '招商频道']);
		$this->display();
    }

    public function opened(){
        if(empty($_SESSION['user']['id'])) redirect('/');

        $res = $this->doApi('/ShopSetting/shop_info',['openid' => session('user.openid')]);
        //dump($res);

        $this->assign('rs',$res->data);
        C('seo',['title' => '开店状态 - 招商频道']);
        $this->display();
    }





    public function auth_failed(){
        $this->display();
    }


}