<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class BrandController extends InitController {
    public function index(){
        //招商类目
        $res = $this->doApi('/Zhaoshang/category');
        $this->assign('category',$res->data);

        //已入驻品牌
        $cid = I('get.cid')?I('get.cid'):100841773;
        $res = $this->doApi('/Zhaoshang/brand_lib',['category_id' => $cid],'category_id');
        $this->assign('brand',$res->data);

        C('seo',['title' => '招商品牌 - 招商频道']);
		$this->display();
    }


}