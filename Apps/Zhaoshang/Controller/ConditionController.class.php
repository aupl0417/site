<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class ConditionController extends InitController {
    public function index(){
        //店铺类型
        $res = $this->doApi('/Zhaoshang/shop_type');
        $this->assign('shop_type',$res->data);

        //招商类目
        $res = $this->doApi('/Zhaoshang/category');
        $this->assign('category',$res->data);

        $cid = I('get.cid')?I('get.cid'):100841773;

        $res = $this->doApi('/Zhaoshang/cred_view',['category_id' => $cid]);
        $this->assign('rs',$res->data);
        //dump($res);
        C('seo',['title' => '入驻要求 - 招商频道']);
		$this->display();
    }


}